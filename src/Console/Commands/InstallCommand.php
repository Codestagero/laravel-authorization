<?php

namespace Codestage\Authorization\Console\Commands;

use Codestage\Authorization\Middleware\CheckAuthorizationMiddleware;
use Codestage\Authorization\Providers\AuthorizationServiceProvider;
use Error;
use Illuminate\Console\Command;
use ReflectionClass;
use Symfony\Component\Console\Command\Command as BaseCommand;

class InstallCommand extends Command
{
    const PermissionEnumPath = 'app/Enums/Permission.php';
    const PermissionEnumNamespace = 'App\Enums';
    const KernelPath = 'app/Http/Kernel.php';
    const KernelClass = '\App\Http\Kernel';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'codestage:install-authorization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the installation procedure for the authorization package.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Publish authorization
        $this->call('vendor:publish', [
            '--tag' => 'configuration',
            '--provider' => AuthorizationServiceProvider::class
        ]);

        // Create the permissions enum
        if ($this->createPermissionEnum()) {
            $this->output->success('Permissions enum created.');
        } else {
            return BaseCommand::FAILURE;
        }

        // Update permissions_enum in the config file
        $configurationContents = file_get_contents($this->laravel->configPath('authorization.php'));
        $configurationContents = str_ireplace(
            'Codestage\Authorization\Contracts\IPermissionEnum::class',
            self::PermissionEnumNamespace . '\Permission::class',
            $configurationContents
        );
        if (file_put_contents($this->laravel->configPath('authorization.php'), $configurationContents) === false) {
            $this->output->info('You can set the enum you intend to use for permissions inside config/authorization.php');
        }

        // Ask the user whether they want to run migrations now
        if ($this->confirm('Do you want to run migrations now?', true)) {
            $this->call('migrate');
        }

        // Add the middleware to the Kernel
        if ($this->addMiddlewareToKernel()) {
            $this->output->success('Middleware added to the HTTP Kernel file.');
        } else {
            $this->output->warning([
                'Middleware could not be added to the Kernel file!',
                'You will have to do this yourself manually.',
                'We suggest you place it in your middleware groups, before SubstituteBindings is run',
                'Note that placing it in the global middleware will not work.'
            ]);
        }

        return BaseCommand::SUCCESS;
    }

    /**
     * Create the permissions enum.
     *
     * @return bool
     */
    private function createPermissionEnum(): bool
    {
        $path = $this->laravel->basePath(self::PermissionEnumPath);

        // If a file by that name already exists, ask the user whether to overwrite it
        if (file_exists($path)) {
            if ($this->confirm('It seems a file with the name of ' . self::PermissionEnumPath . ' already exists. Do you wish to overwrite it?', true)) {
                if (!unlink($path)) {
                    $this->output->error('File could not be deleted! Delete it manually and try again.');
                }
            } else {
                return false;
            }
        }

        // Generate the permission enum from the stub
        $enumContents = $this->completeStub('permission_enum.stub', [
            'namespace' => self::PermissionEnumNamespace
        ]);

        // Publish the stub
        return file_put_contents($path, $enumContents) !== false;
    }

    /**
     * Generate a hydrated string value from the given stub.
     *
     * @param string $stubName
     * @param array<string, string> $replacements
     * @return string
     */
    private function completeStub(string $stubName, array $replacements = []): string
    {
        // Try to get the stub file contents
        $stub = file_get_contents($this->laravel->basePath('vendor/codestage/laravel-authorization/stubs/' . $stubName));

        // Make sure the stub actually exists
        if ($stub === false) {
            throw new Error('Stub not found: ' . $stubName);
        }

        // Replace interpolations from the replacements array
        foreach ($replacements as $key => $value) {
            $stub = (string)str_ireplace('{{ ' . $key . ' }}', $value, $stub);
            $stub = (string)str_ireplace('{{' . $key . '}}', $value, $stub);
        }

        // Return the resulting string
        return $stub;
    }

    /**
     * Add the required middleware to the Kernel.
     *
     * @return bool
     */
    public function addMiddlewareToKernel(): bool
    {
        // If the kernel does not exist, there is nothing to be done
        if (!file_exists(self::KernelPath)) {
            return false;
        }

        // If the kernel does not exist, there is nothing to be done
        if (!class_exists(self::KernelClass)) {
            return false;
        }

        // Try to get the initial file contents
        $newFileContents = file_get_contents(self::KernelPath);

        if ($newFileContents === false) {
            return false;
        }

        // Try to reflect on the middlewareGroups property of the Kernel class
        $reflection = new ReflectionClass(self::KernelClass);

        if (!$reflection->hasProperty('middlewareGroups')) {
            return false;
        }

        $groups = $reflection->getProperty('middlewareGroups')->getValue();

        if (!is_array($groups)) {
            return false;
        }

        // Add the middleware to each group
        foreach ($groups as $group => $groupMiddleware) {
            if (!in_array(CheckAuthorizationMiddleware::class, $groupMiddleware)) {
                $newFileContents = preg_replace(
                    '([\'"]' . $group . '[\'"] => \[[a-zA-Z0-9\s:,/\'"]+?)((?:\w|\)+)?SubstituteBindings::class)',
                    '$1' . CheckAuthorizationMiddleware::class . ',\n\t$2',
                    $newFileContents
                );
            }
        }

        // Update the kernel file
        return file_put_contents(self::KernelPath, $newFileContents) !== false;
    }
}

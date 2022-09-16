<?php

namespace Codestage\Authorization\Console\Commands;

use Codestage\Authorization\Providers\AuthorizationServiceProvider;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as BaseCommand;

class InstallCommand extends Command
{
    const PermissionEnumPath = 'app/Enums/Permission.php';
    const PermissionEnumNamespace = 'App\Enums';

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
        $this->call('vendor:publish --tag=configuration --provider=' . AuthorizationServiceProvider::class);

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
            self::PermissionEnumNamespace . '\Permission',
            $configurationContents
        );

        if (file_put_contents($this->laravel->configPath('authorization.php'), $configurationContents) === false) {
            $this->output->info('You can set the enum you intend to use for permissions inside config/authorization.php');
        }

        // Create the authorization namespaces
        if (!is_dir($this->laravel->basePath('app/Authorization'))) {
            mkdir($this->laravel->basePath('app/Authorization'));
        }
        if (!is_dir($this->laravel->basePath('app/Authorization/Handlers'))) {
            mkdir($this->laravel->basePath('app/Authorization/Handlers'));
        }
        if (!is_dir($this->laravel->basePath('app/Authorization/Policies'))) {
            mkdir($this->laravel->basePath('app/Authorization/Policies'));
        }
        if (!is_dir($this->laravel->basePath('app/Authorization/Requirements'))) {
            mkdir($this->laravel->basePath('app/Authorization/Requirements'));
        }

        // Ask the user whether they want to run migrations now
        if ($this->confirm('Do you want to run migrations now?', true)) {
            $this->call('migrate');
        }

        // Check whether the user has default laravel policies and warn them if they do
        $defaultPoliciesPath = $this->laravel->basePath('app/Policies');
        if (is_dir($defaultPoliciesPath)) {
            if ($this->checkDirectoryEmpty($defaultPoliciesPath)) {
                unlink($defaultPoliciesPath);
            } else {
                $this->output->warning([
                    'It seems like you are using Laravel policies as well in your project.',
                    'We recommend migrating them to the policy-based authorization this package provides.'
                ]);
            }
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
            if ($this->confirm('It seems a file with the name of ' . self::PermissionEnumPath . 'already exists. Do you wish to overwrite it?', true)) {
                if (!unlink(unlink($path))) {
                    $this->output->error('File could not be deleted! Delete it manually and try again.');
                }
            } else {
                return false;
            }
        }

        // Get the stub contents
        $stub = file_get_contents($this->laravel->basePath('vendor/codestage/stubs/permission_enum.stub'));

        // Replace the namespace
        $stub = str_ireplace('{{ namespace }}', self::PermissionEnumNamespace, $stub);

        // Publish the stub
        return file_put_contents($path, $stub) !== false;
    }

    /**
     * Check if the given directory is empty.
     *
     * @param string $directory
     * @return bool
     */
    private function checkDirectoryEmpty(string $directory): bool
    {
        $handle = opendir($directory);
        while (($entry = readdir($handle)) !== false) {
            if ($entry !== "." && $entry !== "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }
}

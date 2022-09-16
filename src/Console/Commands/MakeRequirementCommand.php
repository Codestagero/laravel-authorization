<?php

namespace Codestage\Authorization\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:requirement', description: 'Create a new requirement class')]
class MakeRequirementCommand extends GeneratorCommand
{
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Requirement';

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws FileNotFoundException
     */
    public function handle(): ?bool
    {
        if (parent::handle() !== false) {
            $this->call(MakeRequirementHandlerCommand::class, [
                'requirement' => $this->getNameInput()
            ]);
        } else {
            return false;
        }

        return null;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        $stubName = 'requirement.stub';

        if (file_exists($this->laravel->basePath('stubs/' . $stubName))) {
            return $this->laravel->basePath('stubs/' . $stubName);
        } else {
            return __DIR__ . '/../../../stubs/' . $stubName;
        }
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceRequirementHandler($stub)->replaceClass($stub, $name);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Authorization\Requirements';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput(): string
    {
        $name = parent::getNameInput();

        if (!str_ends_with($name, 'Requirement')) {
            $name .= 'Requirement';
        }

        return $name;
    }

    /**
     * Get the desired handler name from the input.
     *
     * @return string
     */
    protected function getHandlerInput(): string
    {
        return $this->getNameInput() . 'Handler';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @return $this
     */
    protected function replaceRequirementHandler(string &$stub): static
    {
        $searches = [
            ['NamespacedDummyRequirementHandler', 'DummyRequirementHandler'],
            ['{{ namespacedRequirementHandler }}', '{{ requirementHandler }}'],
            ['{{namespacedRequirementHandler}}', '{{requirementHandler}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->rootNamespace() . 'Authorization\\Handlers\\' . $this->getHandlerInput(), $this->getHandlerInput()],
                $stub
            );
        }

        return $this;
    }
}

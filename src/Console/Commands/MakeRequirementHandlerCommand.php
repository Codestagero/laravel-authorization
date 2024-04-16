<?php

namespace Codestage\Authorization\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:requirement-handler', description: 'Create a new requirement handler class')]
class MakeRequirementHandlerCommand extends GeneratorCommand
{
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Requirement handler';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        $stubName = 'requirement_handler.stub';

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
     * @throws FileNotFoundException
     * @return string
     *
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceRequirement($stub)->replaceClass($stub, $name);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Authorization\Handlers';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput(): string
    {
        $requirementName = trim($this->argument('requirement'));

        if (!str_ends_with($requirementName, 'Handler')) {
            $requirementName .= 'Handler';
        }

        return $requirementName;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getRequirementInput(): string
    {
        return trim($this->argument('requirement'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['requirement', InputArgument::REQUIRED, 'The name of the requirement class that should be handled'],
        ];
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @return $this
     */
    protected function replaceRequirement(string &$stub): static
    {
        $searches = [
            ['NamespacedDummyRequirement', 'DummyRequirement'],
            ['{{ namespacedRequirement }}', '{{ requirement }}'],
            ['{{namespacedRequirement}}', '{{requirement}}'],
        ];

        $requirementName = $this->getRequirementInput();

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->rootNamespace() . 'Authorization\\Requirements\\' . $requirementName, $requirementName],
                $stub
            );
        }

        return $this;
    }
}

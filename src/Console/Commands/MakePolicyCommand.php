<?php

namespace Codestage\Authorization\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:policy', description: 'Create a new policy class')]
class MakePolicyCommand extends GeneratorCommand
{
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Policy';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        $stubName = 'policy.stub';

        if (file_exists($this->laravel->basePath('stubs/' . $stubName))) {
            return $this->laravel->basePath('stubs/' . $stubName);
        } else {
            return __DIR__ . '/../../../stubs/' . $stubName;
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Authorization\Policies';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput(): string
    {
        $name = parent::getNameInput();

        if (!str_ends_with($name, 'Policy')) {
            $name .= 'Policy';
        }

        return $name;
    }
}

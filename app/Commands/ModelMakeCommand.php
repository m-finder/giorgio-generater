<?php

namespace App\Commands;

use Illuminate\Console\GeneratorCommand;

class ModelMakeCommand extends GeneratorCommand
{
    /** {@inheritdoc} */
    protected $signature = 'make:model {name : The name of the model} {--p|path= : The path where the model should be created} {--f|force : Create the class even if the class already exists}';

    /** {@inheritdoc} */
    protected $description = 'Create a new Model for PHP';


    /** {@inheritdoc} */
    protected $type = 'Model';

    /** {@inheritdoc} */
    protected function getNameInput(): string
    {
        return ucfirst(parent::getNameInput());
    }

    /** {@inheritdoc} */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Models';
    }

    /** {@inheritdoc} */
    protected function getPath($name): string
    {
        $basePath = $this->option('path') ?: getcwd();

        $this->makeDirectory($basePath);

        return rtrim($basePath, '/\\') . '/' . class_basename($name) . '.php';
    }

    /** {@inheritdoc} */
    protected function getStub(): string
    {
        $relativePath = '/stubs/controller.stub';

        return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__ . $relativePath;
    }
}

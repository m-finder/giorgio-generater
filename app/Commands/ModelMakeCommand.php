<?php

namespace App\Commands;

use Illuminate\Console\GeneratorCommand;

class ModelMakeCommand extends GeneratorCommand
{
    /** {@inheritdoc} */
    protected $signature = 'make:model {name} {--p|path=} {--f|force}';

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

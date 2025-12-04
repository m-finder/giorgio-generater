<?php

namespace App\Commands;

use Illuminate\Console\GeneratorCommand;

class ModelMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:model {name} {--p|path=} {--f|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Model for PHP';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput(): string
    {
        return ucfirst(parent::getNameInput());
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Models';
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name): string
    {
        if ($this->option('path')) {
            $basePath = $this->option('path');
            $this->makeDirectory($basePath);
            return rtrim($basePath, '/\\') . '/' . class_basename($name) . '.php';
        }

        $basePath = getcwd() . '/app/Models';
        $this->makeDirectory($basePath);
        return $basePath . '/' . class_basename($name) . '.php';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        $local = getcwd() . '/.gen/stubs/model.stub';
        if (file_exists($local)) {
            return $local;
        }

        return base_path('stubs/model.stub');
    }
}

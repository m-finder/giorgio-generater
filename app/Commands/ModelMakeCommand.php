<?php

namespace App\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

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
    protected $description = 'Create a new Model for Laravel';

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

        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        if ($this->option('path')) {
            $basePath = $this->option('path');
            $this->makeDirectory($basePath);
            return rtrim($basePath, '/\\') . '/app/' . str_replace('\\', '/', $name) . '.php';
        }

        return getcwd() . '/app/' . str_replace('\\', '/', $name) . '.php';
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

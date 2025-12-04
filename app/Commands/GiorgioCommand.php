<?php

namespace App\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

abstract class GiorgioCommand extends GeneratorCommand
{

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
}

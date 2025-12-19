<?php

namespace App\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GiorgioCommand extends GeneratorCommand
{

    /**
     * Initialize
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        if (!is_dir(getcwd() . '/.gen')) {
            $this->call('init', ['--force' => true]);
        }
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
}

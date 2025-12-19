<?php

namespace App\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class LaravelModelMakeCommand extends GiorgioCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel:model 
                            {name : The name of the class}
                            {--p|path= : The path of the class}
                            {--f|force : Create the class even if the controller already exists}';

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
     * @return int
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        parent::handle();

        // 获取类名和路径
        $name = $this->argument('name');
        $path = $this->getPath($name);

        // 显示生成的详情
        $this->table(['Key', 'Value'], [
            ['Class Name', class_basename(str_replace('/', '\\', $name))],
            ['Namespace', $this->qualifyClass($name)],
            ['File Path', $path],
        ]);

        return self::SUCCESS;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        $local = getcwd() . '/.gen/stubs/laravel/model.stub';
        if (file_exists($local)) {
            return $local;
        }

        return base_path('stubs/laravel/model.stub');
    }
}

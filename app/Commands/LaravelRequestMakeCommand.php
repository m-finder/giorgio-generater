<?php

namespace App\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class LaravelRequestMakeCommand extends GiorgioCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel:request 
                            {name : The name of the class}
                            {--p|path= : The path of the class}
                            {--f|force : Create the class even if the controller already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Request for Laravel';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Http\Requests';
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
        $local = getcwd() . '/.gen/stubs/laravel/request.stub';
        if (file_exists($local)) {
            return $local;
        }

        return base_path('stubs/laravel/request.stub');
    }
}

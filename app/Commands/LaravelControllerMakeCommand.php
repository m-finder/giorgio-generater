<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputOption;
use function Laravel\Prompts\confirm;

class LaravelControllerMakeCommand extends GiorgioCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel:controller
                            {name : The name of the class}
                            {--type= : Manually specify the controller stub file to use}
                            {--s|singleton : Generate a singleton resource controller class}
                            {--api : Exclude the create and edit methods from the controller}
                            {--invokable : Generate a single method, invokable controller class}
                            {--m|model= : Generate a resource controller for the given model}
                            {--parent : Generate a nested resource controller class}
                            {--resource : Generate a resource controller class}
                            {--requests : Generate FormRequest classes for store and update}
                            {--creatable : Indicate that a singleton resource should be creatable}
                            {--p|path= : The path of the class}
                            {--f|force : Create the class even if the controller already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller class';

    /**
     * The type of class being generated
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Http\Controllers';
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
        $stub = null;

        if ($type = $this->option('type')) {
            $stub = "/stubs/laravel/controller.{$type}.stub";
        } elseif ($this->option('parent')) {
            $stub = $this->option('singleton')
                ? '/stubs/laravel/controller.nested.singleton.stub'
                : '/stubs/laravel/controller.nested.stub';
        } elseif ($this->option('model')) {
            $stub = '/stubs/laravel/controller.model.stub';
        } elseif ($this->option('invokable')) {
            $stub = '/stubs/laravel/controller.invokable.stub';
        } elseif ($this->option('singleton')) {
            $stub = '/stubs/laravel/controller.singleton.stub';
        } elseif ($this->option('resource')) {
            $stub = '/stubs/laravel/controller.stub';
        }

        if ($this->option('api') && is_null($stub)) {
            $stub = '/stubs/laravel/controller.api.stub';
        } elseif ($this->option('api') && !is_null($stub) && !$this->option('invokable')) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        $stub ??= '/stubs/laravel/controller.plain.stub';

        $local = getcwd() . '/.gen/' . $stub;
        if (file_exists($local)) {
            return $local;
        }

        return base_path($stub);
    }


    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in the base namespace.
     *
     * @param string $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $rootNamespace = $this->rootNamespace();
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        if ($this->option('parent')) {
            $replace = $this->buildParentReplacements();
        }

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        if ($this->option('creatable')) {
            $replace['abort(404);'] = '//';
        }

        $baseControllerExists = file_exists($this->getPath("{$rootNamespace}Http\Controllers\Controller"));

        if ($baseControllerExists) {
            $replace["use {$controllerNamespace}\Controller;\n"] = '';
        } else {
            $replace[' extends Controller'] = '';
            $replace["use {$rootNamespace}Http\Controllers\Controller;\n"] = '';
        }

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    protected function buildParentReplacements(): array
    {
        $parentModelClass = $this->parseModel($this->option('parent'));
        $modelPath = $this->getPath($parentModelClass);
        if (!file_exists($modelPath) &&
            confirm("A {$parentModelClass} model does not exist. Do you want to generate it?", default: true)) {
            $this->call('laravel:model', ['name' => $parentModelClass]);
        }

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            '{{ namespacedParentModel }}' => $parentModelClass,
            '{{namespacedParentModel}}' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            '{{ parentModel }}' => class_basename($parentModelClass),
            '{{parentModel}}' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            '{{ parentModelVariable }}' => lcfirst(class_basename($parentModelClass)),
            '{{parentModelVariable}}' => lcfirst(class_basename($parentModelClass)),
        ];
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace): array
    {
        $modelClass = $this->parseModel($this->option('model'));
        $modelPath = $this->getPath($modelClass);
        if (!file_exists($modelPath) && confirm("A {$modelClass} model does not exist. Do you want to generate it?", default: true)) {
            $this->call('laravel:model', ['name' => $modelClass]);
        }

        $replace = $this->buildFormRequestReplacements($replace, $modelClass);

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param string $model
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function parseModel(string $model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     * @param string $modelClass
     * @return array
     */
    protected function buildFormRequestReplacements(array $replace, string $modelClass): array
    {
        [$namespace, $storeRequestClass, $updateRequestClass] = [
            'Illuminate\\Http', 'Request', 'Request',
        ];

        if ($this->option('requests')) {
            $namespace = 'App\\Http\\Requests';

            [$storeRequestClass, $updateRequestClass] = $this->generateFormRequests(
                $modelClass, $storeRequestClass, $updateRequestClass
            );
        }

        $namespacedRequests = $namespace . '\\' . $storeRequestClass . ';';

        if ($storeRequestClass !== $updateRequestClass) {
            $namespacedRequests .= PHP_EOL . 'use ' . $namespace . '\\' . $updateRequestClass . ';';
        }

        return array_merge($replace, [
            '{{ storeRequest }}' => $storeRequestClass,
            '{{storeRequest}}' => $storeRequestClass,
            '{{ updateRequest }}' => $updateRequestClass,
            '{{updateRequest}}' => $updateRequestClass,
            '{{ namespacedStoreRequest }}' => $namespace . '\\' . $storeRequestClass,
            '{{namespacedStoreRequest}}' => $namespace . '\\' . $storeRequestClass,
            '{{ namespacedUpdateRequest }}' => $namespace . '\\' . $updateRequestClass,
            '{{namespacedUpdateRequest}}' => $namespace . '\\' . $updateRequestClass,
            '{{ namespacedRequests }}' => $namespacedRequests,
            '{{namespacedRequests}}' => $namespacedRequests,
        ]);
    }

    /**
     * Generate the form requests for the given model and classes.
     *
     * @param string $modelClass
     * @param string $storeRequestClass
     * @param string $updateRequestClass
     * @return array
     */
    protected function generateFormRequests(string $modelClass, string $storeRequestClass, string $updateRequestClass): array
    {
        $storeRequestClass = 'Store' . class_basename($modelClass) . 'Request';

        $this->call('laravel:request', [
            'name' => $storeRequestClass,
        ]);

        $updateRequestClass = 'Update' . class_basename($modelClass) . 'Request';

        $this->call('laravel:request', [
            'name' => $updateRequestClass,
        ]);

        return [$storeRequestClass, $updateRequestClass];
    }
}

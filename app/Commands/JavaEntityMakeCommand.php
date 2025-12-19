<?php

namespace App\Commands;


use Illuminate\Support\Str;

class JavaEntityMakeCommand extends GiorgioCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'java:entity
                            {name : The name of the class}
                            {--pe|package= : The package of the class}
                            {--table : The table name of the class}
                            {--p|path= : The path of the class}
                            {--f|force : Create the class even if the controller already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Entity for Java';

    protected $type = 'Entity';

    /**
     * 获取默认包名
     */
    protected function getDefaultPackage(): string
    {
        return config('giorgio.java.default_package', 'com.example') . '.entity';
    }

    /**
     * 获取Java源码根目录
     */
    protected function getJavaSourcePath(): string
    {
        return $this->option('path') ?: getcwd() . '/src/main/java';
    }

    /**
     * 构建类名
     */
    protected function buildClass($name): string
    {
        $stub = file_get_contents($this->getStub());

        // 获取包名
        $package = $this->option('package') ?: $this->getDefaultPackage();

        // 获取类名（处理可能包含的路径）
        $className = class_basename(str_replace('/', '\\', $name));

        // 处理表名
        $tableName = $this->option('table') ?: $this->generateTableName($className);

        // 替换模板变量
        $replacements = [
            '{{ package }}' => $package,
            '{{ class }}' => $className,
            '{{ table }}' => $this->formatTableName($tableName),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
    }

    /**
     * 根据类名生成表名
     */
    protected function generateTableName(string $className): string
    {
        // 将驼峰命名转换为下划线命名
        $snake = Str::snake($className);

        // 转换为复数形式（简单实现）
        return Str::plural($snake);
    }

    /**
     * 格式化表名
     */
    protected function formatTableName(string $tableName): string
    {
        // 如果表名包含引号，直接返回
        if (Str::startsWith($tableName, ['"', "'"])) {
            return $tableName;
        }

        // 否则添加双引号
        return '"' . $tableName . '"';
    }

    /**
     * 获取文件路径
     */
    protected function getPath($name): string
    {
        $package = $this->option('package') ?: $this->getDefaultPackage();
        $className = class_basename(str_replace('/', '\\', $name));

        // 将包名转换为路径
        $packagePath = str_replace('.', '/', $package);

        // 构建完整路径
        $basePath = $this->getJavaSourcePath();
        return $basePath . '/' . $packagePath . '/' . $className . '.java';
    }

    /**
     * 执行命令
     */
    public function handle(): int
    {
        // 获取类名和路径
        $name = $this->argument('name');
        $path = $this->getPath($name);

        // 检查文件是否已存在
        if (!$this->option('force') && file_exists($path)) {
            $this->error("Entity already exists at: {$path}");
            $this->info('Use --force to overwrite.');
            return self::FAILURE;
        }

        // 确保目录存在
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // 生成文件内容
        $content = $this->buildClass($name);

        // 写入文件
        file_put_contents($path, $content);

        // 输出成功信息
        $info = $this->type;
        $this->components->info(sprintf('%s [%s] created successfully.', $info, $path));

        // 显示生成的详情
        $this->table(['Key', 'Value'], [
            ['Class Name', class_basename(str_replace('/', '\\', $name))],
            ['Package', $this->option('package') ?: $this->getDefaultPackage()],
            ['Table Name', $this->option('table') ?: $this->generateTableName(class_basename(str_replace('/', '\\', $name)))],
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
        $local = getcwd() . '/.gen/stubs/java/entity.stub';
        if (file_exists($local)) {
            return $local;
        }

        return base_path('stubs/java/entity.stub');
    }
}

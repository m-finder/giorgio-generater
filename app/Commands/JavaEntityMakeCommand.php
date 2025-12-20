<?php

namespace App\Commands;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * MySQL 到 Java 类型映射
     */
    protected array $typeMapping = [
        'tinyint' => 'Integer',
        'smallint' => 'Integer',
        'mediumint' => 'Integer',
        'int' => 'Integer',
        'bigint' => 'Long',
        'float' => 'Float',
        'double' => 'Double',
        'decimal' => 'BigDecimal',
        'char' => 'String',
        'varchar' => 'String',
        'text' => 'String',
        'mediumtext' => 'String',
        'longtext' => 'String',
        'date' => 'LocalDate',
        'datetime' => 'LocalDateTime',
        'timestamp' => 'LocalDateTime',
        'time' => 'LocalTime',
        'enum' => 'String',
        'set' => 'String',
        'json' => 'String',
        'boolean' => 'Boolean',
        'bool' => 'Boolean',
    ];

    /**
     * 获取默认包名
     */
    protected function getDefaultPackage(): string
    {
        return config('giorgio.java.default_package', 'com.example') . '.entity';
    }

    /**
     * 获取 Java 源码根目录
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

        // 获取类名
        $className = class_basename(str_replace('/', '\\', $name));

        // 处理表名
        $tableName = $this->option('table') ?: $this->generateTableName($className);

        // 生成字段
        [$fields, $imports] = $this->generateFieldsFromDatabase($tableName);

        // 处理导入
        $this->addFixedImports($imports);

        // 替换模板变量
        $replacements = [
            '{{ package }}' => $package,
            '{{ class }}' => $className,
            '{{ table }}' => $this->formatTableName($tableName),
            '{{ fields }}' => $fields,
            '{{ imports }}' => $this->formatImports($imports),
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
     * 从数据库表生成字段
     */
    protected function generateFieldsFromDatabase(string $tableName): array
    {
        if (!Schema::hasTable($tableName)) {
            $this->error("Table '{$tableName}' does not exist in the database.");
            return ['', []];
        }

        $columns = DB::select("SHOW FULL COLUMNS FROM `{$tableName}`");

        $fields = [];
        $imports = [];

        foreach ($columns as $column) {
            $fieldCode = $this->generateFieldCode($column);

            if (!empty($fieldCode)) {
                $fields[] = $fieldCode;

                // 收集需要导入的类型
                $javaType = $this->mapToJavaType($column->Type);
                $this->collectImports($javaType, $imports);
            }
        }

        return [
            implode("\n\n", $fields),
            array_unique($imports)
        ];
    }

    /**
     * 生成字段代码
     */
    protected function generateFieldCode(object $column): string
    {
        $fieldName = $this->columnNameToFieldName($column->Field);
        $javaType = $this->mapToJavaType($column->Type);
        $comment = $column->Comment ?: '';

        $annotations = $this->generateFieldAnnotations($column);

        $code = '';

        // 添加注释
        if ($comment) {
            $code .= "    /**\n";
            $code .= "     * {$comment}\n";
            $code .= "     */\n";
        }

        // 添加注解
        if (!empty($annotations)) {
            foreach ($annotations as $annotation) {
                $code .= "    {$annotation}\n";
            }
        }

        // 添加字段声明
        $code .= "    private {$javaType} {$fieldName};";

        return $code;
    }

    /**
     * 列名转换为字段名（驼峰）
     */
    protected function columnNameToFieldName(string $columnName): string
    {
        // 移除下划线并转为驼峰
        return Str::camel($columnName);
    }

    /**
     * 映射 MySQL 类型到 Java 类型
     */
    protected function mapToJavaType(string $mysqlType): string
    {
        $mysqlType = strtolower($mysqlType);

        // 提取基本类型
        preg_match('/^([a-z]+)/', $mysqlType, $matches);
        $baseType = $matches[1] ?? $mysqlType;

        // 处理无符号整数
        if (str_contains($mysqlType, 'unsigned') && str_contains($baseType, 'int')) {
            switch ($baseType) {
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'int':
                    return 'Integer';
                case 'bigint':
                    return 'Long';
            }
        }

        // 处理带括号的类型（如 varchar(255)）
        $baseType = preg_replace('/\([^)]*\)/', '', $baseType);

        return $this->typeMapping[$baseType] ?? 'String';
    }

    /**
     * 生成字段注解
     */
    protected function generateFieldAnnotations(object $column): array
    {
        $annotations = [];

        // 主键注解
        if ($column->Key === 'PRI') {
            $idType = $this->determineIdType($column);
            $annotations[] = "@TableId(type = IdType.{$idType})";
        } // 普通字段注解
        else {
            $fieldName = $column->Field;
            $propertyName = $this->columnNameToFieldName($fieldName);
            $createTimeField = config('giorgio.field.created_time_field');
            $updateTimeField = config('giorgio.field.updated_time_field');

            // 如果字段名和属性名不同，添加 @TableField 注解
            if ($fieldName !== $propertyName && !in_array($fieldName, array_merge($createTimeField, $updateTimeField))) {
                $annotations[] = "@TableField(\"{$fieldName}\")";
            }

            // 特殊字段处理（创建时间、更新时间）
            if (in_array($fieldName, $createTimeField)) {
                $annotations[] = '@TableField(value = "created_at", fill = FieldFill.INSERT, updateStrategy = FieldStrategy.NEVER)';
            } elseif (in_array($fieldName, $updateTimeField)) {
                $annotations[] = '@TableField(value = "updated_at", fill = FieldFill.INSERT_UPDATE)';
            }
        }

        return $annotations;
    }

    /**
     * 确定主键类型
     */
    protected function determineIdType(object $column): string
    {
        $type = strtolower($column->Type);

        // 检查是否为自增
        if (str_contains($type, 'auto_increment') || str_contains($column->Extra, 'auto_increment')) {
            return 'AUTO';
        }

        // 检查是否有默认值
        if ($column->Default !== null) {
            return 'INPUT';
        }

        return 'NONE';
    }

    /**
     * 收集需要导入的类
     */
    protected function collectImports(string $javaType, array &$imports): void
    {
        $importMap = [
            'LocalDate' => 'java.time.LocalDate',
            'LocalDateTime' => 'java.time.LocalDateTime',
            'LocalTime' => 'java.time.LocalTime',
            'BigDecimal' => 'java.math.BigDecimal',
        ];

        if (isset($importMap[$javaType]) && $importMap[$javaType]) {
            if (!in_array($importMap[$javaType], $imports)) {
                $imports[] = $importMap[$javaType];
            }
        }
    }

    /**
     * 添加固定的导入语句
     */
    protected function addFixedImports(array &$imports): void
    {
        $fixedImports = [
            'lombok.Data',
            'lombok.Builder',
            'lombok.AllArgsConstructor',
            'lombok.NoArgsConstructor',
            'com.baomidou.mybatisplus.annotation.*',
        ];

        foreach ($fixedImports as $import) {
            if (!in_array($import, $imports)) {
                $imports[] = $import;
            }
        }
    }


    /**
     * 格式化导入语句
     */
    protected function formatImports(array $imports): string
    {
        $imports = array_unique(array_filter($imports));
        sort($imports);

        $result = '';
        foreach ($imports as $import) {
            $result .= "import {$import};\n";
        }

        return rtrim($result);
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

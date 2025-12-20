<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Helper\TableSeparator;

class ConfigInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print the config info';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Make sure your env file is working well.');

        $db = config('database.default');

        $this->table(['Key', 'Value'], [
            ['env("JAVA_DEFAULT_PACKAGE"):', getenv('JAVA_DEFAULT_PACKAGE')],
            ['config("giorgio.java.default_package"):', config('giorgio.java.default_package')],
            [new TableSeparator(), new TableSeparator()],
            ['env("JAVA_SOURCE_PATH"):', getenv('JAVA_SOURCE_PATH')],
            ['config("giorgio.java.source_path"):', config('giorgio.java.source_path')],
            [new TableSeparator(), new TableSeparator()],
            ['env("CREATED_TIME_FIELD"):', getenv('CREATED_TIME_FIELD')],
            ['config("giorgio.field.created_time_field"):', config('giorgio.field.created_time_field')],
            [new TableSeparator(), new TableSeparator()],
            ['env("UPDATED_TIME_FIELD"):', getenv('UPDATED_TIME_FIELD')],
            ['config("giorgio.field.updated_time_field"):', config('giorgio.field.updated_time_field')],
            [new TableSeparator(), new TableSeparator()],
            ['env("DB_CONNECTION"):', getenv('DB_CONNECTION')],
            ['config("database.default"):', config('database.default')],
            [new TableSeparator(), new TableSeparator()],
            ['env("DB_HOST"):', getenv('DB_HOST')],
            ['config("database.connections.' . $db . '.host"):', config('database.connections.' . $db . '.host')],
            [new TableSeparator(), new TableSeparator()],
            ['env("DB_PORT"):', getenv('DB_PORT')],
            ['config("database.connections.' . $db . '.port"):', config('database.connections.' . $db . '.port')],
            [new TableSeparator(), new TableSeparator()],
            ['env("DB_DATABASE"):', getenv('DB_DATABASE')],
            ['config("database.connections.' . $db . '.database"):', config('database.connections.' . $db . '.database')],
            [new TableSeparator(), new TableSeparator()],
            ['env("DB_USERNAME"):', getenv('DB_USERNAME')],
            ['config("database.connections.' . $db . '.username"):', config('database.connections.' . $db . '.username')],
            [new TableSeparator(), new TableSeparator()],
            ['env("DB_PASSWORD"):', getenv('DB_PASSWORD')],
            ['config("database.connections.' . $db . '.password"):', config('database.connections.' . $db . '.password')],
        ]);

        return self::SUCCESS;
    }
}

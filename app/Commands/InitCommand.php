<?php

namespace App\Commands;

use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;

class InitCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init {--f|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gen init';

    /**
     * Handle
     *
     * @param Filesystem $file
     * @return void
     */
    public function handle(Filesystem $file): void
    {
        $dotGen = getcwd() . '/.gen';

        if (!$this->option('force') && $file->exists($dotGen)) {
            $this->warn('.gen already exists (use --force to overwrite)');
            return;
        }

        if ($this->option('force') && $file->exists($dotGen)) {
            $file->deleteDirectory($dotGen);
        }

        $file->ensureDirectoryExists($dotGen . '/stubs');
        $file->copyDirectory(base_path('stubs'), $dotGen . '/stubs');
        $file->copy(base_path('env.example'), $dotGen . '/.env');

        $this->info('Initialization successful. You can modify the config and template in the .gen directory');
    }
}
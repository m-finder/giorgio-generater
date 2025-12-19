<?php

namespace App\Providers;

use Dotenv\Dotenv;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadRuntimeEnv();
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * load runtime env
     * @return void
     */
    protected function loadRuntimeEnv(): void
    {
        $dotGenEnvPath = getcwd() . '/.gen/.env';
        if (file_exists($dotGenEnvPath)) {
            Dotenv::createMutable(getcwd(), '.gen/.env')->load();
        }
    }
}

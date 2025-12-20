<?php

use Dotenv\Dotenv;
use LaravelZero\Framework\Application;

// load env file in .gen
$dotGenEnvPath = getcwd() . '/.gen/.env';
if (file_exists($dotGenEnvPath)) {
    Dotenv::createUnsafeImmutable(getcwd() . '/.gen/', '.env')->load();
}

return Application::configure(basePath: dirname(__DIR__))->create();

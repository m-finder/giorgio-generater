<?php

return [
    # db config
    'db' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'giorgio-generater'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ],
    # java config
    'java' => [
        'source_path' => env('JAVA_SOURCE_PATH', 'src/main/java'),
        'default_package' => env('JAVA_DEFAULT_PACKAGE', 'com.example'),
    ]
];
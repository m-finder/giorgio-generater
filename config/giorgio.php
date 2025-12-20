<?php

return [
    # time field
    'field' => [
        'created_time_field' => explode(',', env('CREATED_TIME_FIELD', 'created_at,create_time')),
        'updated_time_field' => explode(',', env('UPDATED_TIME_FIELD', 'updated_at,update_time')),
    ],

    # java config
    'java' => [
        'source_path' => env('JAVA_SOURCE_PATH', 'src/main/java'),
        'default_package' => env('JAVA_DEFAULT_PACKAGE', 'com.example'),
    ]
];
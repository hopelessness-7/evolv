<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Code execution driver
    |--------------------------------------------------------------------------
    |
    | auto    — Judge0 first, LocalPhp fallback for PHP when Judge0 fails
    | judge0  — Judge0 only
    | local   — PHP CLI inside the app container only
    |
    */
    'driver' => env('JUDGE0_DRIVER', 'auto'),

    'host' => rtrim(env('JUDGE0_HOST', 'http://judge0-server:2358'), '/'),
    'timeout' => (int) env('JUDGE0_TIMEOUT', 30),
    'auth_token' => env('JUDGE0_AUTH_TOKEN'),

    'local_php_binary' => env('JUDGE0_LOCAL_PHP', 'php'),

    'language_ids' => [
        'php' => 68,
        'python' => 71,
        'javascript' => 63,
        'go' => 60,
        'sql' => 82,
    ],

];

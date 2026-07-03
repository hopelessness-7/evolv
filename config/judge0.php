<?php

return [

    'host' => rtrim(env('JUDGE0_HOST', 'http://judge0-server:2358'), '/'),
    'timeout' => (int) env('JUDGE0_TIMEOUT', 30),
    'auth_token' => env('JUDGE0_AUTH_TOKEN'),

    'language_ids' => [
        'php' => 68,
        'python' => 71,
        'javascript' => 63,
        'go' => 60,
        'sql' => 82,
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default API Version
    |--------------------------------------------------------------------------
    |
    | Module routes are registered under /api/{version}/...
    | New breaking changes ship as v2, v3, etc.
    |
    */

    'default_version' => env('API_DEFAULT_VERSION', 'v1'),

    'supported_versions' => [
        'v1',
    ],

];

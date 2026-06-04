<?php

$prefix = env('SECURITY_SUITE_ROUTE_PREFIX', 'security');

return [
    'enabled' => env('HTTP_LOGGER_ENABLED', true),

    'log_body_methods' => ['POST', 'PUT', 'PATCH'],

    'max_body_length' => 2000,

    'exclude_uris' => [
        $prefix . '/idle-ping',
        $prefix . '/idle-config',
        $prefix . '/http-logs',
        $prefix . '/http-logs/*',
        '_debugbar*',
        'telescope*',
    ],

    'exclude_methods' => [],

    'log_response' => env('HTTP_LOGGER_RESPONSE', false),

    'per_page' => 20,
];

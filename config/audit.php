<?php

return [
    /*
    | Layout for audit/password/HTTP views. Falls back to security_suite.layout when null.
    */
    'layout' => env('SECURITY_SUITE_LAYOUT', 'layouts.app'),

    /*
    | Modules shown in the audit log filter. Empty array = all modules present in the database.
    */
    'modules' => [],

    /*
    | Actions allowed in query filters. Empty array = any action in the database.
    */
    'actions' => [
        'created',
        'updated',
        'deleted',
        'permissions_synced',
        'password_changed',
        'password_expired_changed',
        'account_blocked_permanent',
        'account_locked_temporary',
        'account_unblocked',
    ],

    /*
    | Role name for viewers (requires spatie/laravel-permission or equivalent "role" middleware).
    */
    'viewer_role' => env('AUDIT_VIEWER_ROLE', 'admin'),

    'per_page' => 20,
];

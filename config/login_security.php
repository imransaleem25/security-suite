<?php
return [
    'max_attempts'    => env('LOGIN_MAX_ATTEMPTS', 5),
    'block_mode'      => env('LOGIN_BLOCK_MODE', 'temporary'), // 'temporary' or 'admin'
    'lockout_minutes' => env('LOGIN_LOCKOUT_MINUTES', 15),

    /*
    | Record login, logout, and failed attempts to login_logs table.
    */
    'logging_enabled' => env('LOGIN_LOGGING_ENABLED', true),

    /*
    | Apply lockout automatically when Laravel fires the Failed auth event.
    | Disable if your app calls LoginBlockService::recordFailure() manually.
    */
    'auto_block_via_events' => env('LOGIN_AUTO_BLOCK_VIA_EVENTS', true),

    /*
    | User field used on the login form (email or username).
    */
    'login_field' => env('LOGIN_FIELD', 'email'),

    /*
    | Paginate login log admin screens.
    */
    'logs_per_page' => 20,

    /*
    | SecureLoginFlow middleware — headers and pre-login block checks.
    */
    'login_flow_security_enabled' => env('LOGIN_FLOW_SECURITY_ENABLED', true),
    'force_https_on_login'        => env('LOGIN_FORCE_HTTPS', true),

    'security_headers' => [
        'X-Frame-Options'           => 'DENY',
        'X-Content-Type-Options'  => 'nosniff',
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Cache-Control'             => 'no-store, no-cache, must-revalidate, max-age=0',
    ],

    /*
    | Named routes that receive SecureLoginFlow middleware when published.
    | Apply manually: Route::middleware('login.security')->group(...)
    */
    'login_route_names' => array_filter(array_map('trim', explode(',', env('LOGIN_ROUTE_NAMES', 'login,register,password.request,password.reset,password.email,password.confirm')))),
];

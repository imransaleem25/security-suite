<?php

return [
    /*
    | URL prefix for package routes (e.g. /security/audit-logs).
    */
    'route_prefix' => env('SECURITY_SUITE_ROUTE_PREFIX', 'security'),

    /*
    | Layout blade used by admin UI views (audit, password history, HTTP logs).
    */
    'layout' => env('SECURITY_SUITE_LAYOUT', 'layouts.app'),

    /*
    | Layout for the forced password-change screen.
    */
    'password_expired_layout' => env('SECURITY_SUITE_PASSWORD_EXPIRED_LAYOUT', 'layouts.guest'),

    /*
    | Named route to redirect after password expiry update (null = use redirect()->intended('/')).
    */
    'home_route' => env('SECURITY_SUITE_HOME_ROUTE', null),

    /*
    | Named route for idle-timeout logout redirect.
    */
    'login_route' => env('SECURITY_SUITE_LOGIN_ROUTE', 'login'),

    /*
    | Session idle timeout in minutes.
    */
    'idle_timeout_minutes' => (int) env('SECURITY_SUITE_IDLE_TIMEOUT', 15),

    /*
    | Seconds before timeout to show a client-side warning (used by /idle-config).
    */
    'idle_warn_before_seconds' => 60,

    /*
    | Route names that refresh the idle timer without running the idle check first.
    */
    'idle_refresh_route_names' => [
        'idle.ping',
        'idle.config',
        'logout',
        'login',
        'password.*',
    ],

    /*
    | Route names excluded from idle-timer refresh (e.g. background polling in the host app).
    */
    'idle_no_refresh_route_names' => [],
];

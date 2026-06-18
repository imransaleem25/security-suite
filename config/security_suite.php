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

    /*
    | Admin Logs menu (Bootstrap). Include in your layout:
    |   @includeWhen(config('security_suite.menu.enabled'), 'security-suite::partials.logs-menu')
    | Or sidebar mode:
    |   @includeWhen(config('security_suite.menu.enabled'), 'security-suite::partials.logs-menu-sidebar')
    */
    'menu' => [
        'enabled' => env('SECURITY_SUITE_MENU_ENABLED', true),
        'mode'    => env('SECURITY_SUITE_MENU_MODE', 'dropdown'), // dropdown|sidebar
        'label'   => env('SECURITY_SUITE_MENU_LABEL', 'Logs'),
        'icon'    => env('SECURITY_SUITE_MENU_ICON', 'bi-journal-text'),
        'viewer_role' => env('AUDIT_VIEWER_ROLE', 'admin'),
        'items' => [
            'audit'            => env('SECURITY_SUITE_MENU_AUDIT', true),
            'password_history' => env('SECURITY_SUITE_MENU_PASSWORD_HISTORY', true),
            'failed_login'     => env('SECURITY_SUITE_MENU_FAILED_LOGIN', true),
            'login_logout'     => env('SECURITY_SUITE_MENU_LOGIN_LOGOUT', true),
            'http'             => env('SECURITY_SUITE_MENU_HTTP', true),
        ],
    ],
];

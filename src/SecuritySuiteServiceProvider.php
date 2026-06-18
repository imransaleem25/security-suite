<?php

namespace ImranSaleem\SecuritySuite;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use ImranSaleem\SecuritySuite\Listeners\LogFailedLogin;
use ImranSaleem\SecuritySuite\Listeners\LogSuccessfulLogin;
use ImranSaleem\SecuritySuite\Listeners\LogUserLogout;

class SecuritySuiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/security_suite.php', 'security_suite');
        $this->mergeConfigFrom(__DIR__ . '/../config/audit.php',          'audit');
        $this->mergeConfigFrom(__DIR__ . '/../config/login_security.php', 'login_security');
        $this->mergeConfigFrom(__DIR__ . '/../config/password_policy.php','password_policy');
        $this->mergeConfigFrom(__DIR__ . '/../config/http_logger.php',    'http_logger');

        $this->app->singleton(Services\AuditService::class);
        $this->app->singleton(Services\LoginBlockService::class);
        $this->app->singleton(Services\LoginLogService::class);
        $this->app->singleton(Services\PasswordPolicyService::class);
        $this->app->singleton(Services\IdleTimeoutService::class);
    }

    public function boot(Router $router): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/security_suite.php'  => config_path('security_suite.php'),
            __DIR__ . '/../config/audit.php'           => config_path('audit.php'),
            __DIR__ . '/../config/login_security.php'  => config_path('login_security.php'),
            __DIR__ . '/../config/password_policy.php' => config_path('password_policy.php'),
            __DIR__ . '/../config/http_logger.php'     => config_path('http_logger.php'),
        ], 'security-suite-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'security-suite-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views/' => resource_path('views/vendor/security-suite'),
        ], 'security-suite-views');

        // Publish idle-timeout script for app layout
        $this->publishes([
            __DIR__ . '/../resources/js/idle-timeout.js' => public_path('vendor/security-suite/idle-timeout.js'),
        ], 'security-suite-assets');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'security-suite');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register middleware aliases
        $router->aliasMiddleware('idle.timeout',     Middleware\CheckIdleTimeout::class);
        $router->aliasMiddleware('password.expiry',   Middleware\CheckPasswordExpiry::class);
        $router->aliasMiddleware('http.logger',       Middleware\LogHttpRequest::class);
        $router->aliasMiddleware('login.security',    Middleware\SecureLoginFlow::class);

        // Auth event listeners (login / logout / failed login logging)
        Event::listen(Login::class,  LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogUserLogout::class);
        Event::listen(Failed::class, LogFailedLogin::class);

        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/security.php');
    }
}

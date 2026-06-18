# Local testing

Use a Laravel app with a Composer **path repository** so changes in this package are picked up immediately (symlinked).

## Quick start (demo app)

A ready-made demo lives next to this repo:

```
c:\wamp64\www\github\security-suite-demo
```

From the demo directory:

```bash
cd c:\wamp64\www\github\security-suite-demo
php artisan serve --port=8088
```

Open http://127.0.0.1:8088 (use another port if 8088 is taken; port 8000 may already be in use on WAMP).

### Demo credentials

| User | Email | Password | Role |
|------|-------|----------|------|
| Admin | admin@example.com | password | admin |
| User | user@example.com | password | user |

Admin sees the **Logs** menu and all `/security/*` admin pages.

---

## Use your own Laravel app

Add to your app's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../security-suite",
        "options": { "symlink": true }
    }
]
```

Then:

```bash
composer require imransaleem/security-suite:@dev spatie/laravel-permission
php artisan vendor:publish --tag=security-suite-config
php artisan vendor:publish --tag=security-suite-migrations
php artisan vendor:publish --tag=security-suite-assets
php artisan migrate
```

Wire middleware in `app/Http/Kernel.php` `web` group:

```php
\ImranSaleem\SecuritySuite\Middleware\CheckPasswordExpiry::class,
\ImranSaleem\SecuritySuite\Middleware\CheckIdleTimeout::class,
```

Add to your layout navbar:

```blade
@includeWhen(config('security_suite.menu.enabled'), 'security-suite::partials.logs-menu-wrapper')
@include('security-suite::partials.idle-timeout-script')
```

Apply login security to auth routes:

```php
Route::middleware(['guest', 'login.security'])->group(function () {
    // login / register / password reset
});
```

---

## After changing package code

Path symlink picks up PHP changes immediately. Re-publish only when config/views/assets change:

```bash
php artisan vendor:publish --tag=security-suite-config --force
php artisan vendor:publish --tag=security-suite-assets --force
composer dump-autoload
```

---

## Recreate the demo app

```bash
composer create-project laravel/laravel:^8.0 security-suite-demo
# add path repository + follow steps in database/seeders/DemoSeeder.php
```

The demo requires PHP 7.4+ and Laravel 8 (matches this machine's PHP 7.4.26).

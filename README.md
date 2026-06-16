# Security Suite — Laravel Security Package
[![Latest Version on Packagist](https://img.shields.io/packagist/v/imransaleem/security-suite.svg)](https://packagist.org/packages/imransaleem/security-suite)
[![License](https://img.shields.io/packagist/l/imransaleem/security-suite.svg)](LICENSE)

A plug-and-play Laravel package for audit logging, password policy, login lockout, idle session timeout, password history UI, and optional HTTP request logging.

---

## Features

| Feature | Description |
|---|---|
| **Audit Logs** | Track create/update/delete actions with before/after diff |
| **Password Policy** | Complexity rules, expiry, history (prevent reuse of last N passwords) |
| **Login Block** | Auto-block after N failed attempts — temporary lock or admin unblock |
| **Idle Timeout** | End session after inactivity (server middleware + optional client ping) |
| **Password History UI** | Admin views for per-user and global password change history |
| **HTTP Logger** | Optional request/response logging with admin viewer |

---

## Requirements

- PHP 7.4+
- Laravel 7.4, 8, 9, or 10
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission) suggested for admin UI routes (`role` middleware)

---

## Installation

### From GitHub (before Packagist)

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/imransaleem/security-suite"
    }
],
"require": {
    "imransaleem/security-suite": "dev-main"
}
```

```bash
composer require imransaleem/security-suite:dev-main
```

### From Packagist (after submission)

```bash
composer require imransaleem/security-suite
```

### Publish & migrate

```bash
php artisan vendor:publish --tag=security-suite-config
php artisan vendor:publish --tag=security-suite-migrations
php artisan vendor:publish --tag=security-suite-views   # optional
php artisan migrate
```

### Middleware (`app/Http/Kernel.php`)

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \ImranSaleem\SecuritySuite\Middleware\CheckPasswordExpiry::class,
        \ImranSaleem\SecuritySuite\Middleware\CheckIdleTimeout::class,
        // optional:
        // \ImranSaleem\SecuritySuite\Middleware\LogHttpRequest::class,
    ],
];
```

### User model

Add to `$fillable` and `$casts`:

```php
'login_attempts', 'locked_until', 'is_blocked',
'password_changed_at', 'forced_change_password',
```

```php
'locked_until'           => 'datetime',
'is_blocked'             => 'boolean',
'forced_change_password' => 'boolean',
```

### Host app layout

Admin views use `config('security_suite.layout')` (default `layouts.app`). The password-expired screen uses `security_suite.password_expired_layout` (default `layouts.guest`).

---

## Routes

All package routes are prefixed (default `/security`):

| Route name | Path |
|---|---|
| `idle.ping` | `GET /security/idle-ping` |
| `idle.config` | `GET /security/idle-config` |
| `password.expired` | `GET /security/password-expired` |
| `audit.logs.index` | `GET /security/audit-logs` |
| `http.logs.index` | `GET /security/http-logs` |

Change the prefix with `SECURITY_SUITE_ROUTE_PREFIX` in `.env`.

---

## Configuration

### `.env`

```env
SECURITY_SUITE_ROUTE_PREFIX=security
SECURITY_SUITE_LAYOUT=layouts.app
SECURITY_SUITE_PASSWORD_EXPIRED_LAYOUT=layouts.guest
SECURITY_SUITE_HOME_ROUTE=dashboard
SECURITY_SUITE_LOGIN_ROUTE=login
SECURITY_SUITE_IDLE_TIMEOUT=15

LOGIN_BLOCK_MODE=temporary
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15

PASSWORD_MIN_LENGTH=12
PASSWORD_EXPIRY_DAYS=30
PASSWORD_HISTORY_COUNT=2

AUDIT_VIEWER_ROLE=admin
HTTP_LOGGER_ENABLED=true
```

### Custom modules & actions

Publish `config/audit.php` and set:

- `modules` — filter dropdown list; leave `[]` to load modules from the database
- `actions` — allowed query filters; leave `[]` to allow any action

### Idle timeout (client-side)

```blade
@auth
<script>
(function () {
    fetch('{{ route("idle.config") }}')
        .then(r => r.json())
        .then(cfg => {
            const TIMEOUT_MS = cfg.timeout_ms;
            const WARN_BEFORE = cfg.warn_before;
            let idleTimer, warnTimer;
            function reset() {
                clearTimeout(idleTimer);
                clearTimeout(warnTimer);
                fetch('{{ route("idle.ping") }}', { credentials: 'same-origin' });
                warnTimer = setTimeout(() => alert('Session expiring soon!'), TIMEOUT_MS - WARN_BEFORE);
                idleTimer = setTimeout(() => {
                    const form = document.getElementById('logout-form');
                    if (form) form.submit();
                }, TIMEOUT_MS);
            }
            ['mousemove','keydown','click','scroll'].forEach(e =>
                document.addEventListener(e, reset, { passive: true }));
            reset();
        });
})();
</script>
@endauth
```

### Background polling (idle)

Add host route names to `config/security_suite.php` → `idle_no_refresh_route_names` so polling does not reset the idle timer.

---

## Usage

### Audit logging

```php
use ImranSaleem\SecuritySuite\Models\AuditLog;

AuditLog::write('users', 'created', $user, [], $user->only(['name', 'email']));
AuditLog::write('orders', 'updated', $order, ['status' => 'pending'], ['status' => 'shipped']);
```

### Login block

```php
use ImranSaleem\SecuritySuite\Services\LoginBlockService;

if ($user && $error = $this->blocker->checkBlock($user)) {
    return back()->withErrors(['email' => $error]);
}
```

### Password policy

```php
use ImranSaleem\SecuritySuite\Services\PasswordPolicyService;

$request->validate(['new_password' => ['required', 'confirmed', $this->policy->complexityRule()]]);
if ($this->policy->isReused($user, $request->new_password)) { /* ... */ }
```

---

## Package structure

```
security-suite/
├── config/
│   ├── security_suite.php
│   ├── audit.php
│   ├── login_security.php
│   ├── password_policy.php
│   └── http_logger.php
├── database/migrations/
├── routes/security.php
├── resources/views/
└── src/
```

---

## License

MIT

<?php

namespace ImranSaleem\SecuritySuite\Services;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use ImranSaleem\SecuritySuite\Models\LoginLog;

class LoginLogService
{
    public function isEnabled(): bool
    {
        return (bool) config('login_security.logging_enabled', true);
    }

    public function logSuccess(?Authenticatable $user, ?string $email = null): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->create(LoginLog::EVENT_LOGIN_SUCCESS, $user, $email);
    }

    public function logFailure(?Authenticatable $user, ?string $email = null, ?string $reason = null): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->create(LoginLog::EVENT_LOGIN_FAILED, $user, $email, $reason);
    }

    public function logLogout(?Authenticatable $user): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->create(LoginLog::EVENT_LOGOUT, $user);
    }

    protected function create(string $event, ?Authenticatable $user = null, ?string $email = null, ?string $reason = null): void
    {
        $request = request();

        LoginLog::create([
            'user_id'        => $user ? $user->getAuthIdentifier() : null,
            'email'          => $email ?? ($user ? ($user->email ?? null) : null),
            'event'          => $event,
            'ip_address'     => $request instanceof Request ? $request->ip() : null,
            'user_agent'     => $request instanceof Request ? $request->userAgent() : null,
            'failure_reason' => $reason,
        ]);
    }
}

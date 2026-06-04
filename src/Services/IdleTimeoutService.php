<?php

namespace ImranSaleem\SecuritySuite\Services;

class IdleTimeoutService
{
    protected int $timeoutMinutes;
    protected int $warnBeforeSeconds;

    public function __construct()
    {
        $this->timeoutMinutes    = (int) config('security_suite.idle_timeout_minutes', 15);
        $this->warnBeforeSeconds = (int) config('security_suite.idle_warn_before_seconds', 60);
    }

    public function getTimeoutSeconds(): int
    {
        return $this->timeoutMinutes * 60;
    }

    public function getTimeoutMs(): int
    {
        return $this->getTimeoutSeconds() * 1000;
    }

    public function getWarnBeforeMs(): int
    {
        return $this->warnBeforeSeconds * 1000;
    }

    public function isIdle(): bool
    {
        $lastActivity = session('last_activity_at');
        if (!$lastActivity) {
            return false;
        }
        return (time() - $lastActivity) > $this->getTimeoutSeconds();
    }

    public function touch(): void
    {
        session(['last_activity_at' => time()]);
    }

    public function clear(): void
    {
        session()->forget('last_activity_at');
    }
}

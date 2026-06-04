<?php

namespace ImranSaleem\SecuritySuite\Services;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;

class LoginBlockService
{
    public int    $maxAttempts;
    public string  $blockMode;
    protected int  $lockoutMinutes;

    public function __construct()
    {
        $this->maxAttempts    = (int) config('login_security.max_attempts', 5);
        $this->blockMode      = config('login_security.block_mode', 'temporary');
        $this->lockoutMinutes = (int) config('login_security.lockout_minutes', 15);
    }

    /**
     * Check if the user is currently blocked or temp-locked.
     * Returns an error message string, or null if the user can proceed.
     */
    public function checkBlock(Authenticatable $user): ?string
    {
        if ($user->is_blocked) {
            return 'Your account has been blocked. Please contact the administrator.';
        }

        if ($user->locked_until && Carbon::now()->lt($user->locked_until)) {
            $remaining = (int) ceil(Carbon::now()->floatDiffInMinutes($user->locked_until));
            return "Too many failed attempts. Try again in {$remaining} minute(s).";
        }

        // Auto-reset expired temp lock
        if ($user->locked_until && Carbon::now()->gte($user->locked_until)) {
            $user->login_attempts = 0;
            $user->locked_until   = null;
            $user->save();
        }

        return null;
    }

    /**
     * Record a failed attempt and apply block if threshold reached.
     */
    public function recordFailure(Authenticatable $user): void
    {
        $user->login_attempts = ($user->login_attempts ?? 0) + 1;

        if ($user->login_attempts >= $this->maxAttempts) {
            if ($this->blockMode === 'admin') {
                $user->is_blocked   = true;
                $user->locked_until = null;
            } else {
                $user->locked_until   = Carbon::now()->addMinutes($this->lockoutMinutes);
                $user->login_attempts = 0;
            }
        }

        $user->save();
    }

    /**
     * Reset all block state on successful login.
     */
    public function resetOnSuccess(Authenticatable $user): void
    {
        $user->login_attempts = 0;
        $user->locked_until   = null;
        $user->save();
    }

    /**
     * Admin unblock — clears all block state.
     */
    public function unblock(Authenticatable $user): void
    {
        $user->is_blocked     = false;
        $user->login_attempts = 0;
        $user->locked_until   = null;
        $user->save();
    }

    public function isBlocked(Authenticatable $user): bool
    {
        return (bool) $user->is_blocked;
    }

    public function isTempLocked(Authenticatable $user): bool
    {
        return $user->locked_until && Carbon::now()->lt($user->locked_until);
    }
}

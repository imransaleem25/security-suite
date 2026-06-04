<?php

namespace ImranSaleem\SecuritySuite\Services;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use ImranSaleem\SecuritySuite\Models\PasswordHistory;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordPolicyService
{
    protected int $minLength;
    protected int $historyCount;
    protected int $expiryDays;

    public function __construct()
    {
        $this->minLength    = (int) config('password_policy.min_length', 12);
        $this->historyCount = (int) config('password_policy.history_count', 2);
        $this->expiryDays   = (int) config('password_policy.expiry_days', 30);
    }

    /**
     * Returns a Laravel Password rule object with configured complexity.
     */
    public function complexityRule(): PasswordRule
    {
        return PasswordRule::min($this->minLength)->letters()->mixedCase()->numbers()->symbols();
    }

    /**
     * Check if the new password was used in the last N passwords.
     */
    public function isReused(Authenticatable $user, string $newPassword): bool
    {
        $userId = (int) $user->id;
        $recent = PasswordHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take($this->historyCount)
            ->pluck('password');

        foreach ($recent as $hash) {
            if (Hash::check($newPassword, $hash)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save current password to history and trim old entries.
     */
    public function saveToHistory(Authenticatable $user): void
    {
        $userId = (int) $user->id;

        PasswordHistory::create([
            'user_id'  => $userId,
            'password' => $user->password,
        ]);

        $ids = PasswordHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->pluck('id')
            ->slice($this->historyCount);

        if ($ids->isNotEmpty()) {
            PasswordHistory::whereIn('id', $ids)->delete();
        }
    }

    /**
     * Check if the user's password has expired.
     */
    public function isExpired(Authenticatable $user): bool
    {
        if (!$user->password_changed_at) {
            return false;
        }
        return now()->diffInDays($user->password_changed_at) >= $this->expiryDays;
    }

    /**
     * Mark password as changed now.
     */
    public function markChanged(Authenticatable $user): void
    {
        $user->password_changed_at    = now();
        $user->forced_change_password = false;
        $user->save();
    }
}

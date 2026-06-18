<?php

namespace ImranSaleem\SecuritySuite\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    public const EVENT_LOGIN_SUCCESS = 'login_success';
    public const EVENT_LOGIN_FAILED  = 'login_failed';
    public const EVENT_LOGOUT          = 'logout';

    protected $fillable = [
        'user_id',
        'email',
        'event',
        'ip_address',
        'user_agent',
        'failure_reason',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function scopeFailed($query)
    {
        return $query->where('event', self::EVENT_LOGIN_FAILED);
    }

    public function scopeLoginLogout($query)
    {
        return $query->whereIn('event', [self::EVENT_LOGIN_SUCCESS, self::EVENT_LOGOUT]);
    }
}

<?php

namespace ImranSaleem\SecuritySuite\Models;

use Illuminate\Database\Eloquent\Model;

class HttpLog extends Model
{
    protected $fillable = [
        'user_id', 'method', 'url', 'route_name',
        'status_code', 'ip_address', 'user_agent',
        'request_payload', 'response_payload', 'duration_ms',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}

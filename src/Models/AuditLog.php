<?php

namespace ImranSaleem\SecuritySuite\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'module', 'action',
        'auditable_type', 'auditable_id', 'auditable_name',
        'old_values', 'new_values',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Static shorthand — delegates to AuditService.
     */
    public static function write(string $module, string $action, $model, array $old = [], array $new = []): void
    {
        app(\ImranSaleem\SecuritySuite\Services\AuditService::class)->log($module, $action, $model, $old, $new);
    }
}

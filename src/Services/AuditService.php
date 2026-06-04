<?php

namespace ImranSaleem\SecuritySuite\Services;

use ImranSaleem\SecuritySuite\Models\AuditLog;

class AuditService
{
    /**
     * Write an audit log entry.
     *
     * @param string $module     e.g. 'users', 'orders', 'settings'
     * @param string $action     e.g. 'created', 'updated', 'deleted'
     * @param object $model      Eloquent model being audited
     * @param array  $oldValues  State before the change
     * @param array  $newValues  State after the change
     */
    public function log(string $module, string $action, $model, array $oldValues = [], array $newValues = []): void
    {
        $name = $model->name
            ?? $model->title
            ?? $model->label
            ?? $model->email
            ?? $model->username
            ?? (isset($model->id) ? (string) $model->id : null);

        AuditLog::create([
            'user_id'        => auth()->id(),
            'module'         => $module,
            'action'         => $action,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->id ?? null,
            'auditable_name' => $name,
            'old_values'     => !empty($oldValues) ? $oldValues : null,
            'new_values'     => !empty($newValues) ? $newValues : null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}

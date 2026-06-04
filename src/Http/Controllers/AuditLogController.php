<?php

namespace ImranSaleem\SecuritySuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ImranSaleem\SecuritySuite\Models\AuditLog;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:' . config('audit.viewer_role', 'admin')]);
    }

    public function index(Request $request)
    {
        $allowedModules = config('audit.modules', []);
        $allowedActions = config('audit.actions', []);

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('module')) {
            if (empty($allowedModules) || in_array($request->module, $allowedModules, true)) {
                $query->where('module', $request->module);
            }
        }
        if ($request->filled('action')) {
            if (empty($allowedActions) || in_array($request->action, $allowedActions, true)) {
                $query->where('action', $request->action);
            }
        }
        if ($request->filled('user_id') && is_numeric($request->user_id)) {
            $query->where('user_id', (int) $request->user_id);
        }
        if ($request->filled('from_date') && strtotime($request->from_date)) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date') && strtotime($request->to_date)) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('searchRecord')) {
            $query->where('auditable_name', 'like', '%' . $request->searchRecord . '%');
        }

        $logs = $query->paginate(config('audit.per_page', 20))->withQueryString();
        $users = app(config('auth.providers.users.model'))->orderBy('name')->get(['id', 'name']);

        $modules = !empty($allowedModules)
            ? $allowedModules
            : AuditLog::query()->distinct()->orderBy('module')->pluck('module')->filter()->values()->all();

        return view('security-suite::audit.index', compact('logs', 'users', 'modules'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('security-suite::audit.show', compact('auditLog'));
    }
}

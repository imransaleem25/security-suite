<?php

namespace ImranSaleem\SecuritySuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ImranSaleem\SecuritySuite\Models\LoginLog;

class LoginLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:' . config('audit.viewer_role', 'admin')]);
    }

    public function index(Request $request)
    {
        return $this->render($request, LoginLog::query()->loginLogout(), 'Login & Logout Logs', 'login-logout');
    }

    public function failed(Request $request)
    {
        return $this->render($request, LoginLog::query()->failed(), 'Failed Login Logs', 'failed-login');
    }

    protected function render(Request $request, $query, string $title, string $pageKey)
    {
        $query = $query->with('user')->orderBy('created_at', 'desc');

        if ($request->filled('user_id') && is_numeric($request->user_id)) {
            $query->where('user_id', (int) $request->user_id);
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('from_date') && strtotime($request->from_date)) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date') && strtotime($request->to_date)) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs  = $query->paginate(config('login_security.logs_per_page', 20))->withQueryString();
        $users = app(config('auth.providers.users.model'))->orderBy('name')->get(['id', 'name']);

        return view('security-suite::login.index', compact('logs', 'users', 'title', 'pageKey'));
    }
}

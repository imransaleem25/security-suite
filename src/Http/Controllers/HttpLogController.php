<?php

namespace ImranSaleem\SecuritySuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ImranSaleem\SecuritySuite\Models\HttpLog;

class HttpLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:' . config('audit.viewer_role', 'admin')]);
    }

    public function index(Request $request)
    {
        $query = HttpLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('url_search')) {
            $query->where(function ($q) use ($request) {
                $q->where('url', 'like', '%' . $request->url_search . '%')
                  ->orWhere('route_name', 'like', '%' . $request->url_search . '%');
            });
        }
        if ($request->filled('method_filter')) {
            $query->where('method', strtoupper($request->method_filter));
        }
        if ($request->filled('status_code') && is_numeric($request->status_code)) {
            $query->where('status_code', (int) $request->status_code);
        }
        if ($request->filled('user_id') && is_numeric($request->user_id)) {
            $query->where('user_id', (int) $request->user_id);
        }
        if ($request->filled('from_date') && strtotime($request->from_date)) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        $logs  = $query->paginate(config('http_logger.per_page', 20))->withQueryString();
        $users = app(config('auth.providers.users.model'))->orderBy('name')->get(['id', 'name']);

        return view('security-suite::http.index', compact('logs', 'users'));
    }

    public function show(HttpLog $httpLog)
    {
        return view('security-suite::http.show', compact('httpLog'));
    }
}

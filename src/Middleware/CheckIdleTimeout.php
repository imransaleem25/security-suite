<?php

namespace ImranSaleem\SecuritySuite\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ImranSaleem\SecuritySuite\Services\IdleTimeoutService;

class CheckIdleTimeout
{
    protected IdleTimeoutService $idle;

    public function __construct(IdleTimeoutService $idle)
    {
        $this->idle = $idle;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $refreshRoutes = config('security_suite.idle_refresh_route_names', []);
        if (!empty($refreshRoutes) && $request->routeIs($refreshRoutes)) {
            $this->idle->touch();
            return $next($request);
        }

        $noRefreshRoutes = config('security_suite.idle_no_refresh_route_names', []);
        if (!empty($noRefreshRoutes) && $request->routeIs($noRefreshRoutes)) {
            return $next($request);
        }

        if ($this->idle->isIdle()) {
            $this->idle->clear();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['idle_timeout' => true], 401);
            }

            return redirect()->route(config('security_suite.login_route', 'login'))
                ->with('idle_timeout', 'Your session expired due to inactivity. Please log in again.');
        }

        $this->idle->touch();
        return $next($request);
    }
}

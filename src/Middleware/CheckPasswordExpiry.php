<?php

namespace ImranSaleem\SecuritySuite\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ImranSaleem\SecuritySuite\Services\PasswordPolicyService;

class CheckPasswordExpiry
{
    protected PasswordPolicyService $policy;

    public function __construct(PasswordPolicyService $policy)
    {
        $this->policy = $policy;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        if ($request->routeIs(
            'password.change', 'password.validate', 'password.request',
            'password.email', 'password.reset', 'password.update',
            'password.expired', 'password.expired.update', 'logout'
        )) {
            return $next($request);
        }

        $user = Auth::user();

        if ($this->policy->isExpired($user) || $user->forced_change_password) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'password_expired'], 403);
            }
            return redirect()->route('password.expired');
        }

        return $next($request);
    }
}

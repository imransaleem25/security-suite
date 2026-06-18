<?php

namespace ImranSaleem\SecuritySuite\Middleware;

use Closure;
use Illuminate\Http\Request;
use ImranSaleem\SecuritySuite\Services\LoginBlockService;

class SecureLoginFlow
{
    protected LoginBlockService $blocker;

    public function __construct(LoginBlockService $blocker)
    {
        $this->blocker = $blocker;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!config('login_security.login_flow_security_enabled', true)) {
            return $next($request);
        }

        if (config('login_security.force_https_on_login', true) && !$request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri());
        }

        if ($request->isMethod('POST')) {
            $user = $this->resolveUserFromRequest($request);
            if ($user && ($message = $this->blocker->checkBlock($user))) {
                return redirect()->back()
                    ->withInput($request->only('email', 'username'))
                    ->withErrors(['email' => $message]);
            }
        }

        $response = $next($request);

        if (method_exists($response, 'header')) {
            $headers = config('login_security.security_headers', []);
            foreach ($headers as $name => $value) {
                $response->header($name, $value);
            }
        }

        return $response;
    }

    protected function resolveUserFromRequest(Request $request)
    {
        $loginField = config('login_security.login_field', 'email');
        $value      = $request->input($loginField);

        if (!is_string($value) || $value === '') {
            return null;
        }

        $userModel = app(config('auth.providers.users.model'));

        return $userModel->where($loginField, $value)->first();
    }
}

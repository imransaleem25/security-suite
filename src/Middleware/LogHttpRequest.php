<?php

namespace ImranSaleem\SecuritySuite\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ImranSaleem\SecuritySuite\Models\HttpLog;

class LogHttpRequest
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('http_logger.enabled', true)) {
            return $next($request);
        }

        // Skip excluded methods
        if (in_array($request->method(), config('http_logger.exclude_methods', []))) {
            return $next($request);
        }

        // Skip excluded URIs
        foreach (config('http_logger.exclude_uris', []) as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        $startTime = microtime(true);

        $response = $next($request);

        // Only log after response is ready
        try {
            $duration = (int) round((microtime(true) - $startTime) * 1000);
            $maxLen   = (int) config('http_logger.max_body_length', 2000);

            // Request payload
            $requestPayload = null;
            if (in_array($request->method(), config('http_logger.log_body_methods', ['POST', 'PUT', 'PATCH']))) {
                $body = $request->except(['password', 'new_password', 'current_password', 'new_password_confirmation', 'captcha', '_token']);
                if (!empty($body)) {
                    $encoded = json_encode($body);
                    $requestPayload = $maxLen > 0 && strlen($encoded) > $maxLen
                        ? json_decode(substr($encoded, 0, $maxLen), true)
                        : $body;
                }
            }

            // Response payload
            $responsePayload = null;
            if (config('http_logger.log_response', false)) {
                $content = $response->getContent();
                if ($content && $maxLen > 0 && strlen($content) > $maxLen) {
                    $content = substr($content, 0, $maxLen) . '...[truncated]';
                }
                $decoded = json_decode($content, true);
                $responsePayload = json_last_error() === JSON_ERROR_NONE ? $decoded : ['raw' => $content];
            }

            // Route name
            $routeName = null;
            try {
                $routeName = $request->route() ? $request->route()->getName() : null;
            } catch (\Throwable $e) {}

            HttpLog::create([
                'user_id'          => Auth::id(),
                'method'           => $request->method(),
                'url'              => $request->fullUrl(),
                'route_name'       => $routeName,
                'status_code'      => $response->getStatusCode(),
                'ip_address'       => $request->ip(),
                'user_agent'       => $request->userAgent(),
                'request_payload'  => $requestPayload,
                'response_payload' => $responsePayload,
                'duration_ms'      => $duration,
            ]);
        } catch (\Throwable $e) {
            // Never break the request if logging fails
        }

        return $response;
    }
}

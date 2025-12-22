<?php

namespace App\Http\Middleware;

use App\Services\LoggingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class ErrorHandlingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $startTime = microtime(true);

        try {
            // Log user activity for important routes
            $this->logUserActivity($request);

            $response = $next($request);

            // Log performance for slow requests
            $executionTime = microtime(true) - $startTime;
            if ($executionTime > 2.0) { // Log requests taking more than 2 seconds
                LoggingService::logPerformance($request->route()?->getName() ?? $request->path(), $executionTime, [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'user_id' => Auth::id(),
                ]);
            }

            return $response;

        } catch (Throwable $e) {
            // Log the error with full context
            LoggingService::logSystemError($e, 'middleware_error_handler');

            // Re-throw the exception to let Laravel's exception handler deal with it
            throw $e;
        }
    }

    /**
     * Log user activity for important routes
     */
    private function logUserActivity(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        $importantRoutes = [
            'sales.store' => 'sale_created',
            'purchases.store' => 'purchase_created',
            'products.store' => 'product_created',
            'products.update' => 'product_updated',
            'suppliers.store' => 'supplier_created',
            'suppliers.update' => 'supplier_updated',

        ];

        $routeName = $request->route()?->getName();

        if (isset($importantRoutes[$routeName])) {
            LoggingService::logUserActivity($importantRoutes[$routeName], [
                'route' => $routeName,
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);
        }
    }

    /**
     * Handle the response after it's been processed
     */
    public function terminate(Request $request, SymfonyResponse $response): void
    {
        // Log failed requests (4xx and 5xx status codes)
        if ($response->getStatusCode() >= 400) {
            LoggingService::logSystemError(
                new \Exception("HTTP {$response->getStatusCode()} response"),
                'http_error_response',
            );
        }

        // Log security-related events
        $this->logSecurityEvents($request, $response);
    }

    /**
     * Log security-related events
     */
    private function logSecurityEvents(Request $request, SymfonyResponse $response): void
    {
        // Log failed login attempts
        if ($request->is('login') && $request->isMethod('POST') && $response->getStatusCode() === 302) {
            $redirectLocation = $response->headers->get('Location');
            if ($redirectLocation && str_contains($redirectLocation, 'login')) {
                LoggingService::logSecurityEvent('failed_login_attempt', [
                    'email' => $request->input('email'),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        }

        // Log unauthorized access attempts
        if ($response->getStatusCode() === 403) {
            LoggingService::logSecurityEvent('unauthorized_access_attempt', [
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => Auth::id(),
                'user_role' => Auth::user()?->role?->name,
            ]);
        }

        // Log CSRF token mismatches
        if ($response->getStatusCode() === 419) {
            LoggingService::logSecurityEvent('csrf_token_mismatch', [
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => Auth::id(),
            ]);
        }

        // Log suspicious activity (too many requests)
        if ($response->getStatusCode() === 429) {
            LoggingService::logSecurityEvent('rate_limit_exceeded', [
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => Auth::id(),
            ]);
        }
    }
}

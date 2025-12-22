<?php

namespace App\Http\Middleware;

use App\Models\AccessCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessCodeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is already authenticated
        if (auth()->check()) {
            return $next($request);
        }

        // Check for access code in session or request
        $accessCode = $request->session()->get('access_code') ?? $request->input('access_code');

        if (!$accessCode) {
            return redirect()->route('access-code.form')
                ->with('error', 'Access code required to continue.');
        }

        // Validate access code
        $code = AccessCode::where('code', $accessCode)->first();

        if (!$code || !$code->isValid()) {
            return redirect()->route('access-code.form')
                ->with('error', 'Invalid or expired access code.');
        }

        // Store access code in session for subsequent requests
        $request->session()->put('access_code', $accessCode);
        $request->session()->put('tenant_id', $code->tenant_id);

        return $next($request);
    }
}

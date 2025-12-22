<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        \Log::info('ResolveTenant middleware', [
            'path' => $request->path(),
            'tenant_id' => $tenant?->id,
            'auth_check' => auth()->check(),
            'user_id' => auth()->user()?->id,
            'user_tenant_id' => auth()->user()?->tenant_id,
        ]);

        if ($tenant) {
            // Set current tenant in application container
            app()->instance('current_tenant', $tenant);

            // Set tenant context for the user
            if (auth()->check()) {
                $user = auth()->user();
                // Allow super admins (tenant_id: null) to access any tenant
                if ($user->tenant_id !== null && $user->tenant_id !== $tenant->id) {
                    // User doesn't belong to this tenant
                    \Log::error('ResolveTenant: User tenant mismatch', [
                        'user_id' => $user->id,
                        'user_tenant_id' => $user->tenant_id,
                        'resolved_tenant_id' => $tenant->id,
                    ]);
                    auth()->logout();
                    return redirect()->route('login')->with('error', 'Access denied to this tenant.');
                }
            }
        } else if (auth()->check()) {
            // If no tenant was resolved from URL/domain, try to use the authenticated user's tenant
            if (auth()->user()->tenant_id) {
                $userTenant = auth()->user()->tenant;
                if ($userTenant) {
                    app()->instance('current_tenant', $userTenant);
                    \Log::info('ResolveTenant: Using user tenant', [
                        'user_id' => auth()->user()->id,
                        'tenant_id' => $userTenant->id,
                    ]);
                }
            } else if (auth()->user()->is_super_admin) {
                // For super admins without a tenant_id, use the first available tenant
                $defaultTenant = Tenant::first();
                if ($defaultTenant) {
                    app()->instance('current_tenant', $defaultTenant);
                    \Log::info('ResolveTenant: Using default tenant for super admin', [
                        'user_id' => auth()->user()->id,
                        'tenant_id' => $defaultTenant->id,
                    ]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Resolve tenant from request
     */
    private function resolveTenant(Request $request): ?Tenant
    {
        // Method 1: Subdomain-based tenant resolution
        $host = $request->getHost();
        $subdomain = $this->getSubdomain($host);

        if ($subdomain && $subdomain !== 'www') {
            $tenant = Tenant::findBySlug($subdomain);
            if ($tenant) {
                return $tenant;
            }
        }

        // Method 2: Domain-based tenant resolution
        $tenant = Tenant::findByDomain($host);
        if ($tenant) {
            return $tenant;
        }

        // Method 3: Path-based tenant resolution (e.g., /tenant/pharmacy-name)
        $pathSegments = explode('/', trim($request->getPathInfo(), '/'));
        if (count($pathSegments) >= 2 && $pathSegments[0] === 'tenant') {
            $tenant = Tenant::findBySlug($pathSegments[1]);
            if ($tenant) {
                return $tenant;
            }
        }

        // Method 4: Session-based tenant (for super admin)
        if (session()->has('current_tenant_id')) {
            $tenant = Tenant::find(session('current_tenant_id'));
            if ($tenant) {
                return $tenant;
            }
        }

        // Method 5: User's default tenant
        if (auth()->check() && auth()->user()->tenant_id) {
            return auth()->user()->tenant;
        }

        return null;
    }

    /**
     * Extract subdomain from host
     */
    private function getSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);

        // For localhost development
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return null;
        }

        // For domains like pharmacy1.medicon.com
        if (count($parts) >= 3) {
            return $parts[0];
        }

        return null;
    }
}

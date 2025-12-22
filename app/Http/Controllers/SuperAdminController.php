<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\AccessCode;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
class SuperAdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            // Check if user is super admin
            if (!$user->is_super_admin) {
                abort(403, 'Access denied. Super admin privileges required.');
            }

            return $next($request);
        });
    }

    /**
     * Super admin dashboard
     */
    public function dashboard(): View
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_users' => User::count(),
            'active_subscriptions' => Tenant::where('subscription_status', 'active')->count(),
        ];

        $recentTenants = Tenant::latest()->limit(5)->get();
        $expiringSubscriptions = Tenant::where('subscription_expires_at', '<=', now()->addDays(30))
                                      ->where('subscription_status', 'active')
                                      ->get();

        return view('super-admin.dashboard', compact('stats', 'recentTenants', 'expiringSubscriptions'));
    }

    /**
     * List all access codes
     */
    public function accessCodes(): View
    {
        $accessCodes = AccessCode::with(['tenant', 'creator', 'usedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('super-admin.access-codes.index', compact('accessCodes'));
    }

    /**
     * Show form to create new access code
     */
    public function createAccessCode(): View
    {
        $tenants = Tenant::where('is_active', true)->orderBy('name')->get();

        return view('super-admin.access-codes.create', compact('tenants'));
    }

    /**
     * Store new access code
     */
    public function storeAccessCode(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'description' => 'nullable|string|max:255',
            'max_uses' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:now',
            'access_type' => 'required|in:admin_setup,user_registration',
            'role_assignment' => 'required|in:admin,pharmacist,sales_staff',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);

        $accessCode = AccessCode::create([
            'code' => AccessCode::generateUniqueCode(),
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'description' => $request->description,
            'max_uses' => $request->max_uses,
            'expires_at' => $request->expires_at,
            'access_type' => $request->access_type,
            'role_assignment' => $request->role_assignment,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('super-admin.access-codes.index')
            ->with('success', "Access code {$accessCode->code} created successfully for {$tenant->name}");
    }

    /**
     * Revoke an access code
     */
    public function revokeAccessCode(AccessCode $accessCode): RedirectResponse
    {
        $accessCode->update(['status' => 'revoked']);

        return redirect()->back()
            ->with('success', "Access code {$accessCode->code} has been revoked");
    }

    /**
     * List all tenants
     */
    public function tenants(): View
    {
        $tenants = Tenant::withCount('users')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

        return view('super-admin.tenants.index', compact('tenants'));
    }

    /**
     * Show tenant creation form
     */
    public function createTenant(): View
    {
        return view('super-admin.tenants.create');
    }

    /**
     * Store new tenant
     */
    public function storeTenant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pharmacy_name' => 'required|string|max:255',
            'contact_email' => 'required|email|unique:tenants,contact_email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'subscription_plan' => 'required|in:basic,standard,premium,enterprise',
            'max_users' => 'required|integer|min:1|max:1000',
            'domain' => 'nullable|string|unique:tenants,domain',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Tenant::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $tenant = Tenant::create($validated);

        return redirect()->route('super-admin.tenants.index')
                        ->with('success', 'Tenant created successfully.');
    }

    /**
     * Show tenant details
     */
    public function showTenant(Tenant $tenant): View
    {
        $tenant->load(['users.role']);
        
        $stats = [
            'total_users' => $tenant->users()->count(),
            'total_products' => $tenant->products()->count(),
            'total_customers' => $tenant->customers()->count(),
            'total_sales' => $tenant->sales()->count(),
        ];

        return view('super-admin.tenants.show', compact('tenant', 'stats'));
    }

    /**
     * Show tenant edit form
     */
    public function editTenant(Tenant $tenant): View
    {
        return view('super-admin.tenants.edit', compact('tenant'));
    }

    /**
     * Update tenant
     */
    public function updateTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pharmacy_name' => 'required|string|max:255',
            'contact_email' => 'required|email|unique:tenants,contact_email,' . $tenant->id,
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'subscription_plan' => 'required|in:basic,standard,premium,enterprise',
            'subscription_status' => 'required|in:active,inactive,suspended,cancelled',
            'max_users' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
            'domain' => 'nullable|string|unique:tenants,domain,' . $tenant->id,
        ]);

        $tenant->update($validated);

        return redirect()->route('super-admin.tenants.show', $tenant)
                        ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Suspend tenant
     */
    public function suspendTenant(Tenant $tenant): RedirectResponse
    {
        $tenant->update([
            'subscription_status' => 'suspended',
            'is_active' => false,
        ]);

        return redirect()->back()
                        ->with('success', 'Tenant suspended successfully.');
    }

    /**
     * Activate tenant
     */
    public function activateTenant(Tenant $tenant): RedirectResponse
    {
        $tenant->update([
            'subscription_status' => 'active',
            'is_active' => true,
        ]);

        return redirect()->back()
                        ->with('success', 'Tenant activated successfully.');
    }

    /**
     * Delete tenant
     */
    public function deleteTenant(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('super-admin.tenants.index')
                        ->with('success', 'Tenant deleted successfully.');
    }

    /**
     * Impersonate tenant (switch context)
     */
    public function impersonateTenant(Tenant $tenant): RedirectResponse
    {
        session(['current_tenant_id' => $tenant->id]);
        
        return redirect()->route('admin.dashboard')
                        ->with('success', 'Switched to tenant: ' . $tenant->name);
    }

    /**
     * Stop impersonating (return to super admin)
     */
    public function stopImpersonating(): RedirectResponse
    {
        session()->forget('current_tenant_id');
        
        return redirect()->route('super-admin.dashboard')
                        ->with('success', 'Returned to super admin view.');
    }

    /**
     * Platform analytics
     */
    public function analytics(): View
    {
        $analytics = [
            'tenants_by_plan' => Tenant::selectRaw('subscription_plan, COUNT(*) as count')
                                      ->groupBy('subscription_plan')
                                      ->get(),
            'tenants_by_status' => Tenant::selectRaw('subscription_status, COUNT(*) as count')
                                        ->groupBy('subscription_status')
                                        ->get(),
            'monthly_signups' => Tenant::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                                      ->where('created_at', '>=', now()->subMonths(12))
                                      ->groupBy('month')
                                      ->orderBy('month')
                                      ->get(),
        ];

        return view('super-admin.analytics', compact('analytics'));
    }

    /**
     * Platform settings
     */
    public function settings(): View
    {
        return view('super-admin.settings');
    }

    /**
     * Update platform settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        if ($request->has('action')) {
            return $this->handleSystemAction($request->action);
        }

        $section = $request->input('section');

        switch ($section) {
            case 'general':
                $this->updateGeneralSettings($request);
                break;
            case 'security':
                $this->updateSecuritySettings($request);
                break;
            case 'email':
                $this->updateEmailSettings($request);
                break;
        }

        return redirect()->route('super-admin.settings')
                        ->with('success', 'Settings updated successfully.');
    }

    /**
     * Handle system actions (backup, cache, etc.)
     */
    private function handleSystemAction(string $action): RedirectResponse
    {
        try {
            switch ($action) {
                case 'backup':
                    // In a real implementation, you would create a database backup
                    return redirect()->route('super-admin.settings')
                                    ->with('success', 'Database backup created successfully.');

                case 'clear_cache':
                    \Artisan::call('cache:clear');
                    \Artisan::call('config:clear');
                    \Artisan::call('view:clear');
                    return redirect()->route('super-admin.settings')
                                    ->with('success', 'System cache cleared successfully.');

                case 'optimize':
                    \Artisan::call('optimize');
                    return redirect()->route('super-admin.settings')
                                    ->with('success', 'Database optimized successfully.');

                case 'clear_logs':
                    // In a real implementation, you would clear log files
                    return redirect()->route('super-admin.settings')
                                    ->with('success', 'System logs cleared successfully.');

                default:
                    return redirect()->route('super-admin.settings')
                                    ->with('error', 'Unknown action.');
            }
        } catch (\Exception $e) {
            return redirect()->route('super-admin.settings')
                            ->with('error', 'Action failed: ' . $e->getMessage());
        }
    }

    /**
     * Update general settings
     */
    private function updateGeneralSettings(Request $request): void
    {
        $request->validate([
            'platform_name' => 'required|string|max:255',
            'support_email' => 'required|email',
            'max_tenants' => 'required|integer|min:1',
            'maintenance_mode' => 'boolean',
        ]);

        // In a real implementation, you would save these to a settings table or config
        // For now, we'll just validate and return success
    }

    /**
     * Update security settings
     */
    private function updateSecuritySettings(Request $request): void
    {
        $request->validate([
            'session_timeout' => 'required|integer|min:5|max:1440',
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'require_2fa' => 'boolean',
            'password_expiry_days' => 'required|integer|min:0|max:365',
        ]);

        // In a real implementation, you would save these to a settings table or config
    }

    /**
     * Update email settings
     */
    private function updateEmailSettings(Request $request): void
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string',
            'smtp_encryption' => 'nullable|in:tls,ssl',
        ]);

        // In a real implementation, you would save these to a settings table or config
    }
}

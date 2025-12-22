<?php

namespace App\Http\Controllers;

use App\Models\AccessCode;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class AccessCodeController extends Controller
{
    /**
     * Show access code entry form
     */
    public function showForm(): View
    {
        return view('auth.access-code');
    }

    /**
     * Verify access code and redirect to tenant setup
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'access_code' => 'required|string|size:8',
        ]);

        $accessCode = AccessCode::where('code', strtoupper($request->access_code))->first();

        if (!$accessCode || !$accessCode->isValid()) {
            return back()->withErrors([
                'access_code' => 'Invalid or expired access code.'
            ]);
        }

        // Store access code info in session
        session([
            'access_code' => $accessCode->code,
            'tenant_id' => $accessCode->tenant_id,
            'tenant_name' => $accessCode->tenant_name,
        ]);

        return redirect()->route('tenant.setup.form');
    }

    /**
     * Show tenant setup form
     */
    public function showSetupForm(): View
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('access-code.form')
                ->with('error', 'Access code session expired. Please enter your access code again.');
        }

        $tenant = Tenant::findOrFail($tenantId);

        return view('auth.tenant-setup', compact('tenant'));
    }

    /**
     * Complete tenant setup and create admin user
     */
    public function completeSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $tenantId = session('tenant_id');
        $accessCodeStr = session('access_code');

        if (!$tenantId || !$accessCodeStr) {
            return redirect()->route('access-code.form')
                ->with('error', 'Access code session expired. Please start over.');
        }

        $accessCode = AccessCode::where('code', $accessCodeStr)->first();
        $tenant = Tenant::findOrFail($tenantId);

        if (!$accessCode || !$accessCode->isValid()) {
            return redirect()->route('access-code.form')
                ->with('error', 'Access code is no longer valid.');
        }

        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();

        // Create tenant admin user
        $user = User::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'role_id' => $adminRole->id,
            'tenant_id' => $tenant->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Mark access code as used
        $accessCode->markAsUsed($user);

        // Clear session
        session()->forget(['access_code', 'tenant_id', 'tenant_name']);

        // Log in the new user
        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', "Welcome to {$tenant->pharmacy_name}! Your admin account has been created successfully.");
    }
}

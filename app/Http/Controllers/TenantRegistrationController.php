<?php

namespace App\Http\Controllers;

use App\Models\AccessCode;
use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class TenantRegistrationController extends Controller
{
    /**
     * Show the access code verification form
     */
    public function showAccessCodeForm(): View
    {
        return view('auth.access-code');
    }

    /**
     * Verify access code and show registration form
     */
    public function verifyAccessCode(Request $request): View|RedirectResponse
    {
        $request->validate([
            'access_code' => 'required|string|size:8',
        ]);

        $accessCode = AccessCode::where('code', strtoupper($request->access_code))
                                ->where('status', 'active')
                                ->first();

        if (!$accessCode) {
            return back()->withErrors(['access_code' => 'Invalid or expired access code.']);
        }

        // Check if code is expired
        if ($accessCode->expires_at && $accessCode->expires_at->isPast()) {
            $accessCode->update(['status' => 'expired']);
            return back()->withErrors(['access_code' => 'This access code has expired.']);
        }

        // Check if code has reached max uses
        if ($accessCode->used_count >= $accessCode->max_uses) {
            $accessCode->update(['status' => 'used']);
            return back()->withErrors(['access_code' => 'This access code has been fully used.']);
        }

        // Store access code in session for registration
        session(['verified_access_code' => $accessCode->code]);

        return view('auth.tenant-register', compact('accessCode'));
    }

    /**
     * Handle tenant user registration
     */
    public function register(Request $request): RedirectResponse
    {
        // Verify session has valid access code
        $accessCodeValue = session('verified_access_code');
        if (!$accessCodeValue) {
            return redirect()->route('access-code.form')
                           ->withErrors(['access_code' => 'Please verify your access code first.']);
        }

        $accessCode = AccessCode::where('code', $accessCodeValue)
                                ->where('status', 'active')
                                ->first();

        if (!$accessCode) {
            session()->forget('verified_access_code');
            return redirect()->route('access-code.form')
                           ->withErrors(['access_code' => 'Access code is no longer valid.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // Get the tenant
            $tenant = Tenant::find($accessCode->tenant_id);
            if (!$tenant) {
                throw new \Exception('Tenant not found.');
            }

            // Check if tenant has reached user limit
            $currentUserCount = User::where('tenant_id', $tenant->id)->count();
            if ($currentUserCount >= $tenant->max_users) {
                throw new \Exception('Tenant has reached maximum user limit.');
            }

            // Get the role for this access code
            $role = Role::where('name', $accessCode->role_assignment)->first();
            if (!$role) {
                throw new \Exception('Role not found.');
            }

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'tenant_id' => $tenant->id,
                'role_id' => $role->id,
                'is_active' => true,
                'email_verified_at' => now(), // Auto-verify for tenant users
            ]);

            // Update access code usage
            $accessCode->increment('used_count');
            $accessCode->update([
                'used_at' => now(),
                'used_by' => $user->id,
            ]);

            // Mark as used if reached max uses
            if ($accessCode->used_count >= $accessCode->max_uses) {
                $accessCode->update(['status' => 'used']);
            }

            // Clear session
            session()->forget('verified_access_code');

            DB::commit();

            // Log the user in
            auth()->login($user);

            // Redirect based on access type
            if ($accessCode->access_type === 'admin_setup') {
                return redirect()->route('admin.dashboard')
                               ->with('success', 'Welcome! Your admin account has been created successfully. You can now manage your pharmacy and create additional users.');
            } else {
                return redirect()->route('dashboard')
                               ->with('success', 'Welcome! Your account has been created successfully.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show admin setup completion form (for first-time admin users)
     */
    public function showAdminSetup(): View
    {
        $user = auth()->user();
        
        // Only allow admin users who haven't completed setup
        if (!$user || $user->role->name !== 'admin' || $user->setup_completed) {
            return redirect()->route('dashboard');
        }

        return view('auth.admin-setup', compact('user'));
    }

    /**
     * Complete admin setup
     */
    public function completeAdminSetup(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        if (!$user || $user->role->name !== 'admin') {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'pharmacy_name' => 'required|string|max:255',
            'pharmacy_address' => 'nullable|string|max:500',
            'pharmacy_phone' => 'nullable|string|max:20',
            'pharmacy_license' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Update tenant information with pharmacy details
            $user->tenant->update([
                'pharmacy_name' => $request->pharmacy_name,
                'address' => $request->pharmacy_address,
                'contact_phone' => $request->pharmacy_phone,
                'license_number' => $request->pharmacy_license,
                'setup_completed' => true,
            ]);

            // Mark user setup as completed
            $user->update(['setup_completed' => true]);

            DB::commit();

            return redirect()->route('admin.dashboard')
                           ->with('success', 'Pharmacy setup completed successfully! You can now start managing your pharmacy.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show user creation form for admins
     */
    public function showCreateUser(): View
    {
        $user = auth()->user();

        // Only allow admin users
        if (!$user || $user->role->name !== 'admin') {
            abort(403, 'Only administrators can create users.');
        }

        $allowedRoleNames = [Role::ADMIN, Role::PHARMACIST, Role::SALES_STAFF, Role::WORKER];

        $roles = Role::whereIn('name', $allowedRoleNames)
            ->active()
            ->get();
        $tenant = $user->tenant;

        // If user doesn't have a tenant, try to get the first active tenant
        if (!$tenant && $user->tenant_id) {
            $tenant = Tenant::findOrFail($user->tenant_id);
        } elseif (!$tenant) {
            $tenant = Tenant::where('is_active', true)->first();
        }

        if (!$tenant) {
            abort(403, 'No active tenant found. Please contact your administrator.');
        }

        // Collect available permissions from all relevant roles (DB + static defaults)
        $allPermissions = $roles
            ->flatMap(function (Role $role) {
                $permissions = $role->permissions;

                if (!is_array($permissions) || empty($permissions)) {
                    $permissions = Role::getDefaultPermissions($role->name);
                } else {
                    // Merge stored permissions with updated defaults (e.g. new access_* permissions)
                    $permissions = array_values(array_unique(array_merge(
                        $permissions,
                        Role::getDefaultPermissions($role->name)
                    )));
                }

                return $permissions;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Map role ID => effective permissions (DB + static defaults)
        $rolePermissionsMap = $roles->mapWithKeys(function (Role $role) {
            $permissions = $role->permissions;

            if (!is_array($permissions) || empty($permissions)) {
                $permissions = Role::getDefaultPermissions($role->name);
            } else {
                $permissions = array_values(array_unique(array_merge(
                    $permissions,
                    Role::getDefaultPermissions($role->name)
                )));
            }

            return [$role->id => $permissions];
        })->toArray();

        return view('admin.users.create', compact('roles', 'tenant', 'allPermissions', 'rolePermissionsMap'));
    }

    /**
     * Create new user (by admin)
     */
    public function createUser(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        // Only allow admin users
        if (!$user || $user->role->name !== 'admin') {
            abort(403, 'Only administrators can create users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        try {
            DB::beginTransaction();

            $tenant = $user->tenant;

            // Check if tenant has reached user limit
            $currentUserCount = User::where('tenant_id', $tenant->id)->count();
            if ($currentUserCount >= $tenant->max_users) {
                throw new \Exception('Your pharmacy has reached the maximum user limit. Please contact support to upgrade your plan.');
            }

            // Verify role is allowed
            $role = Role::findOrFail($request->role_id);
            $allowedRoleNames = [Role::ADMIN, Role::PHARMACIST, Role::SALES_STAFF, Role::WORKER];

            if (!in_array($role->name, $allowedRoleNames, true)) {
                throw new \Exception('Invalid role selected.');
            }

            // Resolve role's effective permissions (DB + static defaults)
            $rolePermissions = $role->permissions;
            if (!is_array($rolePermissions) || empty($rolePermissions)) {
                $rolePermissions = Role::getDefaultPermissions($role->name);
            } else {
                $rolePermissions = array_values(array_unique(array_merge(
                    $rolePermissions,
                    Role::getDefaultPermissions($role->name)
                )));
            }

            // Determine final permissions for this user
            $permissions = $request->input('permissions');
            if (is_array($permissions)) {
                // Custom per-user permissions override role defaults
                $permissions = array_values(array_unique($permissions));
            } else {
                $permissions = $rolePermissions;
            }

            // Create the user
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'tenant_id' => $tenant->id,
                'role_id' => $role->id,
                'permissions' => $permissions,
                'is_active' => true,
                'email_verified_at' => now(),
                'created_by' => $user->id,
            ]);

            DB::commit();

            return redirect()->route('admin.users')
                           ->with('success', "User {$newUser->name} created successfully with {$role->name} role.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show user edit form for admins
     */
    public function showEditUser(User $user): View
    {
        $currentUser = auth()->user();

        // Only allow admin users
        if (!$currentUser || $currentUser->role->name !== 'admin') {
            abort(403, 'Only administrators can edit users.');
        }

        // Determine if current user is a super admin (has no tenant or is explicitly marked as super admin)
        $isCurrentUserSuperAdmin = $currentUser->is_super_admin || $currentUser->tenant_id === null;

        // Determine if target user is a system-level user (has no tenant)
        $isTargetUserSystemLevel = $user->tenant_id === null;

        // Allow editing if:
        // 1. Current user is a super admin, OR
        // 2. Target user is system-level (can be edited by any admin), OR
        // 3. Both users belong to the same tenant
        if (!$isCurrentUserSuperAdmin && !$isTargetUserSystemLevel && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'You cannot edit users from other tenants.');
        }

        $allowedRoleNames = [Role::ADMIN, Role::PHARMACIST, Role::SALES_STAFF, Role::WORKER];

        $roles = Role::whereIn('name', $allowedRoleNames)
            ->active()
            ->get();
        $tenant = $isCurrentUserSuperAdmin || $isTargetUserSystemLevel ? $user->tenant : $currentUser->tenant;

        // Collect all available permissions from relevant roles (DB + static defaults)
        $allPermissions = $roles
            ->flatMap(function (Role $role) {
                $permissions = $role->permissions;

                if (!is_array($permissions) || empty($permissions)) {
                    $permissions = Role::getDefaultPermissions($role->name);
                } else {
                    $permissions = array_values(array_unique(array_merge(
                        $permissions,
                        Role::getDefaultPermissions($role->name)
                    )));
                }

                return $permissions;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $rolePermissionsMap = $roles->mapWithKeys(function (Role $role) {
            $permissions = $role->permissions;

            if (!is_array($permissions) || empty($permissions)) {
                $permissions = Role::getDefaultPermissions($role->name);
            } else {
                $permissions = array_values(array_unique(array_merge(
                    $permissions,
                    Role::getDefaultPermissions($role->name)
                )));
            }

            return [$role->id => $permissions];
        })->toArray();

        // Determine current effective permissions for this user
        $userPermissions = $user->permissions;

        if (!is_array($userPermissions) || empty($userPermissions)) {
            $userRole = $user->role;
            if ($userRole) {
                $userPermissions = $rolePermissionsMap[$userRole->id] ?? Role::getDefaultPermissions($userRole->name);
            } else {
                $userPermissions = [];
            }
        }

        return view('admin.users.edit', compact('user', 'roles', 'tenant', 'allPermissions', 'rolePermissionsMap', 'userPermissions'));
    }

    /**
     * Update user (by admin)
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        // Only allow admin users
        if (!$currentUser || $currentUser->role->name !== 'admin') {
            abort(403, 'Only administrators can update users.');
        }

        // Determine if current user is a super admin (has no tenant or is explicitly marked as super admin)
        $isCurrentUserSuperAdmin = $currentUser->is_super_admin || $currentUser->tenant_id === null;

        // Determine if target user is a system-level user (has no tenant)
        $isTargetUserSystemLevel = $user->tenant_id === null;

        // Allow editing if:
        // 1. Current user is a super admin, OR
        // 2. Target user is system-level (can be edited by any admin), OR
        // 3. Both users belong to the same tenant
        if (!$isCurrentUserSuperAdmin && !$isTargetUserSystemLevel && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'You cannot update users from other tenants.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|confirmed|min:8',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::findOrFail($request->role_id);

            // Ensure role is one of the allowed tenant roles
            $allowedRoleNames = [Role::ADMIN, Role::PHARMACIST, Role::SALES_STAFF, Role::WORKER];
            if (!in_array($role->name, $allowedRoleNames, true)) {
                throw new \Exception('Invalid role selected.');
            }

            // Resolve role's effective permissions (DB + static defaults)
            $rolePermissions = $role->permissions;
            if (!is_array($rolePermissions) || empty($rolePermissions)) {
                $rolePermissions = Role::getDefaultPermissions($role->name);
            } else {
                $rolePermissions = array_values(array_unique(array_merge(
                    $rolePermissions,
                    Role::getDefaultPermissions($role->name)
                )));
            }

            // Determine final permissions for this user
            $permissions = $request->input('permissions');

            if (is_array($permissions)) {
                // Custom per-user permissions override role defaults
                $permissions = array_values(array_unique($permissions));
            } else {
                // If no permissions were submitted, keep existing or fall back to role defaults
                $permissions = $user->permissions;

                if (!is_array($permissions) || empty($permissions)) {
                    $permissions = $rolePermissions;
                }
            }

            // Update the user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role_id' => $role->id,
                'permissions' => $permissions,
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.users')
                           ->with('success', "User {$user->name} updated successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete user (by admin)
     */
    public function deleteUser(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        // Only allow admin users
        if (!$currentUser || $currentUser->role->name !== 'admin') {
            abort(403, 'Only administrators can delete users.');
        }

        // Determine if current user is a super admin (has no tenant or is explicitly marked as super admin)
        $isCurrentUserSuperAdmin = $currentUser->is_super_admin || $currentUser->tenant_id === null;

        // Determine if target user is a system-level user (has no tenant)
        $isTargetUserSystemLevel = $user->tenant_id === null;

        // Allow editing if:
        // 1. Current user is a super admin, OR
        // 2. Target user is system-level (can be edited by any admin), OR
        // 3. Both users belong to the same tenant
        if (!$isCurrentUserSuperAdmin && !$isTargetUserSystemLevel && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'You cannot delete users from other tenants.');
        }

        // Prevent deleting the current user
        if ($user->id === $currentUser->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        try {
            DB::beginTransaction();

            $userName = $user->name;
            $user->delete();

            DB::commit();

            return redirect()->route('admin.users')
                           ->with('success', "User {$userName} deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

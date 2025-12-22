<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Traits\HasRoleBasedRouting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
class TenantRoleController extends Controller
{
    use HasRoleBasedRouting;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('tenant.admin'); // Custom middleware for tenant admin access
    }

    /**
     * Display roles for current tenant
     */
    public function index(): View
    {
        $roles = Role::forCurrentTenant()
                    ->withCount('users')
                    ->orderBy('name')
                    ->paginate(20);

        return view('tenant.roles.index', compact('roles'));
    }

    /**
     * Show role creation form
     */
    public function create(): View
    {
        $permissions = Permission::all()->groupBy('category');
        
        return view('tenant.roles.create', compact('permissions'));
    }

    /**
     * Store new role
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'tenant_id' => app('current_tenant')->id,
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return $this->redirectToIndex('tenant.roles', 'Role created successfully.');
    }

    /**
     * Show role details
     */
    public function show(Role $role): View
    {
        $this->authorize('view', $role);
        
        $role->load(['permissions', 'users']);
        
        return view('tenant.roles.show', compact('role'));
    }

    /**
     * Show role edit form
     */
    public function edit(Role $role): View
    {
        $this->authorize('update', $role);
        
        $permissions = Permission::all()->groupBy('category');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('tenant.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return $this->redirectToIndex('tenant.roles', 'Role updated successfully.');
    }

    /**
     * Delete role
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);
        
        // Check if role is in use
        if ($role->users()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'Cannot delete role that is assigned to users.');
        }

        // Prevent deletion of system roles
        if (in_array($role->name, ['admin', 'pharmacist', 'sales_staff'])) {
            return redirect()->back()
                           ->with('error', 'Cannot delete system roles.');
        }

        $role->delete();

        return $this->redirectToIndex('tenant.roles', 'Role deleted successfully.');
    }

    /**
     * Assign role to user
     */
    public function assignToUser(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = auth()->user()->tenant->users()->findOrFail($validated['user_id']);
        $user->update(['role_id' => $role->id]);

        return redirect()->back()
                        ->with('success', 'Role assigned to user successfully.');
    }

    /**
     * Remove role from user
     */
    public function removeFromUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = auth()->user()->tenant->users()->findOrFail($validated['user_id']);
        
        // Don't remove role from tenant admin
        if ($user->id === auth()->id()) {
            return redirect()->back()
                           ->with('error', 'Cannot remove role from yourself.');
        }

        $user->update(['role_id' => null]);

        return redirect()->back()
                        ->with('success', 'Role removed from user successfully.');
    }

    /**
     * Clone role
     */
    public function clone(Role $role): RedirectResponse
    {
        $this->authorize('view', $role);
        
        $newRole = Role::create([
            'name' => $role->name . ' (Copy)',
            'description' => $role->description,
            'tenant_id' => app('current_tenant')->id,
        ]);

        $newRole->permissions()->sync($role->permissions->pluck('id'));

        return redirect()->route('tenant.roles.edit', $newRole)
                        ->with('success', 'Role cloned successfully. You can now modify it.');
    }

    /**
     * Get available permissions for AJAX
     */
    public function getPermissions(): \Illuminate\Http\JsonResponse
    {
        $permissions = Permission::all()->groupBy('category');
        
        return response()->json($permissions);
    }

    /**
     * Bulk assign permissions to role
     */
    public function bulkAssignPermissions(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);
        
        $validated = $request->validate([
            'action' => 'required|in:assign_all,remove_all,assign_category',
            'category' => 'nullable|string',
        ]);

        switch ($validated['action']) {
            case 'assign_all':
                $permissions = Permission::all()->pluck('id');
                $role->permissions()->sync($permissions);
                $message = 'All permissions assigned to role.';
                break;
                
            case 'remove_all':
                $role->permissions()->detach();
                $message = 'All permissions removed from role.';
                break;
                
            case 'assign_category':
                if ($validated['category']) {
                    $permissions = Permission::where('category', $validated['category'])->pluck('id');
                    $currentPermissions = $role->permissions->pluck('id')->toArray();
                    $allPermissions = array_unique(array_merge($currentPermissions, $permissions->toArray()));
                    $role->permissions()->sync($allPermissions);
                    $message = 'Category permissions assigned to role.';
                } else {
                    return redirect()->back()->with('error', 'Category is required.');
                }
                break;
                
            default:
                return redirect()->back()->with('error', 'Invalid action.');
        }

        return redirect()->back()->with('success', $message);
    }
}

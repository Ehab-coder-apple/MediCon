<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\BatchPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Product::class => ProductPolicy::class,
        Batch::class => BatchPolicy::class,
        Attendance::class => AttendancePolicy::class,
        \App\Models\Category::class => \App\Policies\CategoryPolicy::class,
        \App\Models\Subcategory::class => \App\Policies\SubcategoryPolicy::class,
        \App\Models\Location::class => \App\Policies\LocationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Super admins can bypass all gate and policy checks
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Define additional gates if needed
        Gate::define('access-admin-dashboard', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('access-pharmacist-dashboard', function (User $user) {
            return $user->hasRole('pharmacist');
        });

        Gate::define('access-sales-dashboard', function (User $user) {
            return $user->hasRole('sales_staff');
        });

        Gate::define('manage-inventory', function (User $user) {
            return $user->hasPermission('manage_inventory');
        });

        Gate::define('view-reports', function (User $user) {
            return $user->hasPermission('view_reports');
        });

        Gate::define('manage-system', function (User $user) {
            return $user->hasPermission('manage_system');
        });
    }
}

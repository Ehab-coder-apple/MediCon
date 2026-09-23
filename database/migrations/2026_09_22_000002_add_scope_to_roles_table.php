<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a 'scope' column distinguishing corporate/HQ-wide roles from
     * branch-locked roles, and seeds the two new HQ roles required by the
     * corporate chain pharmacy architecture. Legacy role names ('admin',
     * 'pharmacist', 'sales_staff', 'worker') are intentionally kept exactly
     * as-is to avoid breaking existing hardcoded role checks; 'scope' is the
     * new mechanism used to alter behaviour instead:
     *  - 'sales_staff' acts as Branch Manager (branch-scoped)
     *  - 'worker' acts as Branch Worker (branch-scoped)
     *  - 'admin' remains global (tenant-wide, HQ-level)
     *  - 'hq_inventory_manager' / 'hq_hr_manager' are new, global HQ roles
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('scope')->default(Role::SCOPE_BRANCH)->after('name');
        });

        // Explicitly set scope for legacy roles (relying on the column
        // default only covers 'branch'; 'admin' must be marked 'global').
        DB::table('roles')->where('name', Role::ADMIN)->update(['scope' => Role::SCOPE_GLOBAL]);
        DB::table('roles')->whereIn('name', [Role::PHARMACIST, Role::SALES_STAFF, Role::WORKER])
            ->update(['scope' => Role::SCOPE_BRANCH]);

        // Seed the two new global HQ roles using the same idempotent
        // updateOrInsert pattern as the existing 2025_12_28_000002 migration.
        DB::table('roles')->updateOrInsert(
            ['name' => Role::HQ_INVENTORY_MANAGER],
            [
                'display_name' => 'HQ Inventory Manager',
                'description' => 'Corporate HQ role with full control over the central warehouse and global read access to every branch\'s backroom and dispensing shelf stock, used to monitor thresholds and initiate stock transfer orders.',
                'scope' => Role::SCOPE_GLOBAL,
                'permissions' => json_encode(Role::getDefaultPermissions(Role::HQ_INVENTORY_MANAGER)),
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => Role::HQ_HR_MANAGER],
            [
                'display_name' => 'HQ HR Manager',
                'description' => 'Corporate HQ role handling global employee records and payroll properties, with unique permission to create temporary branch allocation overrides.',
                'scope' => Role::SCOPE_GLOBAL,
                'permissions' => json_encode(Role::getDefaultPermissions(Role::HQ_HR_MANAGER)),
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally do not delete the HQ roles to avoid data loss for
        // any user already assigned to them (consistent with the existing
        // role-seeding migrations in this codebase).
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};

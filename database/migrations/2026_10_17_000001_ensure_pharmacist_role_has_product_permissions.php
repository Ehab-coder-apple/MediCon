<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The 'roles' table is shared (not per-tenant) and its 'permissions'
     * JSON column is only written once at creation time - it is never kept
     * in sync with App\Models\Role::getDefaultPermissions() afterwards. On
     * any tenant whose 'pharmacist' role row predates 'manage_products'/
     * 'view_inventory' being added to that default list, ProductPolicy
     * blocks pharmacists from /pharmacist/products with a 403, even though
     * current code assumes every pharmacist has them. Backfill the missing
     * permissions onto the existing row instead of overwriting it, so any
     * other custom permissions already granted are preserved.
     */
    public function up(): void
    {
        $pharmacist = DB::table('roles')->where('name', 'pharmacist')->first();

        if (! $pharmacist) {
            return;
        }

        $permissions = json_decode($pharmacist->permissions ?? '[]', true) ?: [];

        $permissions = array_values(array_unique(array_merge($permissions, [
            'manage_products',
            'view_inventory',
        ])));

        DB::table('roles')->where('id', $pharmacist->id)->update([
            'permissions' => json_encode($permissions),
            'updated_at' => now(),
        ]);
    }

    /**
     * No safe rollback - we don't know whether these permissions were
     * already present before this migration ran.
     */
    public function down(): void
    {
        //
    }
};

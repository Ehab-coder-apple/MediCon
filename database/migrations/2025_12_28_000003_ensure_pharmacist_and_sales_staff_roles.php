<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure Pharmacist role exists and is active
        $pharmacist = DB::table('roles')->where('name', Role::PHARMACIST)->first();
        if ($pharmacist) {
            DB::table('roles')->where('id', $pharmacist->id)->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('roles')->insert([
                'name' => Role::PHARMACIST,
                'display_name' => 'Pharmacist',
                'description' => 'Manages inventory, prescriptions, and pharmacy operations',
                'permissions' => json_encode(Role::getDefaultPermissions(Role::PHARMACIST)),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure Sales Staff role exists and is active
        $salesStaff = DB::table('roles')->where('name', Role::SALES_STAFF)->first();
        if ($salesStaff) {
            DB::table('roles')->where('id', $salesStaff->id)->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('roles')->insert([
                'name' => Role::SALES_STAFF,
                'display_name' => 'Sales Staff',
                'description' => 'Handles sales transactions and customer service',
                'permissions' => json_encode(Role::getDefaultPermissions(Role::SALES_STAFF)),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We do not delete roles on rollback to avoid data loss.
    }
};


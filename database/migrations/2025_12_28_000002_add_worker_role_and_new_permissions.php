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
        // Ensure the Worker role exists with its default permissions
        $workerPermissions = Role::getDefaultPermissions(Role::WORKER);

        DB::table('roles')->updateOrInsert(
            ['name' => Role::WORKER],
            [
                'display_name' => 'Worker',
                'description' => 'Operational worker with limited access focused on daily tasks such as viewing inventory and working within their assigned branch.',
                'permissions' => json_encode($workerPermissions),
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Ensure Admin role permissions include any newly-defined default permissions
        $admin = DB::table('roles')->where('name', Role::ADMIN)->first();
        if ($admin) {
            $existing = $admin->permissions ? json_decode($admin->permissions, true) : [];
            if (!is_array($existing)) {
                $existing = [];
            }

            $merged = array_values(array_unique(array_merge(
                $existing,
                Role::getDefaultPermissions(Role::ADMIN)
            )));

            DB::table('roles')->where('id', $admin->id)->update([
                'permissions' => json_encode($merged),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank: we do not remove roles to avoid data loss.
    }
};


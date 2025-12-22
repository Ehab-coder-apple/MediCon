<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ADMIN,
                'display_name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => Role::getDefaultPermissions(Role::ADMIN),
                'is_active' => true,
            ],
            [
                'name' => Role::PHARMACIST,
                'display_name' => 'Pharmacist',
                'description' => 'Manages inventory, prescriptions, and pharmacy operations',
                'permissions' => Role::getDefaultPermissions(Role::PHARMACIST),
                'is_active' => true,
            ],
            [
                'name' => Role::SALES_STAFF,
                'display_name' => 'Sales Staff',
                'description' => 'Handles sales transactions and customer service',
                'permissions' => Role::getDefaultPermissions(Role::SALES_STAFF),
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}

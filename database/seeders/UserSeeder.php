<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $pharmacistRole = Role::where('name', Role::PHARMACIST)->first();
        $salesRole = Role::where('name', Role::SALES_STAFF)->first();

        // Create or update Admin user
        User::updateOrCreate(
            ['email' => 'admin@medicon.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'branch_id' => 'MAIN',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Create or update Pharmacist user
        User::updateOrCreate(
            ['email' => 'pharmacist@medicon.com'],
            [
                'name' => 'John Pharmacist',
                'password' => Hash::make('password'),
                'role_id' => $pharmacistRole->id,
                'branch_id' => 'MAIN',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Create or update Sales Staff user
        User::updateOrCreate(
            ['email' => 'sales@medicon.com'],
            [
                'name' => 'Jane Sales',
                'password' => Hash::make('password'),
                'role_id' => $salesRole->id,
                'branch_id' => 'MAIN',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
    }
}

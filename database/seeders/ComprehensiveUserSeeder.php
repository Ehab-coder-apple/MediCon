<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ComprehensiveUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist
        $this->ensureRolesExist();

        // Create admin users
        $this->createAdminUsers();

        // Create pharmacist users
        $this->createPharmacistUsers();

        // Create sales staff users
        $this->createSalesStaffUsers();

        // Assign users to multiple branches (many-to-many)
        $this->assignUsersToMultipleBranches();
    }

    private function ensureRolesExist(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator'],
            ['name' => 'pharmacist', 'display_name' => 'Pharmacist'],
            ['name' => 'sales_staff', 'display_name' => 'Sales Staff'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                ['display_name' => $roleData['display_name']]
            );
        }
    }

    private function createAdminUsers(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        $adminUsers = [
            [
                'name' => 'Dr. Sarah Johnson',
                'email' => 'admin@medicon.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id' => $adminRole->id,
                'branch_id' => 1,
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.admin@medicon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $adminRole->id,
                'branch_id' => 1,
            ],
            [
                'name' => 'Dr. Emily Rodriguez',
                'email' => 'emily.admin@medicon.com',
                'password' => Hash::make('admin2024'),
                'email_verified_at' => now(),
                'role_id' => $adminRole->id,
                'branch_id' => 2,
            ],
        ];

        foreach ($adminUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }

    private function createPharmacistUsers(): void
    {
        $pharmacistRole = Role::where('name', 'pharmacist')->first();

        $pharmacistUsers = [
            [
                'name' => 'Dr. John Pharmacist',
                'email' => 'pharmacist@medicon.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id' => $pharmacistRole->id,
                'branch_id' => 1,
            ],
            [
                'name' => 'Dr. Lisa Thompson',
                'email' => 'lisa.pharmacist@medicon.com',
                'password' => Hash::make('pharma123'),
                'email_verified_at' => now(),
                'role_id' => $pharmacistRole->id,
                'branch_id' => 1,
            ],
            [
                'name' => 'Dr. Ahmed Hassan',
                'email' => 'ahmed.pharmacist@medicon.com',
                'password' => Hash::make('pharmacy2024'),
                'email_verified_at' => now(),
                'role_id' => $pharmacistRole->id,
                'branch_id' => 2,
            ],
            [
                'name' => 'Dr. Maria Garcia',
                'email' => 'maria.pharmacist@medicon.com',
                'password' => Hash::make('medicon123'),
                'email_verified_at' => now(),
                'role_id' => $pharmacistRole->id,
                'branch_id' => 1,
            ],
            [
                'name' => 'Dr. David Kim',
                'email' => 'david.pharmacist@medicon.com',
                'password' => Hash::make('pharmacy456'),
                'email_verified_at' => now(),
                'role_id' => $pharmacistRole->id,
                'branch_id' => 3,
            ],
        ];

        foreach ($pharmacistUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }

    private function createSalesStaffUsers(): void
    {
        $salesRole = Role::where('name', 'sales_staff')->first();

        $salesUsers = [
            [
                'name' => 'Jane Sales Staff',
                'email' => 'sales@medicon.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id' => $salesRole->id,
                'branch_id' => 1,
            ],
            [
                'name' => 'Robert Wilson',
                'email' => 'robert.sales@medicon.com',
                'password' => Hash::make('sales123'),
                'email_verified_at' => now(),
                'role_id' => $salesRole->id,
                'branch_id' => 1,
            ],
            [
                'name' => 'Jennifer Brown',
                'email' => 'jennifer.sales@medicon.com',
                'password' => Hash::make('sales2024'),
                'email_verified_at' => now(),
                'role_id' => $salesRole->id,
                'branch_id' => 2,
            ],
            [
                'name' => 'Carlos Martinez',
                'email' => 'carlos.sales@medicon.com',
                'password' => Hash::make('medicon456'),
                'email_verified_at' => now(),
                'role_id' => $salesRole->id,
                'branch_id' => 1,
            ],
            [
                'name' => 'Anna Petrov',
                'email' => 'anna.sales@medicon.com',
                'password' => Hash::make('sales789'),
                'email_verified_at' => now(),
                'role_id' => $salesRole->id,
                'branch_id' => 2,
            ],
            [
                'name' => 'James Taylor',
                'email' => 'james.sales@medicon.com',
                'password' => Hash::make('taylor123'),
                'email_verified_at' => now(),
                'role_id' => $salesRole->id,
                'branch_id' => 3,
            ],
            [
                'name' => 'Sophie Anderson',
                'email' => 'sophie.sales@medicon.com',
                'password' => Hash::make('sophie456'),
                'email_verified_at' => now(),
                'role_id' => $salesRole->id,
                'branch_id' => 1,
            ],
        ];

        foreach ($salesUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }

    private function assignUsersToMultipleBranches(): void
    {
        // Assign pharmacist@medicon.com to branches 1 and 2
        $pharmacist1 = User::where('email', 'pharmacist@medicon.com')->first();
        if ($pharmacist1) {
            $pharmacist1->branches()->sync([1, 2], false);
        }

        // Assign maria.pharmacist@medicon.com to branches 1 and 3
        $pharmacist2 = User::where('email', 'maria.pharmacist@medicon.com')->first();
        if ($pharmacist2) {
            $pharmacist2->branches()->sync([1, 3], false);
        }

        // Assign sales@medicon.com to branches 1 and 2
        $sales1 = User::where('email', 'sales@medicon.com')->first();
        if ($sales1) {
            $sales1->branches()->sync([1, 2], false);
        }

        // Assign carlos.sales@medicon.com to branches 1, 2, and 3
        $sales2 = User::where('email', 'carlos.sales@medicon.com')->first();
        if ($sales2) {
            $sales2->branches()->sync([1, 2, 3], false);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        // Create demo pharmacies
        $pharmacies = [
            [
                'name' => 'Demo Pharmacy',
                'slug' => 'demo-pharmacy',
                'pharmacy_name' => 'Demo Pharmacy Inc.',
                'contact_email' => 'admin@demo-pharmacy.com',
                'contact_phone' => '+1-555-0123',
                'address' => '123 Main Street',
                'city' => 'Demo City',
                'state' => 'Demo State',
                'country' => 'US',
                'postal_code' => '12345',
                'subscription_plan' => 'premium',
                'subscription_status' => 'active',
                'max_users' => 50,
                'is_active' => true,
                'admin_email' => 'admin@demo-pharmacy.com',
                'admin_name' => 'Demo Admin',
            ],
            [
                'name' => 'City Health Pharmacy',
                'slug' => 'city-health-pharmacy',
                'pharmacy_name' => 'City Health Pharmacy',
                'contact_email' => 'admin@cityhealthpharmacy.com',
                'contact_phone' => '+1-555-0124',
                'address' => '456 Oak Avenue',
                'city' => 'Springfield',
                'state' => 'IL',
                'country' => 'US',
                'postal_code' => '62701',
                'subscription_plan' => 'standard',
                'subscription_status' => 'active',
                'max_users' => 30,
                'is_active' => true,
                'admin_email' => 'admin@cityhealthpharmacy.com',
                'admin_name' => 'Sarah Mitchell',
            ],
            [
                'name' => 'Wellness Plus Pharmacy',
                'slug' => 'wellness-plus-pharmacy',
                'pharmacy_name' => 'Wellness Plus Pharmacy',
                'contact_email' => 'admin@wellnesspluspharmacy.com',
                'contact_phone' => '+1-555-0125',
                'address' => '789 Wellness Drive',
                'city' => 'Austin',
                'state' => 'TX',
                'country' => 'US',
                'postal_code' => '78701',
                'subscription_plan' => 'premium',
                'subscription_status' => 'active',
                'max_users' => 75,
                'is_active' => true,
                'admin_email' => 'admin@wellnesspluspharmacy.com',
                'admin_name' => 'Dr. James Wilson',
            ],
        ];

        foreach ($pharmacies as $pharmacyData) {
            $adminEmail = $pharmacyData['admin_email'];
            $adminName = $pharmacyData['admin_name'];
            unset($pharmacyData['admin_email'], $pharmacyData['admin_name']);

            // Create or update tenant
            $tenant = Tenant::firstOrCreate(
                ['slug' => $pharmacyData['slug']],
                $pharmacyData
            );

            // Create tenant admin user if not exists
            User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make('password'),
                    'tenant_id' => $tenant->id,
                    'role_id' => $adminRole->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $this->command->info("✅ Created tenant: {$tenant->pharmacy_name} ({$adminEmail})");
        }
    }
}


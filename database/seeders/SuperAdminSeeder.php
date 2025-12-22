<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin User
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@medicon.com',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super Admin created: superadmin@medicon.com / password');

        // Create a demo tenant
        $demoTenant = Tenant::create([
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
        ]);

        // Create tenant admin for demo pharmacy
        $tenantAdmin = User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo-pharmacy.com',
            'password' => Hash::make('password'),
            'tenant_id' => $demoTenant->id,
            'is_super_admin' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Demo Tenant created: ' . $demoTenant->name);
        $this->command->info('Demo Tenant Admin created: admin@demo-pharmacy.com / password');
    }
}

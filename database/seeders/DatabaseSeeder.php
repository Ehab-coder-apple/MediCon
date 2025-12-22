<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting MediCon Database Seeding...');

        // Step 1: Create tenants (foundation for multi-tenancy)
        $this->command->info('🏢 Creating demo pharmacies (tenants)...');
        $this->call(TenantSeeder::class);

        // Step 2: Create branches (needed for user branch assignments)
        $this->command->info('🏬 Creating branch locations...');
        $this->call(BranchSeeder::class);

        // Step 3: Create roles and users (foundation)
        $this->command->info('👥 Creating roles and users...');
        $this->call([
            RoleSeeder::class,
            ComprehensiveUserSeeder::class,
        ]);

        // Step 4: Create categories and locations (needed for products)
        $this->command->info('📂 Creating product categories and locations...');
        $this->call([
            CategorySeeder::class,
            LocationSeeder::class,
        ]);

        // Step 5: Create suppliers (needed for purchases)
        $this->command->info('🏢 Creating pharmaceutical suppliers...');
        $this->call(PharmaceuticalSupplierSeeder::class);

        // Step 6: Create products and batches
        $this->command->info('💊 Creating pharmaceutical products and batches...');
        $this->call(PharmaceuticalProductSeeder::class);

        // Step 7: Create purchase orders (creates additional batches)
        $this->command->info('📦 Creating purchase orders...');
        $this->call(PharmaceuticalPurchaseSeeder::class);

        // Step 8: Create sales transactions (requires products and customers)
        $this->command->info('💰 Creating sales transactions...');
        $this->call(PharmaceuticalSalesSeeder::class);

        // Step 9: Create leave types (for leave management)
        $this->command->info('🏖️ Creating leave types...');
        $this->call(LeaveTypeSeeder::class);

        // Step 10: Create attendance records (optional; only if seeder exists)
        if (class_exists(\Database\Seeders\AttendanceSeeder::class)) {
            $this->command->info('📊 Creating attendance records...');
            $this->call(AttendanceSeeder::class);
        }

        // Step 11: Create inventory metrics test data
        $this->command->info('🧪 Creating inventory metrics test data...');
        $this->call(InventoryMetricsTestSeeder::class);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->displaySeedingSummary();
    }

    private function displaySeedingSummary(): void
    {
        $this->command->info('');
        $this->command->info('📈 SEEDING SUMMARY:');
        $this->command->info('==================');

        // Get counts from database
        $userCount = \App\Models\User::count();
        $roleCount = \App\Models\Role::count();
        $supplierCount = \App\Models\Supplier::count();
        $productCount = \App\Models\Product::count();
        $batchCount = \App\Models\Batch::count();
        $purchaseCount = \App\Models\Purchase::count();
        $purchaseItemCount = \App\Models\PurchaseItem::count();
        $customerCount = \App\Models\Customer::count();
        $saleCount = \App\Models\Sale::count();
        $saleItemCount = \App\Models\SaleItem::count();
        $attendanceCount = \App\Models\Attendance::count();


        $this->command->info("👥 Users: {$userCount} (across {$roleCount} roles)");
        $this->command->info("🏢 Suppliers: {$supplierCount}");
        $this->command->info("💊 Products: {$productCount} with {$batchCount} batches");
        $this->command->info("📦 Purchases: {$purchaseCount} orders with {$purchaseItemCount} items");
        $this->command->info("👤 Customers: {$customerCount}");
        $this->command->info("💰 Sales: {$saleCount} transactions with {$saleItemCount} items");
        $this->command->info("📊 Attendance: {$attendanceCount} records");


        $this->command->info('');
        $this->command->info('🎯 TEST ACCOUNTS:');
        $this->command->info('================');
        $this->command->info('Admin: admin@medicon.com / password');
        $this->command->info('Pharmacist: pharmacist@medicon.com / password');
        $this->command->info('Sales Staff: sales@medicon.com / password');

        $this->command->info('');
        $this->command->info('🌐 ACCESS YOUR APPLICATION:');
        $this->command->info('===========================');
        $this->command->info('URL: http://127.0.0.1:8000');
        $this->command->info('Start server: php artisan serve');

        $this->command->info('');
        $this->command->info('🎉 MediCon is ready for development and testing!');
    }
}

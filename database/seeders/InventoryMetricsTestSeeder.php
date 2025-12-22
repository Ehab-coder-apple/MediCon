<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Batch;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InventoryMetricsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧪 Creating inventory metrics test data...');

        // 1. Create EXPIRED PRODUCTS (batches with expiry_date <= today)
        $this->createExpiredProducts();

        // 2. Create NEARLY EXPIRED PRODUCTS (batches expiring within 30 days)
        $this->createNearlyExpiredProducts();

        // 3. Create LOW STOCK PRODUCTS (quantity <= alert_quantity)
        $this->createLowStockProducts();

        // 4. Create OUT OF STOCK PRODUCTS (quantity = 0)
        $this->createOutOfStockProducts();

        $this->command->info('✅ Inventory metrics test data created successfully!');
    }

    /**
     * Create products with expired batches
     */
    private function createExpiredProducts(): void
    {
        $this->command->line('📛 Creating expired products...');

        $expiredProducts = [
            [
                'name' => 'Expired Antibiotic Syrup',
                'code' => 'EXP-ANT-001',
                'category' => 'Antibiotics',
                'cost_price' => 5.50,
                'selling_price' => 10.99,
                'alert_quantity' => 20,
            ],
            [
                'name' => 'Expired Cough Syrup',
                'code' => 'EXP-COUGH-001',
                'category' => 'Cold & Flu',
                'cost_price' => 3.25,
                'selling_price' => 6.99,
                'alert_quantity' => 15,
            ],
            [
                'name' => 'Expired Vitamin C Tablets',
                'code' => 'EXP-VIT-001',
                'category' => 'Vitamins',
                'cost_price' => 2.00,
                'selling_price' => 4.99,
                'alert_quantity' => 30,
            ],
        ];

        foreach ($expiredProducts as $productData) {
            $product = Product::updateOrCreate(
                ['code' => $productData['code']],
                $productData
            );

            // Create expired batches (expiry_date in the past)
            Batch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'EXPBATCH-' . $product->code . '-001'],
                [
                    'expiry_date' => now()->subDays(60), // Expired 60 days ago
                    'quantity' => 45,
                    'cost_price' => $product->cost_price,
                    'manufacturer' => 'Test Manufacturer',
                ]
            );

            Batch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'EXPBATCH-' . $product->code . '-002'],
                [
                    'expiry_date' => now()->subDays(30), // Expired 30 days ago
                    'quantity' => 30,
                    'cost_price' => $product->cost_price,
                    'manufacturer' => 'Test Manufacturer',
                ]
            );

            $this->command->line("  ✓ Created: {$product->name}");
        }
    }

    /**
     * Create products with batches expiring soon (within 30 days)
     */
    private function createNearlyExpiredProducts(): void
    {
        $this->command->line('⏰ Creating nearly expired products...');

        $nearlyExpiredProducts = [
            [
                'name' => 'Nearly Expired Pain Relief',
                'code' => 'NEAR-PAIN-001',
                'category' => 'Pain Relief',
                'cost_price' => 2.50,
                'selling_price' => 4.99,
                'alert_quantity' => 25,
            ],
            [
                'name' => 'Nearly Expired Digestive Aid',
                'code' => 'NEAR-DIG-001',
                'category' => 'Digestive',
                'cost_price' => 4.75,
                'selling_price' => 9.99,
                'alert_quantity' => 20,
            ],
            [
                'name' => 'Nearly Expired Allergy Relief',
                'code' => 'NEAR-ALLERGY-001',
                'category' => 'Allergy',
                'cost_price' => 3.50,
                'selling_price' => 7.49,
                'alert_quantity' => 18,
            ],
        ];

        foreach ($nearlyExpiredProducts as $productData) {
            $product = Product::updateOrCreate(
                ['code' => $productData['code']],
                $productData
            );

            // Create batches expiring within 30 days
            Batch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'NEARBATCH-' . $product->code . '-001'],
                [
                    'expiry_date' => now()->addDays(15), // Expires in 15 days
                    'quantity' => 50,
                    'cost_price' => $product->cost_price,
                    'manufacturer' => 'Test Manufacturer',
                ]
            );

            Batch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'NEARBATCH-' . $product->code . '-002'],
                [
                    'expiry_date' => now()->addDays(25), // Expires in 25 days
                    'quantity' => 35,
                    'cost_price' => $product->cost_price,
                    'manufacturer' => 'Test Manufacturer',
                ]
            );

            $this->command->line("  ✓ Created: {$product->name}");
        }
    }

    /**
     * Create low stock products (quantity <= alert_quantity)
     */
    private function createLowStockProducts(): void
    {
        $this->command->line('📉 Creating low stock products...');

        $lowStockProducts = [
            [
                'name' => 'Low Stock Insulin Injection',
                'code' => 'LOW-INS-001',
                'category' => 'Diabetes',
                'cost_price' => 25.00,
                'selling_price' => 49.99,
                'alert_quantity' => 50,
            ],
            [
                'name' => 'Low Stock Blood Pressure Monitor',
                'code' => 'LOW-BP-001',
                'category' => 'Medical Devices',
                'cost_price' => 35.00,
                'selling_price' => 69.99,
                'alert_quantity' => 30,
            ],
            [
                'name' => 'Low Stock Antibiotic Cream',
                'code' => 'LOW-CREAM-001',
                'category' => 'Topical',
                'cost_price' => 4.50,
                'selling_price' => 8.99,
                'alert_quantity' => 40,
            ],
        ];

        foreach ($lowStockProducts as $productData) {
            $product = Product::updateOrCreate(
                ['code' => $productData['code']],
                $productData
            );

            // Create batches with quantity below alert_quantity
            Batch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'LOWBATCH-' . $product->code . '-001'],
                [
                    'expiry_date' => now()->addMonths(6),
                    'quantity' => 15, // Below alert_quantity of 50/30/40
                    'cost_price' => $product->cost_price,
                    'manufacturer' => 'Test Manufacturer',
                ]
            );

            Batch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'LOWBATCH-' . $product->code . '-002'],
                [
                    'expiry_date' => now()->addMonths(8),
                    'quantity' => 10,
                    'cost_price' => $product->cost_price,
                    'manufacturer' => 'Test Manufacturer',
                ]
            );

            $this->command->line("  ✓ Created: {$product->name}");
        }
    }

    /**
     * Create out of stock products (quantity = 0)
     */
    private function createOutOfStockProducts(): void
    {
        $this->command->line('🚫 Creating out of stock products...');

        $outOfStockProducts = [
            [
                'name' => 'Out of Stock Cardiac Medicine',
                'code' => 'OOS-CARD-001',
                'category' => 'Cardiovascular',
                'cost_price' => 12.50,
                'selling_price' => 24.99,
                'alert_quantity' => 25,
            ],
            [
                'name' => 'Out of Stock Migraine Relief',
                'code' => 'OOS-MIGR-001',
                'category' => 'Pain Relief',
                'cost_price' => 5.75,
                'selling_price' => 11.99,
                'alert_quantity' => 20,
            ],
            [
                'name' => 'Out of Stock Sleep Aid',
                'code' => 'OOS-SLEEP-001',
                'category' => 'Sleep',
                'cost_price' => 8.00,
                'selling_price' => 15.99,
                'alert_quantity' => 15,
            ],
        ];

        foreach ($outOfStockProducts as $productData) {
            $product = Product::updateOrCreate(
                ['code' => $productData['code']],
                $productData
            );

            // Create batches with zero quantity
            Batch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'OOSBATCH-' . $product->code . '-001'],
                [
                    'expiry_date' => now()->addMonths(12),
                    'quantity' => 0, // Out of stock
                    'cost_price' => $product->cost_price,
                    'manufacturer' => 'Test Manufacturer',
                ]
            );

            $this->command->line("  ✓ Created: {$product->name}");
        }
    }
}


<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing tenants with subscription amounts
        $tenants = \App\Models\Tenant::all();

        foreach ($tenants as $tenant) {
            $subscriptionAmounts = [
                'basic' => 29.99,
                'standard' => 59.99,
                'premium' => 99.99,
                'enterprise' => 199.99,
            ];

            $plans = array_keys($subscriptionAmounts);
            $randomPlan = $plans[array_rand($plans)];

            $tenant->update([
                'subscription_plan' => $randomPlan,
                'subscription_amount' => $subscriptionAmounts[$randomPlan],
                'subscription_status' => 'active',
            ]);
        }

        $this->command->info('Updated ' . $tenants->count() . ' tenants with subscription data');

        // Create sample batches for existing products to populate inventory data
        $products = \App\Models\Product::all();

        foreach ($products as $product) {
            // Create 1-3 batches per product
            $batchCount = rand(1, 3);

            for ($i = 0; $i < $batchCount; $i++) {
                \App\Models\Batch::create([
                    'product_id' => $product->id,
                    'batch_number' => 'B' . $product->code . '-' . date('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'expiry_date' => now()->addMonths(rand(6, 24)),
                    'quantity' => rand(10, 500),
                    'cost_price' => $product->cost_price * (1 + (rand(-10, 10) / 100)), // ±10% variation
                    'manufacturer' => 'Sample Manufacturer',
                    'tenant_id' => $product->tenant_id,
                ]);
            }
        }

        $this->command->info('Created sample batches for ' . $products->count() . ' products');
    }
}

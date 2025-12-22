<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductBatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample products
        $products = [
            [
                'name' => 'Paracetamol 500mg',
                'category' => 'Pain Relief',
                'code' => 'PARA500',
                'cost_price' => 0.50,
                'selling_price' => 1.25,
                'alert_quantity' => 50,
                'description' => 'Pain relief and fever reducer',
                'is_active' => true,
            ],
            [
                'name' => 'Aspirin 100mg',
                'category' => 'Pain Relief',
                'code' => 'ASP100',
                'cost_price' => 0.30,
                'selling_price' => 0.75,
                'alert_quantity' => 30,
                'description' => 'Low-dose aspirin for heart health',
                'is_active' => true,
            ],
            [
                'name' => 'Amoxicillin 250mg',
                'category' => 'Antibiotic',
                'code' => 'AMOX250',
                'cost_price' => 2.00,
                'selling_price' => 5.50,
                'alert_quantity' => 20,
                'description' => 'Broad-spectrum antibiotic',
                'is_active' => true,
            ],
            [
                'name' => 'Vitamin C 1000mg',
                'category' => 'Vitamins',
                'code' => 'VITC1000',
                'cost_price' => 0.80,
                'selling_price' => 2.00,
                'alert_quantity' => 25,
                'description' => 'Immune system support',
                'is_active' => true,
            ],
            [
                'name' => 'Cough Syrup',
                'category' => 'Respiratory',
                'code' => 'COUGH001',
                'cost_price' => 3.50,
                'selling_price' => 8.75,
                'alert_quantity' => 15,
                'description' => 'Cough suppressant and expectorant',
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Create sample batches for each product
            $this->createBatchesForProduct($product);
        }
    }

    private function createBatchesForProduct(Product $product): void
    {
        $batches = [
            [
                'batch_number' => 'B001-' . $product->code,
                'expiry_date' => now()->addMonths(6),
                'quantity' => rand(10, 100),
                'cost_price' => $product->cost_price,
            ],
            [
                'batch_number' => 'B002-' . $product->code,
                'expiry_date' => now()->addMonths(12),
                'quantity' => rand(20, 80),
                'cost_price' => $product->cost_price * 0.95, // Slightly different cost
            ],
            [
                'batch_number' => 'B003-' . $product->code,
                'expiry_date' => now()->addDays(15), // Expiring soon
                'quantity' => rand(5, 25),
                'cost_price' => $product->cost_price * 1.05,
            ],
        ];

        // Create one low-stock product
        if ($product->code === 'PARA500') {
            $batches[0]['quantity'] = 5; // Low stock
            $batches[1]['quantity'] = 8;
            $batches[2]['quantity'] = 2;
        }

        // Create one expired batch for demonstration
        if ($product->code === 'ASP100') {
            $batches[] = [
                'batch_number' => 'B004-' . $product->code,
                'expiry_date' => now()->subDays(30), // Expired
                'quantity' => rand(10, 30),
                'cost_price' => $product->cost_price,
            ];
        }

        foreach ($batches as $batchData) {
            $product->batches()->create($batchData);
        }
    }
}

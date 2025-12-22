<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample suppliers
        $suppliers = [
            [
                'name' => 'MediSupply Corp',
                'contact_person' => 'John Smith',
                'phone' => '+1-555-0123',
                'email' => 'orders@medisupply.com',
                'address' => '123 Medical District, Healthcare City, HC 12345',
                'notes' => 'Primary supplier for general medications',
                'is_active' => true,
            ],
            [
                'name' => 'PharmaCorp International',
                'contact_person' => 'Sarah Johnson',
                'phone' => '+1-555-0456',
                'email' => 'procurement@pharmacorp.com',
                'address' => '456 Pharma Avenue, Medicine Town, MT 67890',
                'notes' => 'Specializes in antibiotics and pain relief',
                'is_active' => true,
            ],
            [
                'name' => 'VitaHealth Distributors',
                'contact_person' => 'Mike Wilson',
                'phone' => '+1-555-0789',
                'email' => 'sales@vitahealth.com',
                'address' => '789 Wellness Street, Health City, HC 54321',
                'notes' => 'Vitamins and supplements supplier',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }

        // Create sample purchases
        $this->createSamplePurchases();
    }

    private function createSamplePurchases(): void
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $adminUser = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->first();

        if (!$adminUser || $products->count() === 0) {
            return;
        }

        // Create 3 sample purchases
        for ($i = 1; $i <= 3; $i++) {
            $supplier = $suppliers->random();

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'user_id' => $adminUser->id,
                'purchase_date' => now()->subDays(rand(1, 30)),
                'reference_number' => Purchase::generateReferenceNumber(),
                'notes' => "Sample purchase order #{$i}",
                'total_cost' => 0,
                'status' => $i === 1 ? 'completed' : ($i === 2 ? 'pending' : 'completed'),
            ]);

            // Add 2-4 items to each purchase
            $itemCount = rand(2, 4);
            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                $quantity = rand(10, 100);
                $unitCost = $product->cost_price * (1 + (rand(-10, 20) / 100)); // ±20% variation

                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'batch_number' => 'B' . str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . $product->code,
                    'expiry_date' => now()->addMonths(rand(6, 24)),
                ]);

                // Create batch if purchase is completed
                if ($purchase->status === 'completed') {
                    $purchaseItem->createOrUpdateBatch();
                }
            }
        }
    }
}

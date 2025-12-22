<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PharmaceuticalPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createPharmaceuticalPurchases();
    }

    private function createPharmaceuticalPurchases(): void
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $adminUsers = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();

        if ($suppliers->isEmpty() || $products->isEmpty() || $adminUsers->isEmpty()) {
            $this->command->warn('Please run suppliers, products, and users seeders first.');
            return;
        }

        // Create purchases for the last 6 months
        for ($month = 6; $month >= 1; $month--) {
            $purchaseDate = Carbon::now()->subMonths($month)->addDays(rand(1, 28));

            // Create 2-4 purchases per month
            $purchasesThisMonth = rand(2, 4);

            for ($i = 0; $i < $purchasesThisMonth; $i++) {
                $this->createPurchaseOrder(
                    $suppliers->random(),
                    $products,
                    $adminUsers->random(),
                    $purchaseDate->copy()->addDays(rand(0, 15))
                );
            }
        }

        // Create some recent purchases (last 30 days)
        for ($i = 0; $i < 8; $i++) {
            $this->createPurchaseOrder(
                $suppliers->random(),
                $products,
                $adminUsers->random(),
                Carbon::now()->subDays(rand(1, 30))
            );
        }
    }

    private function createPurchaseOrder(Supplier $supplier, $products, User $user, Carbon $purchaseDate): void
    {
        $referenceNumber = $this->generateReferenceNumber($purchaseDate);

        $purchase = Purchase::create([
            'reference_number' => $referenceNumber,
            'supplier_id' => $supplier->id,
            'user_id' => $user->id,
            'purchase_date' => $purchaseDate,
            'status' => $this->getRandomStatus($purchaseDate),
            'notes' => $this->generatePurchaseNotes($supplier),
            'total_cost' => 0, // Will be calculated after adding items
            'created_at' => $purchaseDate,
            'updated_at' => $purchaseDate,
        ]);

        // Add 3-8 different products to each purchase
        $selectedProducts = $products->random(rand(3, 8));
        $totalCost = 0;

        foreach ($selectedProducts as $product) {
            $quantity = $this->getRealisticQuantity($product);
            $unitCost = $this->getRealisticUnitCost($product);
            $subtotal = $quantity * $unitCost;
            $totalCost += $subtotal;

            $purchaseItem = PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $subtotal,
                'batch_number' => $this->generateBatchNumber($product->code, $purchaseDate),
                'expiry_date' => $this->generateExpiryDate($product, $purchaseDate),
            ]);

            // If purchase is completed, create/update batch
            if ($purchase->status === 'completed') {
                $this->createOrUpdateBatch($purchaseItem, $product, $quantity);
            }
        }

        // Update purchase total cost
        $purchase->update(['total_cost' => $totalCost]);
    }

    private function createOrUpdateBatch(PurchaseItem $purchaseItem, Product $product, int $quantity): void
    {
        // Check if batch already exists
        $existingBatch = Batch::where('batch_number', $purchaseItem->batch_number)->first();

        if ($existingBatch) {
            // Update existing batch quantity
            $existingBatch->increment('quantity', $quantity);
        } else {
            // Create new batch
            Batch::create([
                'product_id' => $product->id,
                'batch_number' => $purchaseItem->batch_number,
                'quantity' => $quantity,
                'cost_price' => $purchaseItem->unit_cost,
                'expiry_date' => $purchaseItem->expiry_date,
            ]);
        }
    }

    private function generateReferenceNumber(Carbon $date): string
    {
        $year = $date->format('Y');
        $month = $date->format('m');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        return "PO-{$year}{$month}-{$sequence}";
    }

    private function getRandomStatus(Carbon $purchaseDate): string
    {
        $daysSincePurchase = Carbon::now()->diffInDays($purchaseDate);

        if ($daysSincePurchase > 30) {
            return 'completed'; // Old purchases are completed
        } elseif ($daysSincePurchase > 14) {
            return collect(['completed', 'completed', 'completed', 'pending'])->random(); // Mostly completed
        } else {
            return collect(['completed', 'pending'])->random(); // Recent purchases mixed
        }
    }



    private function generatePurchaseNotes(Supplier $supplier): string
    {
        $notes = [
            "Regular monthly order from {$supplier->name}",
            "Bulk purchase for seasonal demand",
            "Emergency restock order",
            "Special pricing negotiated with {$supplier->contact_person}",
            "Fast-moving items replenishment",
            "New product trial order",
            "Quarterly bulk purchase agreement",
            "Promotional pricing order",
        ];

        return collect($notes)->random();
    }

    private function getRealisticQuantity(Product $product): int
    {
        // Base quantity on product category and alert quantity
        $baseQuantity = $product->alert_quantity ?? 50;

        switch ($product->category) {
            case 'Pain Relief':
            case 'Vitamins':
                return rand($baseQuantity, $baseQuantity * 3); // High demand
            case 'Antibiotics':
                return rand($baseQuantity, $baseQuantity * 2); // Moderate demand
            case 'Cold & Flu':
                return rand($baseQuantity, $baseQuantity * 4); // Seasonal high demand
            case 'Digestive':
            case 'Allergy':
            case 'Topical':
                return rand($baseQuantity, $baseQuantity * 2); // Moderate demand
            default:
                return rand($baseQuantity, $baseQuantity * 2);
        }
    }

    private function getRealisticUnitCost(Product $product): float
    {
        // Cost should be 70-90% of the product's cost price for bulk purchases
        $baseCost = $product->cost_price;
        $discountFactor = rand(70, 90) / 100;

        return round($baseCost * $discountFactor, 2);
    }

    private function generateBatchNumber(string $productCode, Carbon $purchaseDate): string
    {
        $year = $purchaseDate->format('Y');
        $month = $purchaseDate->format('m');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        return "B{$year}{$month}-{$productCode}-{$sequence}";
    }

    private function generateExpiryDate(Product $product, Carbon $purchaseDate): Carbon
    {
        // Different product categories have different shelf lives
        $shelfLifeMonths = match($product->category) {
            'Pain Relief' => rand(18, 36),
            'Antibiotics' => rand(12, 24),
            'Vitamins' => rand(24, 48),
            'Cold & Flu' => rand(12, 30),
            'Digestive' => rand(18, 36),
            'Allergy' => rand(24, 36),
            'Topical' => rand(12, 24),
            default => rand(18, 30),
        };

        return $purchaseDate->copy()->addMonths($shelfLifeMonths);
    }
}

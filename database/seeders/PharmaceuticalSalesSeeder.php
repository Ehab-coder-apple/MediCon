<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PharmaceuticalSalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createCustomers();
        $this->createPharmaceuticalSales();
    }

    private function createCustomers(): void
    {
        $customers = [
            [
                'name' => 'John Smith',
                'phone' => '+1-555-2001',
                'email' => 'john.smith@email.com',
            ],
            [
                'name' => 'Mary Johnson',
                'phone' => '+1-555-2002',
                'email' => 'mary.johnson@email.com',
            ],
            [
                'name' => 'Robert Davis',
                'phone' => '+1-555-2003',
                'email' => 'robert.davis@email.com',
            ],
            [
                'name' => 'Jennifer Wilson',
                'phone' => '+1-555-2004',
                'email' => 'jennifer.wilson@email.com',
            ],
            [
                'name' => 'Michael Brown',
                'phone' => '+1-555-2005',
                'email' => 'michael.brown@email.com',
            ],
            [
                'name' => 'Sarah Miller',
                'phone' => '+1-555-2006',
                'email' => 'sarah.miller@email.com',
            ],
            [
                'name' => 'David Garcia',
                'phone' => '+1-555-2007',
                'email' => 'david.garcia@email.com',
            ],
            [
                'name' => 'Lisa Rodriguez',
                'phone' => '+1-555-2008',
                'email' => 'lisa.rodriguez@email.com',
            ],
            [
                'name' => 'James Martinez',
                'phone' => '+1-555-2009',
                'email' => 'james.martinez@email.com',
            ],
            [
                'name' => 'Amanda Taylor',
                'phone' => '+1-555-2010',
                'email' => 'amanda.taylor@email.com',
            ],
            [
                'name' => 'Christopher Anderson',
                'phone' => '+1-555-2011',
                'email' => 'chris.anderson@email.com',
            ],
            [
                'name' => 'Jessica Thomas',
                'phone' => '+1-555-2012',
                'email' => 'jessica.thomas@email.com',
            ],
            [
                'name' => 'Daniel Jackson',
                'phone' => '+1-555-2013',
                'email' => 'daniel.jackson@email.com',
            ],
            [
                'name' => 'Ashley White',
                'phone' => '+1-555-2014',
                'email' => 'ashley.white@email.com',
            ],
            [
                'name' => 'Matthew Harris',
                'phone' => '+1-555-2015',
                'email' => 'matthew.harris@email.com',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::updateOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );
        }
    }

    private function createPharmaceuticalSales(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $salesUsers = User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'pharmacist', 'sales_staff']))->get();

        if ($products->isEmpty() || $salesUsers->isEmpty()) {
            $this->command->warn('Please run products and users seeders first.');
            return;
        }

        // Create sales for the last 3 months
        for ($month = 3; $month >= 1; $month--) {
            $salesThisMonth = rand(15, 25); // 15-25 sales per month

            for ($i = 0; $i < $salesThisMonth; $i++) {
                $saleDate = Carbon::now()->subMonths($month)->addDays(rand(1, 28));
                $this->createSaleTransaction($customers, $products, $salesUsers, $saleDate);
            }
        }

        // Create recent sales (last 30 days) with higher frequency
        for ($i = 0; $i < 40; $i++) {
            $saleDate = Carbon::now()->subDays(rand(1, 30));
            $this->createSaleTransaction($customers, $products, $salesUsers, $saleDate);
        }

        // Create today's sales
        for ($i = 0; $i < rand(3, 8); $i++) {
            $saleDate = Carbon::today()->addHours(rand(9, 18))->addMinutes(rand(0, 59));
            $this->createSaleTransaction($customers, $products, $salesUsers, $saleDate);
        }
    }

    private function createSaleTransaction($customers, $products, $salesUsers, Carbon $saleDate): void
    {
        $invoiceNumber = $this->generateInvoiceNumber($saleDate);

        // 70% chance of having a registered customer, 30% walk-in
        $customer = rand(1, 100) <= 70 ? $customers->random() : null;

        $sale = Sale::create([
            'invoice_number' => $invoiceNumber,
            'customer_id' => $customer?->id,
            'user_id' => $salesUsers->random()->id,
            'sale_date' => $saleDate,
            'payment_method' => $this->getRandomPaymentMethod(),
            'status' => $this->getRandomStatus($saleDate),
            'notes' => $this->generateSaleNotes($customer),
            'total_price' => 0, // Will be calculated after adding items
            'paid_amount' => 0, // Will be calculated after adding items
            'discount_amount' => 0,
            'tax_amount' => 0,
            'change_amount' => 0,
            'created_at' => $saleDate,
            'updated_at' => $saleDate,
        ]);

        // Add 1-5 different products to each sale
        $selectedProducts = $products->random(rand(1, 5));
        $totalPrice = 0;

        foreach ($selectedProducts as $product) {
            $quantity = $this->getRealisticSaleQuantity($product);
            $unitPrice = $this->getRealisticSalePrice($product);
            $subtotal = $quantity * $unitPrice;
            $totalPrice += $subtotal;

            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $subtotal,
                'batch_id' => $this->getAvailableBatch($product)?->id,
            ]);

            // Update batch quantity if sale is completed
            if ($sale->status === 'completed' && $saleItem->batch_id) {
                $batch = Batch::find($saleItem->batch_id);
                if ($batch && $batch->quantity >= $quantity) {
                    $batch->decrement('quantity', $quantity);
                }
            }
        }

        // Update sale total price and paid amount
        $sale->update([
            'total_price' => $totalPrice,
            'paid_amount' => $totalPrice, // Assume full payment
        ]);
    }

    private function getAvailableBatch(Product $product): ?Batch
    {
        return Batch::where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->first();
    }

    private function generateInvoiceNumber(Carbon $date): string
    {
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        return "INV-{$year}{$month}{$day}-{$sequence}";
    }

    private function getRandomStatus(Carbon $saleDate): string
    {
        $hoursAgo = Carbon::now()->diffInHours($saleDate);

        if ($hoursAgo > 24) {
            return 'completed'; // Old sales are completed
        } else {
            return collect(['completed', 'completed', 'completed', 'pending'])->random(); // Recent sales mostly completed
        }
    }

    private function getRandomPaymentMethod(): string
    {
        return collect([
            'cash',
            'card',
            'insurance',
            'mixed',
        ])->random();
    }

    private function generateSaleNotes(?Customer $customer): ?string
    {
        if (!$customer) {
            return collect([
                'Walk-in customer',
                'Cash sale - no receipt requested',
                'Tourist customer',
                'Emergency purchase',
                null, // Some sales have no notes
            ])->random();
        }

        return collect([
            "Regular customer - {$customer->name}",
            'Prescription filled',
            'Customer requested generic alternative',
            'Insurance claim processed',
            'Customer loyalty discount applied',
            'Bulk purchase discount',
            null, // Some sales have no notes
        ])->random();
    }

    private function getRealisticSaleQuantity(Product $product): int
    {
        // Most sales are small quantities
        switch ($product->category) {
            case 'Pain Relief':
            case 'Cold & Flu':
                return rand(1, 3); // Usually 1-3 boxes/bottles
            case 'Antibiotics':
                return 1; // Usually prescribed as single course
            case 'Vitamins':
                return rand(1, 2); // 1-2 bottles
            case 'Digestive':
            case 'Allergy':
            case 'Topical':
                return rand(1, 2); // 1-2 units
            default:
                return rand(1, 2);
        }
    }

    private function getRealisticSalePrice(Product $product): float
    {
        // Price should be close to selling price with occasional discounts
        $basePrice = $product->selling_price;

        // 80% chance of full price, 20% chance of discount
        if (rand(1, 100) <= 20) {
            $discountFactor = rand(85, 95) / 100; // 5-15% discount
            return round($basePrice * $discountFactor, 2);
        }

        return $basePrice;
    }
}

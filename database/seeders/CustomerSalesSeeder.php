<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample customers
        $customers = [
            [
                'name' => 'John Smith',
                'phone' => '+1-555-0101',
                'email' => 'john.smith@email.com',
                'address' => '123 Main Street, Anytown, AT 12345',
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'is_active' => true,
            ],
            [
                'name' => 'Sarah Johnson',
                'phone' => '+1-555-0202',
                'email' => 'sarah.johnson@email.com',
                'address' => '456 Oak Avenue, Somewhere, SW 67890',
                'date_of_birth' => '1990-03-22',
                'gender' => 'female',
                'insurance_number' => 'INS123456789',
                'is_active' => true,
            ],
            [
                'name' => 'Michael Brown',
                'phone' => '+1-555-0303',
                'address' => '789 Pine Road, Elsewhere, EL 54321',
                'date_of_birth' => '1978-11-08',
                'gender' => 'male',
                'emergency_contact' => 'Jane Brown - +1-555-0304',
                'medical_notes' => 'Allergic to penicillin',
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }

        // Create sample sales
        $this->createSampleSales();
    }

    private function createSampleSales(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $salesUser = User::whereHas('role', function($q) {
            $q->whereIn('name', ['admin', 'pharmacist', 'sales_staff']);
        })->first();

        if (!$salesUser || $products->count() === 0) {
            return;
        }

        // Create 5 sample sales
        for ($i = 1; $i <= 5; $i++) {
            $customer = $i <= 3 ? $customers->random() : null; // Some walk-in customers

            $sale = Sale::create([
                'customer_id' => $customer?->id,
                'user_id' => $salesUser->id,
                'sale_date' => now()->subDays(rand(0, 7)),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'total_price' => 0, // Will be calculated
                'discount_amount' => rand(0, 1) ? rand(5, 20) : 0,
                'tax_amount' => 0, // No tax for simplicity
                'paid_amount' => 0, // Will be set after calculating total
                'change_amount' => 0, // Will be calculated
                'payment_method' => ['cash', 'card', 'insurance'][rand(0, 2)],
                'notes' => $i === 1 ? 'Customer requested generic alternatives' : null,
                'status' => 'completed',
            ]);

            // Add 1-3 items to each sale
            $itemCount = rand(1, 3);
            $selectedProducts = $products->random($itemCount);
            $subtotal = 0;

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->selling_price;
                $itemDiscount = rand(0, 1) ? rand(1, 5) : 0;
                $itemTotal = ($quantity * $unitPrice) - $itemDiscount;
                $subtotal += $itemTotal;

                // Find an available batch
                $batch = $product->batches()->where('quantity', '>', 0)->first();

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch?->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                ]);
            }

            // Update sale totals
            $totalAfterDiscount = $subtotal - $sale->discount_amount;
            $paidAmount = $totalAfterDiscount + rand(0, 20); // Sometimes overpaid
            $changeAmount = max(0, $paidAmount - $totalAfterDiscount);

            $sale->update([
                'total_price' => $totalAfterDiscount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
            ]);
        }
    }
}

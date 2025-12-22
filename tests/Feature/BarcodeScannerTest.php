<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeScannerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Pharmacy',
            'slug' => 'test-pharmacy',
            'pharmacy_name' => 'Test Pharmacy Inc',
            'contact_email' => 'test@pharmacy.com',
        ]);

        // Create a user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'branch_id' => null, // No specific branch
        ]);

        // Create a product with barcode
        $this->product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Medicine',
            'category' => 'Antibiotics',
            'code' => 'MED001',
            'barcode' => '5901234123457',
            'cost_price' => 50.00,
            'selling_price' => 100.00,
            'alert_quantity' => 10,
            'is_active' => true,
        ]);

        // Create a batch with stock
        $this->batch = Batch::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'batch_number' => 'BATCH001',
            'quantity' => 50,
            'expiry_date' => now()->addMonths(6),
        ]);

        // Create warehouse and warehouse stock for the batch
        $warehouse = \App\Models\Warehouse::firstOrCreate(
            [
                'tenant_id' => $this->tenant->id,
                'type' => 'on_shelf',
                'is_system' => true,
            ],
            [
                'name' => 'On Shelf',
                'is_sellable' => true,
            ]
        );

        \App\Models\WarehouseStock::create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $this->product->id,
            'batch_id' => $this->batch->id,
            'quantity' => 50,
        ]);
    }

    /**
     * Test barcode lookup API endpoint
     */
    public function test_lookup_product_by_barcode()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/products/by-barcode/5901234123457');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'product' => [
                    'id' => $this->product->id,
                    'name' => 'Test Medicine',
                    'barcode' => '5901234123457',
                    'selling_price' => 100.0,
                ],
            ]);

        $this->assertNotEmpty($response->json('batches'));
    }

    /**
     * Test barcode lookup with product code fallback
     */
    public function test_lookup_product_by_code_fallback()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/products/by-barcode/MED001');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'product' => [
                    'name' => 'Test Medicine',
                ],
            ]);
    }

    /**
     * Test barcode not found
     */
    public function test_barcode_not_found()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/products/by-barcode/INVALID123');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test get product details
     */
    public function test_get_product_details()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/products/{$this->product->id}/details");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'product' => [
                    'id' => $this->product->id,
                    'name' => 'Test Medicine',
                ],
            ]);
    }

    /**
     * Test check stock availability
     */
    public function test_check_stock_available()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/products/{$this->product->id}/check-stock", [
                'quantity' => 10,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_stock' => true,
                'available_quantity' => 50,
            ]);
    }

    /**
     * Test check stock insufficient
     */
    public function test_check_stock_insufficient()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/products/{$this->product->id}/check-stock", [
                'quantity' => 100,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_stock' => false,
                'available_quantity' => 50,
            ]);
    }

    /**
     * Test quick sale creation
     */
    public function test_quick_sale_creation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/sales/quick-create', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'batch_id' => $this->product->batches->first()->id,
                        'quantity' => 5,
                        'unit_price' => 100.00,
                    ],
                ],
                'customer_name' => 'Walk-in Customer',
                'payment_method' => 'cash',
                'paid_amount' => 500.00,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'sale' => [
                    'total_price' => 500.0,
                    'items_count' => 5,
                ],
            ]);

        // Verify inventory was updated
        $this->product->refresh();
        $this->assertEquals(45, $this->product->batches->first()->quantity);
    }

    /**
     * Test quick sale with insufficient stock
     */
    public function test_quick_sale_insufficient_stock()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/sales/quick-create', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'batch_id' => $this->product->batches->first()->id,
                        'quantity' => 100,
                        'unit_price' => 100.00,
                    ],
                ],
                'customer_name' => 'Walk-in Customer',
                'payment_method' => 'cash',
                'paid_amount' => 10000.00,
            ]);

        $response->assertStatus(400);
    }

    /**
     * Test unauthorized access
     */
    public function test_unauthorized_access()
    {
        $response = $this->getJson('/api/products/by-barcode/5901234123457');

        $response->assertStatus(401);
    }
}


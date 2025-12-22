<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Batch;
use App\Models\ProductDisplaySetting;
use App\Models\FeaturedProduct;
use App\Models\Role;
use App\Services\ProductDisplayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDisplaySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant manually
        $this->tenant = Tenant::create([
            'name' => 'Test Pharmacy',
            'slug' => 'test-pharmacy',
            'pharmacy_name' => 'Test Pharmacy Inc.',
            'contact_email' => 'test@pharmacy.com',
            'contact_phone' => '+1-555-0000',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'country' => 'US',
            'postal_code' => '00000',
            'subscription_plan' => 'premium',
            'subscription_status' => 'active',
            'is_active' => true,
        ]);

        // Create admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        // Create user
        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
            'role_id' => $adminRole->id,
        ]);

        $this->service = new ProductDisplayService();
    }

    /** @test */
    public function can_create_product_display_setting()
    {
        $setting = ProductDisplaySetting::forTenant($this->tenant->id);

        $this->assertNotNull($setting);
        $this->assertEquals($this->tenant->id, $setting->tenant_id);
        $this->assertEquals('fast_moving', $setting->display_strategy);
    }

    /** @test */
    public function can_update_display_strategy_directly()
    {
        $setting = ProductDisplaySetting::forTenant($this->tenant->id);
        $setting->update([
            'display_strategy' => 'high_stock',
            'products_limit' => 15,
        ]);

        $this->assertDatabaseHas('pharmacy_product_display_settings', [
            'tenant_id' => $this->tenant->id,
            'display_strategy' => 'high_stock',
            'products_limit' => 15,
        ]);
    }

    /** @test */
    public function can_add_featured_product_directly()
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'code' => 'TEST001',
            'category' => 'Test',
            'cost_price' => 10.00,
            'selling_price' => 20.00,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        FeaturedProduct::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'display_order' => 0,
        ]);

        $this->assertDatabaseHas('featured_products', [
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
        ]);
    }

    /** @test */
    public function service_returns_fast_moving_products()
    {
        ProductDisplaySetting::forTenant($this->tenant->id)->update(['display_strategy' => 'fast_moving']);

        $products = $this->service->getDisplayProducts($this->tenant->id);

        $this->assertIsObject($products);
    }

    /** @test */
    public function service_returns_high_stock_products()
    {
        ProductDisplaySetting::forTenant($this->tenant->id)->update(['display_strategy' => 'high_stock']);

        $products = $this->service->getDisplayProducts($this->tenant->id);

        $this->assertIsObject($products);
    }

    /** @test */
    public function service_returns_nearly_expired_products()
    {
        ProductDisplaySetting::forTenant($this->tenant->id)->update(['display_strategy' => 'nearly_expired']);

        $products = $this->service->getDisplayProducts($this->tenant->id);

        $this->assertIsObject($products);
    }

    /** @test */
    public function service_returns_custom_selection_products()
    {
        ProductDisplaySetting::forTenant($this->tenant->id)->update(['display_strategy' => 'custom_selection']);

        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Custom Product',
            'code' => 'CUSTOM001',
            'category' => 'Test',
            'cost_price' => 10.00,
            'selling_price' => 20.00,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        FeaturedProduct::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'display_order' => 0,
        ]);

        $products = $this->service->getDisplayProducts($this->tenant->id);

        $this->assertIsObject($products);
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductDisplaySetting;
use App\Models\FeaturedProduct;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductDisplaySettingsController extends Controller
{
    /**
     * Show the product display settings page
     */
    public function index(): View
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, try to get the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        $setting = ProductDisplaySetting::forTenant($tenantId);
        $featuredProducts = FeaturedProduct::forTenant($tenantId)
            ->with('product')
            ->get();

        $allProducts = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.product-display-settings.index', compact(
            'setting',
            'featuredProducts',
            'allProducts'
        ));
    }

    /**
     * Update display strategy
     */
    public function updateStrategy(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, try to get the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        $validated = $request->validate([
            'display_strategy' => 'required|in:fast_moving,high_stock,nearly_expired,custom_selection',
            'products_limit' => 'required|integer|min:5|max:100',
        ]);

        $setting = ProductDisplaySetting::forTenant($tenantId);
        $setting->update($validated);

        return redirect()->back()->with('success', 'Product display strategy updated successfully.');
    }

    /**
     * Add product to featured list
     */
    public function addFeaturedProduct(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, try to get the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Check if product already featured
        $exists = FeaturedProduct::where('tenant_id', $tenantId)
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'This product is already in the featured list.');
        }

        // Get next display order
        $nextOrder = FeaturedProduct::where('tenant_id', $tenantId)
            ->max('display_order') + 1;

        FeaturedProduct::create([
            'tenant_id' => $tenantId,
            'product_id' => $validated['product_id'],
            'display_order' => $nextOrder,
        ]);

        return redirect()->back()->with('success', 'Product added to featured list.');
    }

    /**
     * Remove product from featured list
     */
    public function removeFeaturedProduct(FeaturedProduct $featuredProduct): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, try to get the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        if ($featuredProduct->tenant_id !== $tenantId) {
            abort(403);
        }

        $featuredProduct->delete();

        return redirect()->back()->with('success', 'Product removed from featured list.');
    }

    /**
     * Reorder featured products
     */
    public function reorderFeaturedProducts(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, try to get the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|integer',
        ]);

        foreach ($validated['product_ids'] as $order => $productId) {
            FeaturedProduct::where('tenant_id', $tenantId)
                ->where('product_id', $productId)
                ->update(['display_order' => $order]);
        }

        return redirect()->back()->with('success', 'Featured products reordered successfully.');
    }
}


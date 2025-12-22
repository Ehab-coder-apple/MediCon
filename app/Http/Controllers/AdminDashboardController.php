<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Batch;
use App\Models\Product;
use Illuminate\Http\Request;
class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::active()->count();
        $adminUsers = User::withRole(Role::ADMIN)->count();
        $pharmacistUsers = User::withRole(Role::PHARMACIST)->count();
        $salesStaffUsers = User::withRole(Role::SALES_STAFF)->count();

        // Inventory metrics
        $totalExpiredProducts = $this->getTotalExpiredProducts();
        $totalNearlyExpiredProducts = $this->getTotalNearlyExpiredProducts();
        $lowStockProducts = $this->getLowStockProductsCount();
        $outOfStockProducts = $this->getOutOfStockProductsCount();

        return view('admin.dashboard', compact(
            'totalUsers',
            'adminUsers',
            'pharmacistUsers',
            'salesStaffUsers',
            'totalExpiredProducts',
            'totalNearlyExpiredProducts',
            'lowStockProducts',
            'outOfStockProducts'
        ));
    }

    /**
     * Get count of products with expired batches
     */
    private function getTotalExpiredProducts(): int
    {
        return Product::whereHas('batches', function ($query) {
            $query->where('expiry_date', '<=', now());
        })->distinct()->count();
    }

    /**
     * Get count of products with batches expiring soon (within 30 days)
     */
    private function getTotalNearlyExpiredProducts(): int
    {
        return Product::whereHas('batches', function ($query) {
            $query->where('expiry_date', '>', now())
                  ->where('expiry_date', '<=', now()->addDays(30));
        })->distinct()->count();
    }

    /**
     * Get count of products with low stock (active quantity <= alert quantity)
     */
    private function getLowStockProductsCount(): int
    {
        $products = Product::with(['batches'])->get();
        return $products->filter(function ($product) {
            return $product->is_low_stock;
        })->count();
    }

    /**
     * Get count of products with zero quantity
     */
    private function getOutOfStockProductsCount(): int
    {
        $products = Product::with(['batches'])->get();
        return $products->filter(function ($product) {
            return $product->active_quantity == 0;
        })->count();
    }

    public function users()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('role')->paginate(10);
        return view('admin.users', compact('users'));
    }
}

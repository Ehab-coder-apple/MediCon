<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display inventory dashboard with alerts
     */
    public function dashboard(): View
    {
        // Get low stock products
        $lowStockProducts = Product::with(['batches'])
            ->get()
            ->filter(function ($product) {
                return $product->is_low_stock;
            });

        // Get expired batches
        $expiredBatches = Batch::with('product')
            ->expired()
            ->orderBy('expiry_date', 'desc')
            ->limit(10)
            ->get();

        // Get expiring soon batches
        $expiringSoonBatches = Batch::with('product')
            ->expiringSoon()
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        // Get out of stock products
        $outOfStockProducts = Product::with(['batches'])
            ->get()
            ->filter(function ($product) {
                return $product->active_quantity == 0;
            });

        // Calculate summary statistics
        $totalProducts = Product::where('is_active', true)->count();
        $totalBatches = Batch::count();
        $activeBatches = Batch::active()->count();
        $totalValue = $this->calculateInventoryValue();

        return view('inventory.dashboard', compact(
            'lowStockProducts',
            'expiredBatches',
            'expiringSoonBatches',
            'outOfStockProducts',
            'totalProducts',
            'totalBatches',
            'activeBatches',
            'totalValue'
        ));
    }

    /**
     * Display detailed inventory report
     */
    public function report(): View
    {
        $products = Product::with(['batches' => function ($query) {
            $query->orderBy('expiry_date');
        }])->paginate(15);

        return view('inventory.report', compact('products'));
    }

    /**
     * Display alerts page
     */
    public function alerts(): View
    {
        $lowStockProducts = Product::with(['batches'])
            ->get()
            ->filter(function ($product) {
                return $product->is_low_stock;
            });

        $expiredBatches = Batch::with('product')
            ->expired()
            ->orderBy('expiry_date', 'desc')
            ->paginate(15, ['*'], 'expired');

        $expiringSoonBatches = Batch::with('product')
            ->expiringSoon()
            ->orderBy('expiry_date')
            ->paginate(15, ['*'], 'expiring');

        return view('inventory.alerts', compact(
            'lowStockProducts',
            'expiredBatches',
            'expiringSoonBatches'
        ));
    }

    /**
     * Calculate total inventory value
     */
    private function calculateInventoryValue(): float
    {
        return Batch::active()
            ->get()
            ->sum(function ($batch) {
                $costPrice = $batch->cost_price ?? $batch->product->cost_price;
                return $batch->quantity * $costPrice;
            });
    }
}

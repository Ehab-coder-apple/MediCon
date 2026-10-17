<?php

namespace App\Http\Controllers;

use App\Models\BranchRequisition;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\BranchContextService;
use Illuminate\Http\Request;

class PharmacistDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $activeBranch = BranchContextService::getActiveUserBranchContext($user);

        // Branch-scoped dashboard stats. These used to be hardcoded sample
        // values; now they reflect only this branch's own sales/stock, not
        // the tenant-wide totals (e.g. a brand-new branch with 2 products
        // and no sales yet must show 0/0/2, not another branch's or HQ's
        // numbers).
        $salesToday = 0;
        $inventoryAlerts = 0;
        $totalMedicines = 0;

        if ($activeBranch) {
            $salesToday = Invoice::where('tenant_id', $user->tenant_id)
                ->whereDate('invoice_date', today())
                ->where('status', '!=', 'draft')
                ->whereHas('user', fn ($q) => $q->where('branch_id', $activeBranch->id))
                ->count();

            $branchWarehouseIds = Warehouse::where('tenant_id', $user->tenant_id)
                ->where('branch_id', $activeBranch->id)
                ->whereIn('type', [Warehouse::TYPE_MAIN, Warehouse::TYPE_ON_SHELF])
                ->pluck('id');

            // Quantity actually on hand at this branch, per product - not
            // the tenant-wide Product::active_quantity (see Product model).
            $branchStockByProduct = WarehouseStock::whereIn('warehouse_id', $branchWarehouseIds)
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->groupBy('product_id')
                ->pluck('qty', 'product_id');

            $stockedProducts = Product::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->get(['id', 'alert_quantity'])
                ->filter(fn ($product) => ($branchStockByProduct[$product->id] ?? 0) > 0);

            $totalMedicines = $stockedProducts->count();
            $inventoryAlerts = $stockedProducts
                ->filter(fn ($product) => ($branchStockByProduct[$product->id] ?? 0) <= $product->alert_quantity)
                ->count();
        }

        // Product catalog for the "Request Stock from HQ" and "Replenish
        // Shelf from Backroom" slide-over panels.
        $requisitionProducts = Product::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // This branch's own pending/recent requisitions, so the pharmacist
        // can see what they've already asked HQ for and its status.
        $myRequisitions = collect();
        if ($activeBranch) {
            $myRequisitions = BranchRequisition::where('tenant_id', $user->tenant_id)
                ->where('branch_id', $activeBranch->id)
                ->with('product')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        return view('pharmacist.dashboard', compact(
            'salesToday',
            'inventoryAlerts',
            'totalMedicines',
            'activeBranch',
            'requisitionProducts',
            'myRequisitions'
        ));
    }

    /**
     * "Check Inventory" used to render a hardcoded placeholder table
     * (fake Paracetamol/Aspirin rows, same for every branch). Redirect to
     * the real, branch-scoped product inventory list instead (My Branch
     * Stock + HQ Availability columns - see ProductController::index()).
     */
    public function inventory()
    {
        return redirect()->route('pharmacist.products.index');
    }


}

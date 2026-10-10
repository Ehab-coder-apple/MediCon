<?php

namespace App\Http\Controllers;

use App\Models\BranchRequisition;
use App\Models\Product;
use App\Services\BranchContextService;
use Illuminate\Http\Request;

class PharmacistDashboardController extends Controller
{
    public function index()
    {
        // Sample data for pharmacist dashboard
        $salesToday = 25;
        $inventoryAlerts = 5;
        $totalMedicines = 150;

        $user = auth()->user();
        $activeBranch = BranchContextService::getActiveUserBranchContext($user);

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

    public function inventory()
    {
        return view('pharmacist.inventory');
    }


}

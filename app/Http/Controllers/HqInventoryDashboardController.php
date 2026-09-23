<?php

namespace App\Http\Controllers;

use App\Models\StockTransferOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HqInventoryDashboardController extends Controller
{
    /**
     * HQ Inventory Manager landing page: Central warehouse overview and
     * stock transfer order pipeline across all branches.
     */
    public function index(Request $request): View
    {
        $this->authorize('access-hq-inventory-dashboard');

        $tenantId = $request->user()->tenant_id;

        $centralWarehouses = Warehouse::where('tenant_id', $tenantId)
            ->whereIn('type', Warehouse::CENTRAL_TYPES)
            ->withSum('stocks as stock_quantity', 'quantity')
            ->orderBy('name')
            ->get();

        $pendingOrders = StockTransferOrder::where('tenant_id', $tenantId)
            ->whereIn('status', [StockTransferOrder::STATUS_PENDING, StockTransferOrder::STATUS_IN_TRANSIT])
            ->with(['sourceWarehouse', 'destinationWarehouse', 'initiator', 'items'])
            ->orderByDesc('created_at')
            ->get();

        $recentOrders = StockTransferOrder::where('tenant_id', $tenantId)
            ->whereIn('status', [StockTransferOrder::STATUS_RECEIVED, StockTransferOrder::STATUS_CANCELLED])
            ->with(['sourceWarehouse', 'destinationWarehouse', 'receiver'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('hq.inventory.dashboard', compact('centralWarehouses', 'pendingOrders', 'recentOrders'));
    }
}

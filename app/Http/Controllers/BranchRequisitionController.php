<?php

namespace App\Http\Controllers;

use App\Models\BranchRequisition;
use App\Models\Warehouse;
use App\Services\BranchContextService;
use App\Services\StockTransferOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchRequisitionController extends Controller
{
    public function __construct(private readonly StockTransferOrderService $stockTransferOrderService)
    {
    }

    /**
     * Submit a new stock requisition from a retail branch up to HQ. Either
     * product_id (an existing catalog product) or custom_product_name (a
     * drug not yet in the system) must be provided.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-inventory');

        $user = $request->user();
        $branch = BranchContextService::getActiveUserBranchContext($user);

        if (! $branch || ! $branch->isRetail()) {
            return $this->fail($request, 'Stock requisitions can only be submitted from a retail pharmacy branch.');
        }

        // Odoo-style batch requisition grid on the pharmacist dashboard:
        // one "Submit Bulk Request" click posts every product+quantity the
        // pharmacist dropped into the cart in a single array payload.
        if ($request->has('items')) {
            return $this->storeBulk($request, $user, $branch);
        }

        $validated = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'custom_product_name' => 'nullable|string|max:255|required_without:product_id',
            'requested_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (empty($validated['product_id']) && empty($validated['custom_product_name'])) {
            return $this->fail($request, 'Please select a product or type the name of the drug you are requesting.');
        }

        $requisition = BranchRequisition::create([
            'tenant_id' => $user->tenant_id,
            'branch_id' => $branch->id,
            'product_id' => $validated['product_id'] ?? null,
            'custom_product_name' => $validated['custom_product_name'] ?? null,
            'requested_quantity' => $validated['requested_quantity'],
            'status' => BranchRequisition::STATUS_PENDING,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json($requisition, 201);
        }

        return back()->with('success', 'Stock requisition submitted to HQ successfully.');
    }

    /**
     * Create one BranchRequisition row per cart line in a single DB
     * transaction - either all items are recorded, or none are, so a
     * failure partway through never leaves HQ with a half-submitted batch.
     */
    private function storeBulk(Request $request, $user, $branch): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.requested_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $requisitions = DB::transaction(function () use ($validated, $user, $branch) {
            return collect($validated['items'])->map(fn (array $item) => BranchRequisition::create([
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branch->id,
                'product_id' => $item['product_id'],
                'custom_product_name' => null,
                'requested_quantity' => $item['requested_quantity'],
                'status' => BranchRequisition::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]));
        });

        return response()->json([
            'message' => $requisitions->count() . ' item(s) requested from HQ successfully.',
            'requisitions' => $requisitions,
        ], 201);
    }

    /**
     * Approve a pending requisition: marks it approved and automatically
     * instantiates a matching pending StockTransferOrder from the tenant's
     * Central (HQ Main) warehouse to the requesting branch's Local
     * Backroom warehouse. No stock moves yet - that still only happens
     * when the branch worker receives the resulting order (see
     * StockTransferOrderService::receiveOrder()).
     *
     * Requires the requisition to reference an existing catalog product;
     * a custom_product_name request for a drug not yet in the system must
     * be added to the catalog first before it can be approved.
     */
    public function approve(Request $request, BranchRequisition $branchRequisition): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-branch-requisitions');
        $this->authorizeTenant($request, $branchRequisition);

        if (! $branchRequisition->isPending()) {
            return $this->fail($request, 'Only a pending requisition can be approved.');
        }

        if (! $branchRequisition->product_id) {
            return $this->fail($request, "\"{$branchRequisition->custom_product_name}\" is not yet in the product catalog. Add it as a product first, then approve the requisition against it.");
        }

        try {
            $central = Warehouse::getOrCreateTenantCentralWarehouse($branchRequisition->tenant_id);
            $backroom = Warehouse::getOrCreateSystemWarehouse(
                $branchRequisition->tenant_id,
                $branchRequisition->branch_id,
                Warehouse::TYPE_MAIN
            );

            $order = $this->stockTransferOrderService->createOrder(
                $central->id,
                $backroom->id,
                [[
                    'product_id' => $branchRequisition->product_id,
                    'batch_id' => null,
                    'quantity' => $branchRequisition->requested_quantity,
                ]]
            );
        } catch (\Exception $e) {
            return $this->fail($request, $e->getMessage());
        }

        $branchRequisition->update([
            'status' => BranchRequisition::STATUS_APPROVED,
            'reviewed_by' => $request->user()->id,
            'stock_transfer_order_id' => $order->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json($branchRequisition->fresh(['stockTransferOrder']));
        }

        return back()->with('success', "Requisition approved - Stock Transfer Order #{$order->id} created.");
    }

    /**
     * Reject a pending requisition. No stock transfer order is created.
     */
    public function reject(Request $request, BranchRequisition $branchRequisition): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-branch-requisitions');
        $this->authorizeTenant($request, $branchRequisition);

        if (! $branchRequisition->isPending()) {
            return $this->fail($request, 'Only a pending requisition can be rejected.');
        }

        $branchRequisition->update([
            'status' => BranchRequisition::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json($branchRequisition->fresh());
        }

        return back()->with('success', 'Requisition rejected.');
    }

    private function authorizeTenant(Request $request, BranchRequisition $requisition): void
    {
        if ($requisition->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized access to this requisition.');
        }
    }

    private function fail(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['error' => $message])->withInput();
    }
}

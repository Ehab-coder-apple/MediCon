<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Batch;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use DB;

class SalesReturnController extends Controller
{
    /**
     * Display a listing of sales returns
     */
    public function index(): View
    {
        // Determine tenant_id: use user's tenant_id, fallback to current_tenant from container
        $tenantId = auth()->user()->tenant_id;
        if (!$tenantId && app()->bound('current_tenant')) {
            $currentTenant = app('current_tenant');
            $tenantId = $currentTenant?->id;
        }

        $returns = SalesReturn::with(['invoice', 'customer', 'user'])
            ->where('tenant_id', $tenantId)
            ->orderBy('return_date', 'desc')
            ->paginate(15);

        return view('sales.returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new sales return
     */
    public function create(Invoice $invoice): View
    {
        // Verify invoice is sent/completed or payment is paid
        if (!in_array($invoice->status, ['sent', 'completed']) && $invoice->payment_status !== 'paid') {
            abort(403, 'Only completed or paid invoices can be returned.');
        }

        // Get invoice items with batch information
        $invoiceItems = $invoice->items()->with(['product', 'batch'])->get();

        return view('sales.returns.create', compact('invoice', 'invoiceItems'));
    }

    /**
     * Store a newly created sales return in storage
     */
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        // Verify invoice is sent/completed or payment is paid
        if (!in_array($invoice->status, ['sent', 'completed']) && $invoice->payment_status !== 'paid') {
            return back()->withErrors(['error' => 'Only completed or paid invoices can be returned.']);
        }

        $validated = $request->validate([
            'return_date' => 'required|date|before_or_equal:today',
            'refund_method' => 'required|string|in:cash,credit,store_credit',
            'items' => 'required|array|min:1',
            'items.*.invoice_item_id' => 'required|exists:invoice_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.reason' => 'required|string|in:damaged,expired,wrong_item,customer_changed_mind,defective,other',
            'items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Determine tenant_id
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId && app()->bound('current_tenant')) {
                $currentTenant = app('current_tenant');
                $tenantId = $currentTenant?->id;
            }

            if (!$tenantId) {
                throw new \Exception('Unable to determine tenant context for sales return');
            }

            // Create sales return
            $salesReturn = SalesReturn::create([
                'tenant_id' => $tenantId,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'user_id' => auth()->id(),
                'reference_number' => SalesReturn::generateReferenceNumber(),
                'return_date' => $validated['return_date'],
                'status' => 'pending',
                'refund_method' => $validated['refund_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Process each return item
            foreach ($validated['items'] as $itemData) {
                $invoiceItem = $invoice->items()->findOrFail($itemData['invoice_item_id']);
                $batch = Batch::findOrFail($itemData['batch_id']);

                // Validate quantity doesn't exceed invoice quantity
                if ($itemData['quantity'] > $invoiceItem->quantity) {
                    throw new \Exception("Return quantity cannot exceed invoice quantity for {$invoiceItem->product_name}");
                }

                // Create return item
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $invoiceItem->product_id,
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $invoiceItem->unit_price,
                    'total_price' => $itemData['quantity'] * $invoiceItem->unit_price,
                    'reason' => $itemData['reason'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Update totals
            $salesReturn->updateTotals();

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . 'sales-returns.show', $salesReturn)
                ->with('success', 'Return request created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sales return creation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to create return request: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified sales return
     */
    public function show(SalesReturn $salesReturn): View
    {
        $salesReturn->load(['invoice', 'customer', 'user', 'items.product', 'items.batch']);
        return view('sales.returns.show', compact('salesReturn'));
    }

    /**
     * Delete a sales return (only pending)
     */
    public function destroy(SalesReturn $salesReturn): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Only allow deletion of pending sales returns
            if ($salesReturn->status !== 'pending') {
                return back()->withErrors(['error' => 'Only pending sales returns can be deleted.']);
            }

            // Delete all associated items
            SalesReturnItem::where('sales_return_id', $salesReturn->id)->delete();

            // Delete the sales return
            $salesReturn->delete();

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . 'sales-returns.index')
                ->with('success', 'Sales return deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete sales return: ' . $e->getMessage()]);
        }
    }

    /**
     * Get the route prefix based on user role
     */
    private function getRoutePrefix(): string
    {
        if (auth()->user()->hasRole('admin')) {
            return 'admin.';
        } elseif (auth()->user()->hasRole('pharmacist')) {
            return 'pharmacist.';
        }
        return '';
    }
}


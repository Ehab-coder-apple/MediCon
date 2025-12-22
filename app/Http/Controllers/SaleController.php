<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tenant;
use App\Services\DatabaseTransactionService;
use App\Services\LoggingService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;
use App\Traits\HasRoleBasedRouting;

class SaleController extends Controller
{
    use HasRoleBasedRouting;
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $sales = Sale::with(['customer', 'user'])
            ->withCount('saleItems')
            ->latest('sale_date')
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)->orderBy('expiry_date');
            }])
            ->orderBy('name')
            ->get();
        $invoiceNumber = Sale::generateInvoiceNumber();

        return view('sales.create', compact('customers', 'products', 'invoiceNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Prepare sale data
            $saleData = [
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'sale_date' => $validated['sale_date'] ?? now(),
                'invoice_number' => $this->generateInvoiceNumber(),
                'payment_method' => $validated['payment_method'],
                'paid_amount' => $validated['paid_amount'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'notes' => $validated['notes'],
                'status' => 'completed',
            ];

            // Handle customer creation if needed
            if (!$saleData['customer_id'] && !empty($validated['customer_name'])) {
                $customer = Customer::create([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'] ?? null,
                    'email' => $validated['customer_email'] ?? null,
                ]);
                $saleData['customer_id'] = $customer->id;
            }

            // Execute sale transaction with proper error handling
            $result = DatabaseTransactionService::executeSaleTransaction($saleData, $validated['items']);

            if (!$result['success']) {
                LoggingService::logSystemError(new Exception($result['message']), 'sale_creation_failed');

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => $result['message']]);
            }

            // Log successful sale
            LoggingService::logUserActivity('sale_created', [
                'sale_id' => $result['data']->id,
                'invoice_number' => $result['data']->invoice_number,
                'total_price' => $result['data']->total_price,
                'items_count' => count($validated['items'])
            ]);

            // Log to activity log
            ActivityLogService::logSale('created', $result['data']);

            // Create corresponding Invoice record
            $this->createInvoiceFromSale($result['data']);

            return $this->redirectToIndex('sales', 'Sale completed successfully. Invoice: ' . $result['data']->invoice_number);

        } catch (Exception $e) {
            LoggingService::logSystemError($e, 'sale_controller_store');

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while processing the sale. Please try again.']);
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        return "INV-{$date}-{$sequence}";
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale): View
    {
        $sale->load(['customer', 'user', 'saleItems.product', 'saleItems.batch']);

        return view('sales.invoice', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale): View|RedirectResponse
    {
        // Only allow editing of pending sales
        if ($sale->status !== 'pending') {
            return $this->redirectToIndex('sales', 'Only pending sales can be edited.', 'error');
        }

        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)->orderBy('expiry_date');
            }])
            ->orderBy('name')
            ->get();

        $sale->load('saleItems.product');

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale): RedirectResponse
    {
        // Only allow updating of pending sales
        if ($sale->status !== 'pending') {
            return $this->redirectToIndex('sales', 'Only pending sales can be updated.', 'error');
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'sale_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,insurance,mixed',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        // Track changes for activity log
        $changes = [];
        foreach ($validated as $key => $value) {
            if ($sale->$key != $value) {
                $changes[$key] = ['old' => $sale->$key, 'new' => $value];
            }
        }

        $sale->update($validated);

        // Log to activity log
        if (!empty($changes)) {
            ActivityLogService::logSale('updated', $sale, $changes);
        }

        return $this->redirectToIndex('sales', 'Sale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale): RedirectResponse
    {
        // Only allow deletion of pending sales
        if ($sale->status !== 'pending') {
            return $this->redirectToIndex('sales', 'Only pending sales can be deleted.', 'error');
        }

        // Log to activity log before deletion
        ActivityLogService::logSale('deleted', $sale);

        $sale->delete();

        return $this->redirectToIndex('sales', 'Sale deleted successfully.');
    }

    /**
     * Get product details for barcode/search lookup
     */
    public function getProductDetails(Request $request)
    {
        $query = $request->get('query');

        if (!$query) {
            return response()->json(['products' => []]);
        }

        $products = Product::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('code', 'LIKE', "%{$query}%")
                  ->orWhere('barcode', 'LIKE', "%{$query}%")
                  ->orWhere('code', '=', $query) // Exact match for code
                  ->orWhere('barcode', '=', $query); // Exact match for barcode
            })
            ->with(['batches' => function($batchQuery) {
                $batchQuery->where('quantity', '>', 0)->orderBy('expiry_date');
            }])
            ->limit(10)
            ->get();

        return response()->json([
            'products' => $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'barcode' => $product->barcode,
                    'selling_price' => $product->selling_price,
                    // POS should only consider stock that is physically on shelf (sellable warehouses)
                    'available_quantity' => $product->on_shelf_quantity,
                    // Optional: expose total active quantity for informational purposes
                    'total_quantity' => $product->active_quantity,
                    'batches' => $product->batches->map(function($batch) {
                        return [
                            'id' => $batch->id,
                            'batch_number' => $batch->batch_number,
                            'quantity' => $batch->quantity,
                            'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                            'status' => $batch->status,
                        ];
                    }),
                ];
            })
        ]);
    }

    /**
     * Generate invoice/receipt for a sale
     */
    public function invoice(Sale $sale): View
    {
        $sale->load(['customer', 'user', 'saleItems.product', 'saleItems.batch']);

        return view('sales.invoice', compact('sale'));
    }

    /**
     * Get today's sales summary
     */
    public function todaySummary()
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, use the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                return response()->json([
                    'total_sales' => 0,
                    'total_amount' => 0,
                    'total_items' => 0,
                    'total_customers' => 0,
                ]);
            }
            $tenantId = $tenant->id;
        }

        // Get today's invoices (new sales method)
        $todayInvoices = Invoice::where('tenant_id', $tenantId)
            ->whereDate('invoice_date', today())
            ->where('status', '!=', 'draft')
            ->get();

        $totalSales = $todayInvoices->count();
        $totalAmount = $todayInvoices->sum('total_amount');
        $totalItems = $todayInvoices->flatMap(function($invoice) {
            return $invoice->items;
        })->sum('quantity');

        // Get unique customers from today's invoices
        $invoiceCustomerIds = $todayInvoices->pluck('customer_id')->filter()->unique();

        // Also include old Sale records for backward compatibility
        $todaySales = Sale::where('tenant_id', $tenantId)
            ->today()
            ->completed()
            ->get();

        $totalSales += $todaySales->count();
        $totalAmount += $todaySales->sum('total_price');
        $totalItems += $todaySales->flatMap(function($sale) {
            return $sale->saleItems;
        })->sum('quantity');

        // Get unique customers from today's sales
        $saleCustomerIds = $todaySales->pluck('customer_id')->filter()->unique();

        // Combine and count unique customers
        $totalCustomers = $invoiceCustomerIds->merge($saleCustomerIds)->unique()->count();

        return response()->json([
            'total_sales' => $totalSales,
            'total_amount' => $totalAmount,
            'total_items' => $totalItems,
            'total_customers' => $totalCustomers,
        ]);
    }

    /**
     * Create an Invoice record from a completed Sale
     */
    private function createInvoiceFromSale(Sale $sale): void
    {
        try {
            $tenantId = auth()->user()->tenant_id;

            // Create invoice from sale
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'customer_id' => $sale->customer_id,
                'user_id' => $sale->user_id,
                'invoice_number' => $sale->invoice_number,
                'invoice_date' => $sale->sale_date,
                'due_date' => now()->addDays(30),
                'subtotal' => $sale->subtotal,
                'discount_amount' => $sale->discount_amount,
                'tax_amount' => $sale->tax_amount,
                'total_amount' => $sale->total_price,
                'paid_amount' => $sale->paid_amount,
                'balance_due' => $sale->total_price - $sale->paid_amount,
                'payment_method' => $sale->payment_method,
                'status' => 'sent',
                'payment_status' => ($sale->paid_amount >= $sale->total_price) ? 'paid' : 'unpaid',
                'type' => 'sale',
                'notes' => $sale->notes,
            ]);

            // Create invoice items from sale items
            foreach ($sale->saleItems as $saleItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $saleItem->product_id,
                    'batch_id' => $saleItem->batch_id,
                    'product_name' => $saleItem->product->name,
                    'product_code' => $saleItem->product->code,
                    'batch_number' => $saleItem->batch->batch_number ?? null,
                    'expiry_date' => $saleItem->batch->expiry_date ?? null,
                    'quantity' => $saleItem->quantity,
                    'unit_price' => $saleItem->unit_price,
                    'total_price' => $saleItem->total_price,
                ]);
            }

            LoggingService::logUserActivity('invoice_created_from_sale', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'sale_id' => $sale->id,
            ]);

        } catch (Exception $e) {
            LoggingService::logSystemError($e, 'create_invoice_from_sale_failed');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Tenant;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use App\Services\ProductDisplayService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Access denied: User not authenticated');
        }

        // Get tenant ID - either from user's tenant_id or from current_tenant context (for super admins)
        $tenantId = $user->tenant_id;

        if (!$tenantId && app()->bound('current_tenant')) {
            $tenantId = app('current_tenant')->id;
        }

        // If user has no tenant_id, try to get the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        $query = Invoice::forTenant($tenantId)
            ->with(['customer', 'user', 'items'])
            ->latest('invoice_date');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20);

        // Get filter options
        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new invoice (POS interface)
     */
    public function create(): View
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Access denied: User not authenticated');
        }

        // If user has no tenant_id, try to get the first tenant
        $tenantId = $user->tenant_id;
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                abort(403, 'Access denied: No active tenant found');
            }
            $tenantId = $tenant->id;
        }

        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        // Use ProductDisplayService to get products based on configured strategy
        $displayService = new ProductDisplayService();
        $products = $displayService->getDisplayProducts($tenantId);

        return view('invoices.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        // If user has no tenant_id, use the first active tenant
        if (!$tenantId) {
            $tenant = Tenant::where('is_active', true)->first();
            if (!$tenant) {
                return back()->withErrors(['error' => 'No active tenant found']);
            }
            $tenantId = $tenant->id;
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($tenantId) {
                    if ($value) {
                        $customer = Customer::where('id', $value)
                            ->where('tenant_id', $tenantId)
                            ->first();
                        if (!$customer) {
                            $fail('The selected customer is invalid or does not belong to your organization.');
                        }
                    }
                }
            ],
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                function ($attribute, $value, $fail) use ($tenantId) {
                    $product = Product::where('id', $value)
                        ->where('tenant_id', $tenantId)
                        ->first();
                    if (!$product) {
                        $fail('One or more selected products are invalid or do not belong to your organization.');
                    }
                }
            ],
            'items.*.batch_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($tenantId) {
                    if ($value) {
                        $batch = Batch::where('id', $value)
                            ->where('tenant_id', $tenantId)
                            ->first();
                        if (!$batch) {
                            $fail('One or more selected batches are invalid or do not belong to your organization.');
                        }
                    }
                }
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,insurance,credit,mixed',
            'paid_amount' => 'nullable|numeric|min:0',
            'delivery_method' => 'required|in:pickup,delivery,shipping',
            'delivery_address' => 'required_if:delivery_method,delivery,shipping',
            'delivery_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Create invoice with initial total_amount of 0 (will be calculated after items are added)
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'invoice_number' => Invoice::generateInvoiceNumber($tenantId),
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_percentage' => $request->discount_percentage ?? 0,
                'tax_amount' => 0,
                'tax_percentage' => $request->tax_percentage ?? 0,
                'total_amount' => 0, // Will be calculated after items are added
                'paid_amount' => $request->paid_amount ?? 0,
                'balance_due' => 0,
                'payment_method' => $request->payment_method,
                'delivery_method' => $request->delivery_method,
                'delivery_address' => $request->delivery_address,
                'delivery_fee' => $request->delivery_fee ?? 0,
                'notes' => $request->notes,
                'status' => 'draft',
                'type' => 'sale',
            ]);

            // Store customer details at time of invoice
            if ($invoice->customer) {
                $invoice->customer_details = [
                    'name' => $invoice->customer->name,
                    'phone' => $invoice->customer->phone,
                    'email' => $invoice->customer->email,
                    'address' => $invoice->customer->address ?? $request->delivery_address,
                ];
                $invoice->save();
            }

            $subtotal = 0;

            // Create invoice items and update inventory
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $batch = isset($itemData['batch_id']) ? Batch::findOrFail($itemData['batch_id']) : null;

                // Check inventory availability
                if ($batch) {
                    if ($batch->quantity < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}. Available: {$batch->quantity}");
                    }
                } else {
                    if ($product->active_quantity < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->active_quantity}");
                    }
                }

                // Calculate item totals before creating
                $itemQuantity = $itemData['quantity'];
                $itemUnitPrice = $itemData['unit_price'];
                $itemSubtotal = $itemQuantity * $itemUnitPrice;
                $itemTotalPrice = $itemSubtotal; // For now, no discount/tax at item level

                // Create invoice item with calculated total_price
                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch?->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'product_description' => $product->description,
                    'quantity' => $itemQuantity,
                    'unit_price' => $itemUnitPrice,
                    'total_price' => $itemTotalPrice,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'batch_number' => $batch?->batch_number,
                    'expiry_date' => $batch?->expiry_date,
                ]);

                $subtotal += $itemTotalPrice;

                // Update inventory
                if ($batch) {
                    $batch->decrement('quantity', $itemData['quantity']);
                } else {
                    // Use FIFO - deduct from oldest batches first
                    $remainingQuantity = $itemData['quantity'];
                    $availableBatches = $product->activeBatches()->orderBy('expiry_date')->get();

                    foreach ($availableBatches as $availableBatch) {
                        if ($remainingQuantity <= 0) break;

                        $deductQuantity = min($remainingQuantity, $availableBatch->quantity);
                        $availableBatch->decrement('quantity', $deductQuantity);
                        $remainingQuantity -= $deductQuantity;
                    }
                }
            }

            // Calculate invoice totals
            $invoice->subtotal = $subtotal;
            $discountAmount = $invoice->discount_percentage > 0
                ? ($subtotal * $invoice->discount_percentage / 100)
                : $invoice->discount_amount;
            $invoice->discount_amount = $discountAmount;

            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = $invoice->tax_percentage > 0
                ? ($afterDiscount * $invoice->tax_percentage / 100)
                : 0;
            $invoice->tax_amount = $taxAmount;

            $invoice->total_amount = $afterDiscount + $taxAmount + $invoice->delivery_fee;
            $invoice->balance_due = $invoice->total_amount - $invoice->paid_amount;

            // Update payment status
            $invoice->updatePaymentStatus();

            // If fully paid, mark as completed
            if ($invoice->payment_status === 'paid') {
                $invoice->status = 'paid';
            }

            $invoice->save();

            DB::commit();

            // Check if this is a "hold" action
            if ($request->input('action') === 'hold') {
                return redirect()->route('invoices.index')
                    ->with('success', 'Invoice saved and held successfully!');
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice): View
    {
        // Ensure invoice belongs to current tenant
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $invoice->load(['customer', 'user', 'items.product', 'items.batch']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Invoice $invoice): View
    {
        // Ensure invoice belongs to current tenant
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        // Only allow editing of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $tenantId = auth()->user()->tenant_id;

        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['batches' => function($query) {
                $query->where('quantity', '>', 0)
                      ->where('expiry_date', '>', now())
                      ->orderBy('expiry_date');
            }])
            ->orderBy('name')
            ->get();

        $invoice->load(['items.product', 'items.batch']);

        return view('invoices.edit', compact('invoice', 'customers', 'products'));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        // Ensure invoice belongs to current tenant
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        // Only allow updating of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be updated.');
        }

        $tenantId = auth()->user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'customer_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($tenantId) {
                    if ($value) {
                        $customer = Customer::where('id', $value)
                            ->where('tenant_id', $tenantId)
                            ->first();
                        if (!$customer) {
                            $fail('The selected customer is invalid or does not belong to your organization.');
                        }
                    }
                }
            ],
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,insurance,credit,mixed',
            'paid_amount' => 'nullable|numeric|min:0',
            'delivery_method' => 'required|in:pickup,delivery,shipping',
            'delivery_address' => 'required_if:delivery_method,delivery,shipping',
            'delivery_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $invoice->update([
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'discount_amount' => $request->discount_amount ?? 0,
                'discount_percentage' => $request->discount_percentage ?? 0,
                'tax_percentage' => $request->tax_percentage ?? 0,
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount ?? 0,
                'delivery_method' => $request->delivery_method,
                'delivery_address' => $request->delivery_address,
                'delivery_fee' => $request->delivery_fee ?? 0,
                'notes' => $request->notes,
            ]);

            // Update customer details if customer changed
            if ($invoice->customer) {
                $invoice->customer_details = [
                    'name' => $invoice->customer->name,
                    'phone' => $invoice->customer->phone,
                    'email' => $invoice->customer->email,
                    'address' => $invoice->customer->address ?? $request->delivery_address,
                ];
                $invoice->save();
            }

            // Recalculate totals
            $invoice->calculateTotals();
            $invoice->updatePaymentStatus();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully!');

        } catch (\Exception $e) {
            Log::error('Invoice update failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        // Ensure invoice belongs to current tenant
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        // Only allow deletion of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                ->with('error', 'Only draft invoices can be deleted.');
        }

        try {
            DB::beginTransaction();

            // Restore inventory for each item
            foreach ($invoice->items as $item) {
                if ($item->batch_id) {
                    $batch = Batch::find($item->batch_id);
                    if ($batch) {
                        $batch->increment('quantity', $item->quantity);
                    }
                } else {
                    // For items without specific batch, add back to the oldest available batch
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $oldestBatch = $product->batches()
                            ->where('expiry_date', '>', now())
                            ->orderBy('expiry_date')
                            ->first();

                        if ($oldestBatch) {
                            $oldestBatch->increment('quantity', $item->quantity);
                        }
                    }
                }
            }

            $invoice->delete();

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice deletion failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('invoices.index')
                ->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    /**
     * Process payment for an invoice
     */
    public function processPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        // Ensure invoice belongs to current tenant
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,insurance,credit',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $paymentAmount = $request->payment_amount;
            $newPaidAmount = $invoice->paid_amount + $paymentAmount;

            // Prevent overpayment
            if ($newPaidAmount > $invoice->total_amount) {
                return back()->withErrors(['payment_amount' => 'Payment amount exceeds the balance due.']);
            }

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
            ]);

            $invoice->updatePaymentStatus();

            // If fully paid, update status
            if ($invoice->payment_status === 'paid') {
                $invoice->update(['status' => 'paid']);
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Payment processed successfully!');

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->withErrors(['error' => 'Failed to process payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Get product details for AJAX requests
     */
    public function getProduct(Product $product): JsonResponse
    {
        // Ensure product belongs to current tenant
        if ($product->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $product->load(['batches' => function($query) {
            $query->where('quantity', '>', 0)
                  ->where('expiry_date', '>', now())
                  ->orderBy('expiry_date');
        }]);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'description' => $product->description,
            'selling_price' => $product->selling_price,
            'active_quantity' => $product->active_quantity,
            'batches' => $product->batches->map(function($batch) {
                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $batch->quantity,
                    'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                    'formatted_expiry_date' => $batch->expiry_date->format('M d, Y'),
                ];
            }),
        ]);
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(Invoice $invoice): RedirectResponse
    {
        // Ensure invoice belongs to current tenant
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $invoice->update(['status' => 'sent']);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice marked as sent!');
    }

    /**
     * Cancel an invoice
     */
    public function cancel(Invoice $invoice): RedirectResponse
    {
        // Ensure invoice belongs to current tenant
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        // Only allow cancellation of unpaid invoices
        if ($invoice->payment_status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot cancel a paid invoice.');
        }

        try {
            DB::beginTransaction();

            // Restore inventory
            foreach ($invoice->items as $item) {
                if ($item->batch_id) {
                    $batch = Batch::find($item->batch_id);
                    if ($batch) {
                        $batch->increment('quantity', $item->quantity);
                    }
                }
            }

            $invoice->update(['status' => 'cancelled']);

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice cancelled successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice cancellation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Failed to cancel invoice: ' . $e->getMessage());
        }
    }

    /**
     * Send invoice via WhatsApp
     */
    public function sendViaWhatsApp(Invoice $invoice, WhatsAppService $whatsAppService): RedirectResponse
    {
        try {
            // Ensure invoice belongs to current tenant
            if ($invoice->tenant_id !== auth()->user()->tenant_id) {
                abort(403);
            }

            // Validate customer has phone number
            if (!$invoice->customer || !$invoice->customer->phone) {
                return redirect()->route('invoices.show', $invoice)
                    ->with('error', 'Customer does not have a valid phone number.');
            }

            // Validate WhatsApp service is enabled
            if (!$whatsAppService->isEnabled()) {
                return redirect()->route('invoices.show', $invoice)
                    ->with('error', 'WhatsApp service is not configured. Please configure it in settings.');
            }

            // Generate PDF invoice
            $invoice->load(['customer', 'user', 'items.product', 'items.batch']);

            $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
                ->setPaper('a4')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            // Store PDF temporarily
            $fileName = 'invoice_' . $invoice->invoice_number . '_' . time() . '.pdf';
            $filePath = 'temp/' . $fileName;
            Storage::disk('local')->put($filePath, $pdf->output());

            // Get full path for WhatsApp
            $pdfUrl = Storage::disk('local')->url($filePath);

            // Send via WhatsApp
            $message = "Hello {$invoice->customer->name},\n\n";
            $message .= "Please find attached your invoice #{$invoice->invoice_number}.\n";
            $message .= "Invoice Date: " . $invoice->invoice_date->format('M d, Y') . "\n";
            $message .= "Total Amount: $" . number_format($invoice->total_amount, 2) . "\n";

            if ($invoice->balance_due > 0) {
                $message .= "Balance Due: $" . number_format($invoice->balance_due, 2) . "\n";
            }

            $message .= "\nThank you for your business!";

            // Send text message first
            $result = $whatsAppService->sendTextMessage(
                $invoice->customer->phone,
                $message
            );

            if (!$result['success']) {
                return redirect()->route('invoices.show', $invoice)
                    ->with('error', 'Failed to send WhatsApp message: ' . ($result['error'] ?? 'Unknown error'));
            }

            // Record the message
            WhatsAppMessage::create([
                'tenant_id' => $invoice->tenant_id,
                'user_id' => auth()->id(),
                'customer_id' => $invoice->customer_id,
                'recipient_phone' => $invoice->customer->phone,
                'message_type' => 'text',
                'message_content' => $message,
                'status' => WhatsAppMessage::STATUS_SENT,
                'whatsapp_message_id' => $result['message_id'] ?? null,
                'sent_at' => now(),
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => 'invoice_notification'
                ]
            ]);

            // Clean up temporary file
            Storage::disk('local')->delete($filePath);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice sent successfully to ' . $invoice->customer->name . ' via WhatsApp!');

        } catch (\Exception $e) {
            Log::error('Failed to send invoice via WhatsApp', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Failed to send invoice via WhatsApp: ' . $e->getMessage());
        }
    }
}

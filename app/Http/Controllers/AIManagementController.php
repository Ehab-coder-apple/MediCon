<?php

namespace App\Http\Controllers;

use App\Models\AIDocument;
use App\Models\ProcessedInvoice;
use App\Models\PrescriptionCheck;
use App\Models\Product;
use App\Models\ProductInformation;
use App\Models\Warehouse;
use App\Services\OpenAIProductService;
use App\Services\InvoicePDFUploadService;
use App\Services\InvoiceItemExtractionService;
use App\Services\WarehouseTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIManagementController extends Controller
{
    /**
     * Show AI dashboard
     */
    public function dashboard()
    {
        $total_documents = AIDocument::count();
        $pending_documents = AIDocument::where('status', 'pending')->count();
        $processed_invoices = ProcessedInvoice::count();
        $pending_invoices = ProcessedInvoice::where('status', 'pending_review')->count();
        $prescription_checks = PrescriptionCheck::count();

        return view('admin.ai.dashboard', compact(
            'total_documents',
            'pending_documents',
            'processed_invoices',
            'pending_invoices',
            'prescription_checks'
        ));
    }

    /**
     * Show invoice processing page
     */
    public function invoices(Request $request)
    {
        $query = ProcessedInvoice::with('aiDocument', 'items');

        // Filter by search term (invoice number)
        if ($request->has('search') && $request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        // Filter by workflow stage
        if ($request->has('workflow_stage') && $request->workflow_stage) {
            $query->where('workflow_stage', $request->workflow_stage);
        }

        $invoices = $query->latest()->paginate(15);

        return view('admin.ai.invoices.index', compact('invoices'));
    }

    /**
     * Show invoice details
     */
    public function showInvoice($id)
    {
        $invoice = ProcessedInvoice::with('items', 'aiDocument', 'approvedForProcessingBy', 'approvedForInventoryBy')->findOrFail($id);
        return view('admin.ai.invoices.show', compact('invoice'));
    }

    /**
     * Stream the original uploaded AI document for an invoice.
     */
    public function viewOriginalDocument($id)
    {
        $invoice = ProcessedInvoice::with('aiDocument')->findOrFail($id);

        if (!$invoice->aiDocument || !$invoice->aiDocument->file_path) {
            abort(404, 'Original invoice document not found');
        }

        $path = $invoice->aiDocument->file_path;

        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'Original invoice file is missing from storage');
        }

        $fullPath = Storage::disk('private')->path($path);
        $mimeType = $invoice->aiDocument->mime_type ?: 'application/pdf';
        $fileName = $invoice->aiDocument->file_name ?: basename($path);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Approve invoice for OCR processing (First Approval)
     */
    public function approveForProcessing(Request $request, $id)
    {
        $invoice = ProcessedInvoice::findOrFail($id);

        if ($invoice->workflow_stage !== 'uploaded') {
            return redirect()->back()->with('error', 'Invoice is not in uploaded stage');
        }

        $invoice->update([
            'workflow_stage' => 'approved_for_processing',
            'approved_for_processing_by' => auth()->id(),
            'approved_for_processing_at' => now(),
        ]);

        // Trigger OCR processing
        $this->processInvoiceOCR($invoice);

        return redirect()->back()->with('success', 'Invoice approved for processing. OCR extraction started.');
    }

    /**
     * Approve invoice for inventory upload (Second Approval)
     */
    public function approveForInventory(Request $request, $id)
    {
        $invoice = ProcessedInvoice::findOrFail($id);

        if ($invoice->workflow_stage !== 'processed') {
            return redirect()->back()->with('error', 'Invoice is not in processed stage');
        }

        $invoice->update([
            'workflow_stage' => 'approved_for_inventory',
            'approved_for_inventory_by' => auth()->id(),
            'approved_for_inventory_at' => now(),
        ]);

        // Add items to inventory
        $itemsAdded = $this->addItemsToInventory($invoice);

        $invoice->update([
            'workflow_stage' => 'completed',
            'inventory_uploaded_at' => now(),
            'items_added_to_inventory' => $itemsAdded,
        ]);

        return redirect()->back()->with('success', "Invoice completed. $itemsAdded items added to inventory.");
    }

    /**
     * Process invoice with OCR
     */
    private function processInvoiceOCR($invoice)
    {
        $invoice->update([
            'workflow_stage' => 'processing',
            'processing_started_at' => now(),
        ]);

        // TODO: Call OCR service to extract data
        // For now, this is a placeholder

        $invoice->update([
            'workflow_stage' => 'processed',
            'processing_completed_at' => now(),
        ]);
    }

    /**
     * Add invoice items to inventory
     */
    private function addItemsToInventory($invoice)
    {
        $itemsAdded = 0;

        foreach ($invoice->items as $item) {
            // TODO: Add item to inventory/stock
            // This would typically create a stock receiving record or update product stock
            $itemsAdded++;
        }

        return $itemsAdded;
    }

    /**
     * Show prescription checks page
     */
    public function prescriptions()
    {
        $checks = PrescriptionCheck::with('medications')
            ->latest()
            ->paginate(15);

        return view('admin.ai.prescriptions.index', compact('checks'));
    }

    /**
     * Show prescription details
     */
    public function showPrescription($id)
    {
        $check = PrescriptionCheck::with('medications.product', 'medications.alternatives')
            ->findOrFail($id);

        return view('admin.ai.prescriptions.show', compact('check'));
    }

    /**
     * Show product information page
     */
    public function products()
    {
        $products = Product::with('information')
            ->where('is_active', true)
            ->paginate(15);

        return view('admin.ai.products.index', compact('products'));
    }

    /**
     * Show product information details
     */
    public function showProduct($id, OpenAIProductService $openaiService)
    {
        $product = Product::with('information')->findOrFail($id);

        // Fetch medical information from OpenAI
        $medicalInfo = null;
        try {
            $medicalInfo = $openaiService->getProductInformation($product->name);
        } catch (\Exception $e) {
            Log::warning('Failed to fetch OpenAI product info for ' . $product->name . ': ' . $e->getMessage());
        }

        return view('admin.ai.products.show', compact('product', 'medicalInfo'));
    }

    /**
     * Edit product information
     */
    public function editProduct($id)
    {
        $product = Product::with('information')->findOrFail($id);
        return view('admin.ai.products.edit', compact('product'));
    }

    /**
     * Update product information
     */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'active_ingredients' => 'array',
            'side_effects' => 'array',
            'indications' => 'array',
            'dosage_information' => 'string',
            'contraindications' => 'array',
            'drug_interactions' => 'array',
            'storage_requirements' => 'array',
        ]);

        ProductInformation::updateOrCreate(
            ['product_id' => $id],
            array_merge($validated, [
                'last_updated_by' => auth()->id(),
                'source' => 'manual_entry',
            ])
        );

        return redirect()->route('admin.ai.products.show', $id)
            ->with('success', 'Product information updated successfully');
    }

    /**
     * Upload PDF for an invoice
     */
    public function uploadInvoicePDF(Request $request, $id)
    {
        $invoice = ProcessedInvoice::findOrFail($id);

        $validated = $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $service = new InvoicePDFUploadService();
        $result = $service->uploadPDF($invoice, $validated['pdf_file']);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->withErrors(['pdf_file' => $result['message']]);
    }

    /**
     * Convert invoice PDF to items
     */
    public function convertToItems(Request $request, $id)
    {
        $invoice = ProcessedInvoice::findOrFail($id);

        if (!$invoice->pdf_file_path) {
            return redirect()->back()->withErrors(['error' => 'No PDF file uploaded for this invoice']);
        }

        $service = new InvoiceItemExtractionService();
        $result = $service->extractItemsFromPDF($invoice);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Show warehouse selection for transfer
     */
    public function selectWarehouse(Request $request, $id)
    {
        $invoice = ProcessedInvoice::findOrFail($id);

        if (!$invoice->items || $invoice->items->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'No items to transfer']);
        }

        $warehouses = Warehouse::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_sellable', false)
            ->orderBy('name')
            ->get();

        return view('admin.ai.invoices.select-warehouse', compact('invoice', 'warehouses'));
    }

    /**
     * Approve warehouse transfer
     */
    public function approveWarehouseTransfer(Request $request, $id)
    {
        $invoice = ProcessedInvoice::findOrFail($id);

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        // Verify warehouse belongs to same tenant
        if ($warehouse->tenant_id !== auth()->user()->tenant_id) {
            return redirect()->back()->withErrors(['error' => 'Invalid warehouse selection']);
        }

        $service = new WarehouseTransferService();
        $result = $service->transferToWarehouse($invoice, $warehouse);

        if ($result['success']) {
            return redirect()->route('admin.ai.invoices.show', $invoice->id)
                ->with('success', $result['message']);
        }

        return redirect()->back()->withErrors(['error' => $result['message']]);
    }
}


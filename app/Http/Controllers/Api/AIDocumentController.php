<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AIDocument;
use App\Models\ProcessedInvoice;
use App\Models\PrescriptionCheck;
use App\Models\Warehouse;
use App\Services\AIDocumentProcessingService;
use App\Services\InvoicePDFUploadService;
use App\Services\InvoiceItemExtractionService;
use App\Services\WarehouseTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AIDocumentController extends Controller
{
    public function __construct(private AIDocumentProcessingService $processingService)
    {
    }

    /**
     * Upload and process a document
     */
    public function upload(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:invoice,prescription',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('ai-documents/' . $request->document_type, 'private');

        $document = AIDocument::create([
            'tenant_id' => auth()->user()->tenant_id,
            'branch_id' => $request->branch_id,
            'user_id' => auth()->id(),
            'document_type' => $request->document_type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'pending',
        ]);

        // Process document asynchronously
        $this->processingService->processDocument($document);

        return response()->json([
            'message' => 'Document uploaded and processing started',
            'document_id' => $document->id,
            'status' => $document->status,
        ]);
    }

    /**
     * Get document status
     */
    public function getStatus($documentId)
    {
        $document = AIDocument::findOrFail($documentId);
        $this->authorize('view', $document);

        return response()->json([
            'id' => $document->id,
            'status' => $document->status,
            'document_type' => $document->document_type,
            'processed_at' => $document->processed_at,
            'error' => $document->processing_error,
        ]);
    }

    /**
     * Get processed invoice details
     */
    public function getInvoice($invoiceId)
    {
        $invoice = ProcessedInvoice::with('items')->findOrFail($invoiceId);
        $this->authorize('view', $invoice);

        return response()->json($invoice);
    }

    /**
     * Approve processed invoice (Legacy - kept for backward compatibility)
     */
    public function approveInvoice(Request $request, $invoiceId)
    {
        $invoice = ProcessedInvoice::findOrFail($invoiceId);
        $this->authorize('update', $invoice);

        $invoice->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $request->notes,
        ]);

        return response()->json(['message' => 'Invoice approved']);
    }

    /**
     * Approve invoice for OCR processing (First Approval)
     */
    public function approveForProcessing(Request $request, $invoiceId)
    {
        $invoice = ProcessedInvoice::findOrFail($invoiceId);
        $this->authorize('update', $invoice);

        if ($invoice->workflow_stage !== 'uploaded') {
            return response()->json(['error' => 'Invoice is not in uploaded stage'], 422);
        }

        $invoice->update([
            'workflow_stage' => 'approved_for_processing',
            'approved_for_processing_by' => auth()->id(),
            'approved_for_processing_at' => now(),
        ]);

        // Trigger OCR processing
        $this->processingService->processDocument($invoice->aiDocument);

        return response()->json([
            'message' => 'Invoice approved for processing',
            'workflow_stage' => $invoice->workflow_stage,
        ]);
    }

    /**
     * Approve invoice for inventory upload (Second Approval)
     */
    public function approveForInventory(Request $request, $invoiceId)
    {
        $invoice = ProcessedInvoice::findOrFail($invoiceId);
        $this->authorize('update', $invoice);

        if ($invoice->workflow_stage !== 'processed') {
            return response()->json(['error' => 'Invoice is not in processed stage'], 422);
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

        return response()->json([
            'message' => 'Invoice approved for inventory',
            'workflow_stage' => $invoice->workflow_stage,
            'items_added' => $itemsAdded,
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
     * Get prescription check details
     */
    public function getPrescription($checkId)
    {
        $check = PrescriptionCheck::with('medications')->findOrFail($checkId);
        $this->authorize('view', $check);

        return response()->json($check);
    }

    /**
     * Upload PDF for an invoice (API endpoint)
     */
    public function uploadInvoicePDF(Request $request, $invoiceId)
    {
        $invoice = ProcessedInvoice::findOrFail($invoiceId);
        $this->authorize('update', $invoice);

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $service = new InvoicePDFUploadService();
        $result = $service->uploadPDF($invoice, $request->file('pdf_file'));

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'file_path' => $result['file_path'],
                'invoice_id' => $invoice->id,
            ]);
        }

        return response()->json(['error' => $result['message']], 422);
    }

    /**
     * Convert invoice PDF to items (API endpoint)
     */
    public function convertInvoiceToItems(Request $request, $invoiceId)
    {
        $invoice = ProcessedInvoice::findOrFail($invoiceId);
        $this->authorize('update', $invoice);

        if (!$invoice->pdf_file_path) {
            return response()->json(['error' => 'No PDF file uploaded for this invoice'], 422);
        }

        $service = new InvoiceItemExtractionService();
        $result = $service->extractItemsFromPDF($invoice);

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'items_count' => $result['items_count'],
                'extraction_status' => $invoice->extraction_status,
            ]);
        }

        return response()->json(['error' => $result['message']], 422);
    }

    /**
     * Transfer invoice items to warehouse (API endpoint)
     */
    public function transferToWarehouse(Request $request, $invoiceId)
    {
        $invoice = ProcessedInvoice::findOrFail($invoiceId);
        $this->authorize('update', $invoice);

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        // Verify warehouse belongs to same tenant
        if ($warehouse->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Invalid warehouse selection'], 422);
        }

        $service = new WarehouseTransferService();
        $result = $service->transferToWarehouse($invoice, $warehouse);

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'items_transferred' => $result['items_transferred'],
                'warehouse_id' => $warehouse->id,
                'transfer_status' => $invoice->transfer_status,
            ]);
        }

        return response()->json(['error' => $result['message']], 422);
    }

    /**
     * Get available warehouses for transfer (API endpoint)
     */
    public function getAvailableWarehouses(Request $request, $invoiceId)
    {
        $invoice = ProcessedInvoice::findOrFail($invoiceId);
        $this->authorize('view', $invoice);

        $warehouses = Warehouse::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_sellable', false)
            ->select('id', 'name', 'type', 'specifications')
            ->orderBy('name')
            ->get();

        return response()->json([
            'invoice_id' => $invoice->id,
            'warehouses' => $warehouses,
        ]);
    }
}


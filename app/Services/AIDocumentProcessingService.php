<?php

namespace App\Services;

use App\Models\AIDocument;
use App\Models\ProcessedInvoice;
use App\Models\ProcessedInvoiceItem;
use App\Models\PrescriptionCheck;
use App\Models\PrescriptionMedication;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class AIDocumentProcessingService
{
    /**
     * Process an uploaded document (PDF or image)
     */
    public function processDocument(AIDocument $document): void
    {
        try {
            $document->update(['status' => 'processing']);

            // Extract text from document using OCR
            $extractedText = $this->extractTextFromDocument($document);
            $document->update(['raw_text' => $extractedText]);

            // Parse extracted text based on document type
            if ($document->document_type === 'invoice') {
                $this->processInvoice($document, $extractedText);
            } elseif ($document->document_type === 'prescription') {
                $this->processPrescription($document, $extractedText);
            }

            $document->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $document->update([
                'status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Extract text from PDF or image using OCR
     */
    private function extractTextFromDocument(AIDocument $document): string
    {
        // This would integrate with Google Cloud Vision, AWS Textract, or similar
        // For now, returning placeholder
        return "OCR extraction would happen here using Google Cloud Vision API";
    }

    /**
     * Process invoice document
     */
    private function processInvoice(AIDocument $document, string $extractedText): void
    {
        // Parse invoice data from extracted text
        $invoiceData = $this->parseInvoiceData($extractedText);

        // Create processed invoice record with 'uploaded' workflow stage
        $invoice = ProcessedInvoice::create([
            'tenant_id' => $document->tenant_id,
            'branch_id' => $document->branch_id,
            'ai_document_id' => $document->id,
            'invoice_number' => $invoiceData['invoice_number'] ?? null,
            'invoice_date' => $invoiceData['invoice_date'] ?? null,
            'total_amount' => $invoiceData['total_amount'] ?? 0,
            'supplier_name' => $invoiceData['supplier_name'] ?? null,
            'status' => 'pending_review',
            'workflow_stage' => 'uploaded', // Start at uploaded stage for multi-step approval
        ]);

        // Create invoice items
        foreach ($invoiceData['items'] ?? [] as $item) {
            ProcessedInvoiceItem::create([
                'processed_invoice_id' => $invoice->id,
                'product_name' => $item['product_name'] ?? '',
                'quantity' => $item['quantity'] ?? 0,
                'unit_price' => $item['unit_price'] ?? 0,
                'total_price' => $item['total_price'] ?? 0,
                'confidence_score' => $item['confidence_score'] ?? 100,
            ]);
        }

        $document->update(['extracted_data' => $invoiceData]);
    }

    /**
     * Process prescription document
     */
    private function processPrescription(AIDocument $document, string $extractedText): void
    {
        // Parse prescription data
        $prescriptionData = $this->parsePrescriptionData($extractedText);

        // Create prescription check record
        $check = PrescriptionCheck::create([
            'tenant_id' => $document->tenant_id,
            'branch_id' => $document->branch_id,
            'ai_document_id' => $document->id,
            'user_id' => auth()->id(),
            'patient_name' => $prescriptionData['patient_name'] ?? null,
            'prescription_date' => $prescriptionData['prescription_date'] ?? null,
        ]);

        // Check each medication against inventory
        foreach ($prescriptionData['medications'] ?? [] as $med) {
            $this->checkMedicationAvailability($check, $med);
        }

        $document->update(['extracted_data' => $prescriptionData]);
    }

    /**
     * Parse invoice data from extracted text
     */
    private function parseInvoiceData(string $text): array
    {
        // This would use AI/regex to parse invoice structure
        return [
            'invoice_number' => null,
            'invoice_date' => null,
            'total_amount' => 0,
            'supplier_name' => null,
            'items' => [],
        ];
    }

    /**
     * Parse prescription data from extracted text
     */
    private function parsePrescriptionData(string $text): array
    {
        // This would use AI/regex to parse prescription structure
        return [
            'patient_name' => null,
            'prescription_date' => null,
            'medications' => [],
        ];
    }

    /**
     * Check medication availability in pharmacy inventory
     */
    private function checkMedicationAvailability(PrescriptionCheck $check, array $medication): void
    {
        // Find product by name or code
        $product = Product::where('name', 'like', '%' . $medication['name'] . '%')
            ->orWhere('code', $medication['code'] ?? '')
            ->first();

        $availableQuantity = 0;
        $status = 'out_of_stock';

        if ($product) {
            $availableQuantity = $product->active_quantity ?? 0;
            if ($availableQuantity >= ($medication['quantity'] ?? 0)) {
                $status = 'in_stock';
            } elseif ($availableQuantity > 0) {
                $status = 'low_stock';
            }
        }

        PrescriptionMedication::create([
            'prescription_check_id' => $check->id,
            'product_id' => $product?->id,
            'medication_name' => $medication['name'] ?? '',
            'dosage' => $medication['dosage'] ?? null,
            'quantity_prescribed' => $medication['quantity'] ?? 0,
            'availability_status' => $status,
            'available_quantity' => $availableQuantity,
        ]);
    }
}


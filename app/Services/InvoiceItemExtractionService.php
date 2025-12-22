<?php

namespace App\Services;

use App\Models\ProcessedInvoice;
use App\Models\ProcessedInvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class InvoiceItemExtractionService
{
    /**
     * Extract items from uploaded PDF
     * This is a placeholder that can be integrated with OCR services like Tesseract, AWS Textract, etc.
     */
    public function extractItemsFromPDF(ProcessedInvoice $invoice): array
    {
        try {
            if (!$invoice->pdf_file_path) {
                throw new Exception('No PDF file found for this invoice');
            }

            $invoice->update(['extraction_status' => 'in_progress']);

            // Get the PDF file path
            $filePath = Storage::disk('private')->path($invoice->pdf_file_path);

            // TODO: Implement actual PDF parsing/OCR
            // For now, this is a placeholder that returns empty items
            // In production, integrate with:
            // - Tesseract OCR
            // - AWS Textract
            // - Google Cloud Vision
            // - Azure Form Recognizer
            // - etc.

            $extractedItems = $this->parseInvoicePDF($filePath);

            // Create ProcessedInvoiceItem records from extracted data
            $itemsCreated = $this->createInvoiceItems($invoice, $extractedItems);

            $invoice->update([
                'extraction_status' => 'completed',
                'extraction_error' => null,
            ]);

            Log::info('Invoice items extracted', [
                'invoice_id' => $invoice->id,
                'items_count' => count($itemsCreated),
            ]);

            return [
                'success' => true,
                'message' => count($itemsCreated) . ' items extracted successfully',
                'items_count' => count($itemsCreated),
            ];
        } catch (Exception $e) {
            Log::error('Item extraction failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            $invoice->update([
                'extraction_status' => 'failed',
                'extraction_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to extract items: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse the PDF file and extract invoice items
     * This is a placeholder method
     */
    private function parseInvoicePDF(string $filePath): array
    {
        // TODO: Implement actual PDF parsing
        // This should extract:
        // - Product names
        // - Quantities
        // - Unit prices
        // - Total prices
        // - Batch numbers (if available)
        // - Expiry dates (if available)

        return [];
    }

    /**
     * Create ProcessedInvoiceItem records from extracted data
     */
    private function createInvoiceItems(ProcessedInvoice $invoice, array $extractedItems): array
    {
        $createdItems = [];

        foreach ($extractedItems as $itemData) {
            try {
                // Try to match product by name or code
                $product = $this->matchProduct($itemData);

                $item = ProcessedInvoiceItem::create([
                    'processed_invoice_id' => $invoice->id,
                    'product_id' => $product?->id,
                    'product_name' => $itemData['product_name'] ?? null,
                    'product_code' => $itemData['product_code'] ?? null,
                    'quantity' => $itemData['quantity'] ?? 0,
                    'unit_price' => $itemData['unit_price'] ?? 0,
                    'total_price' => $itemData['total_price'] ?? 0,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'manufacturer' => $itemData['manufacturer'] ?? null,
                    'confidence_score' => $itemData['confidence_score'] ?? 0,
                ]);

                $createdItems[] = $item;
            } catch (Exception $e) {
                Log::warning('Failed to create invoice item', [
                    'invoice_id' => $invoice->id,
                    'item_data' => $itemData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $createdItems;
    }

    /**
     * Try to match extracted product data with existing products
     */
    private function matchProduct(array $itemData): ?Product
    {
        if (isset($itemData['product_code'])) {
            return Product::where('code', $itemData['product_code'])->first();
        }

        if (isset($itemData['product_name'])) {
            return Product::where('name', 'like', '%' . $itemData['product_name'] . '%')->first();
        }

        return null;
    }
}


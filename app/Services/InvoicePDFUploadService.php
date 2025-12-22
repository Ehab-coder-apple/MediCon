<?php

namespace App\Services;

use App\Models\ProcessedInvoice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class InvoicePDFUploadService
{
    /**
     * Maximum file size in bytes (10MB)
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Allowed MIME types
     */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
    ];

    /**
     * Upload and store a PDF file for an invoice
     */
    public function uploadPDF(ProcessedInvoice $invoice, UploadedFile $file): array
    {
        try {
            // Validate file
            $this->validateFile($file);

            // Store the file
            $path = $this->storeFile($invoice, $file);

            // Update invoice with PDF information
            $invoice->update([
                'pdf_file_path' => $path,
                'pdf_file_name' => $file->getClientOriginalName(),
                'pdf_file_size' => $file->getSize(),
                'extraction_status' => 'pending',
            ]);

            Log::info('Invoice PDF uploaded', [
                'invoice_id' => $invoice->id,
                'file_path' => $path,
                'file_size' => $file->getSize(),
            ]);

            return [
                'success' => true,
                'message' => 'PDF uploaded successfully',
                'file_path' => $path,
            ];
        } catch (Exception $e) {
            Log::error('PDF upload failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate the uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed size of 10MB');
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new Exception('Invalid file type. Only PDF files are allowed');
        }

        // Additional validation: check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'pdf') {
            throw new Exception('Invalid file extension. Only .pdf files are allowed');
        }
    }

    /**
     * Store the file securely
     */
    private function storeFile(ProcessedInvoice $invoice, UploadedFile $file): string
    {
        $tenantId = $invoice->tenant_id;
        $invoiceId = $invoice->id;
        $timestamp = now()->format('YmdHis');
        $filename = "invoice_{$invoiceId}_{$timestamp}.pdf";

        $path = $file->storeAs(
            "invoices/pdfs/tenant_{$tenantId}",
            $filename,
            'private'
        );

        return $path;
    }

    /**
     * Get the PDF file URL for viewing
     */
    public function getPDFUrl(ProcessedInvoice $invoice): ?string
    {
        if (!$invoice->pdf_file_path) {
            return null;
        }

        return Storage::disk('private')->url($invoice->pdf_file_path);
    }

    /**
     * Delete the PDF file
     */
    public function deletePDF(ProcessedInvoice $invoice): bool
    {
        if (!$invoice->pdf_file_path) {
            return true;
        }

        try {
            Storage::disk('private')->delete($invoice->pdf_file_path);
            $invoice->update([
                'pdf_file_path' => null,
                'pdf_file_name' => null,
                'pdf_file_size' => null,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete PDF', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}


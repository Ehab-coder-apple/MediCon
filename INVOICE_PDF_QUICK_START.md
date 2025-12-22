# Invoice PDF Processing - Quick Start Guide

## Getting Started

### 1. Database Migration
The migration has already been run. To verify:
```bash
php artisan migrate:status
```

### 2. Create Test Invoice
Create a test invoice in the system:
1. Navigate to Admin Dashboard → AI Management → Invoices
2. Create a new invoice or use an existing one in "Uploaded" stage

### 3. Upload PDF

#### Web Interface
1. Go to invoice detail page
2. Scroll to "Upload Invoice PDF" section
3. Select a PDF file (max 10MB)
4. Click "Upload PDF"

#### API
```bash
curl -X POST http://localhost:8000/api/ai/invoices/{invoiceId}/upload-pdf \
  -H "Authorization: Bearer {token}" \
  -F "pdf_file=@invoice.pdf"
```

### 4. Convert to Items

#### Web Interface
1. After PDF upload, scroll to "Convert PDF to Items" section
2. Click "Convert to Items" button
3. System will extract items from PDF

#### API
```bash
curl -X POST http://localhost:8000/api/ai/invoices/{invoiceId}/convert-to-items \
  -H "Authorization: Bearer {token}"
```

### 5. Select Warehouse & Transfer

#### Web Interface
1. After items extraction, scroll to "Transfer to Warehouse" section
2. Click "Select Warehouse & Transfer"
3. Choose destination warehouse
4. Click "Confirm Transfer to Warehouse"

#### API
```bash
# Get available warehouses
curl -X GET http://localhost:8000/api/ai/invoices/{invoiceId}/available-warehouses \
  -H "Authorization: Bearer {token}"

# Transfer to warehouse
curl -X POST http://localhost:8000/api/ai/invoices/{invoiceId}/transfer-to-warehouse \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"warehouse_id": 1}'
```

## File Structure

### Services
- `app/Services/InvoicePDFUploadService.php` - PDF upload and storage
- `app/Services/InvoiceItemExtractionService.php` - Item extraction
- `app/Services/WarehouseTransferService.php` - Warehouse transfer

### Controllers
- `app/Http/Controllers/AIManagementController.php` - Web endpoints
- `app/Http/Controllers/Api/AIDocumentController.php` - API endpoints

### Views
- `resources/views/admin/ai/invoices/show.blade.php` - Invoice detail page
- `resources/views/admin/ai/invoices/select-warehouse.blade.php` - Warehouse selection

### Routes
- `routes/web.php` - Web routes (lines 275-286)
- `routes/api.php` - API routes (lines 165-174)

## Database Fields

### ProcessedInvoice Model
- `pdf_file_path` - Path to uploaded PDF
- `pdf_file_name` - Original filename
- `pdf_file_size` - File size in bytes
- `warehouse_id` - Selected warehouse
- `transfer_approved_by` - User who approved
- `transfer_approved_at` - Approval timestamp
- `transfer_status` - Transfer status
- `extraction_status` - Extraction status
- `extraction_error` - Error message

## Troubleshooting

### PDF Upload Fails
- Check file is PDF format
- Verify file size < 10MB
- Check storage permissions

### Item Extraction Fails
- Verify PDF file was uploaded
- Check extraction_error field for details
- Ensure PDF is readable

### Warehouse Transfer Fails
- Verify warehouse exists
- Check warehouse belongs to tenant
- Ensure items were extracted

## Next Steps

1. Implement actual PDF parsing (currently placeholder)
2. Integrate OCR service (Tesseract, AWS Textract, etc.)
3. Add item review/edit interface
4. Create batch records with expiry dates
5. Add inventory sync with warehouse stock


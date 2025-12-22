# Invoice PDF Processing - Code Structure

## Service Layer

### InvoicePDFUploadService
```
uploadPDF(invoice, file) → array
  - Validates file (PDF, max 10MB)
  - Stores file securely
  - Updates invoice with PDF metadata
  - Returns success/error response

getPDFUrl(invoice) → string|null
  - Returns secure URL for viewing PDF

deletePDF(invoice) → bool
  - Deletes PDF file and clears metadata
```

### InvoiceItemExtractionService
```
extractItemsFromPDF(invoice) → array
  - Validates PDF exists
  - Parses PDF (placeholder for OCR)
  - Creates ProcessedInvoiceItem records
  - Updates extraction_status
  - Returns success/error response

parseInvoicePDF(filePath) → array
  - Placeholder for actual PDF parsing
  - Should extract: product name, qty, price, batch, expiry

createInvoiceItems(invoice, items) → array
  - Creates ProcessedInvoiceItem records
  - Matches products with existing products
  - Handles errors gracefully

matchProduct(itemData) → Product|null
  - Matches by product code or name
```

### WarehouseTransferService
```
transferToWarehouse(invoice, warehouse) → array
  - Validates items exist
  - Transfers each item to warehouse
  - Updates inventory in WarehouseStock
  - Sets transfer_status = completed
  - Returns success/error response

transferItem(invoice, item, warehouse) → int
  - Creates/updates WarehouseStock
  - Creates batch if needed
  - Returns 1 on success, 0 on failure

getOrCreateBatch(item) → Batch|null
  - Creates batch with batch_number
  - Sets expiry_date and cost_price
  - Returns batch or null

approveTransfer(invoice) → array
  - Gets warehouse from invoice
  - Calls transferToWarehouse()
```

## Controller Layer

### AIManagementController (Web)
```
uploadInvoicePDF(request, id)
  - Validates PDF file
  - Calls InvoicePDFUploadService
  - Redirects with success/error

convertToItems(request, id)
  - Validates PDF exists
  - Calls InvoiceItemExtractionService
  - Redirects with success/error

selectWarehouse(request, id)
  - Gets invoice with items
  - Gets available warehouses
  - Returns select-warehouse view

approveWarehouseTransfer(request, id)
  - Validates warehouse_id
  - Calls WarehouseTransferService
  - Redirects to invoice show
```

### AIDocumentController (API)
```
uploadInvoicePDF(request, invoiceId)
  - Validates PDF file
  - Calls InvoicePDFUploadService
  - Returns JSON response

convertInvoiceToItems(request, invoiceId)
  - Validates PDF exists
  - Calls InvoiceItemExtractionService
  - Returns JSON response

transferToWarehouse(request, invoiceId)
  - Validates warehouse_id
  - Calls WarehouseTransferService
  - Returns JSON response

getAvailableWarehouses(request, invoiceId)
  - Gets warehouses for tenant
  - Returns JSON list
```

## Database Schema

### ProcessedInvoice Table (New Columns)
```
pdf_file_path: string|null
pdf_file_name: string|null
pdf_file_size: integer|null
warehouse_id: foreignId|null → warehouses.id
transfer_approved_by: foreignId|null → users.id
transfer_approved_at: timestamp|null
transfer_status: string (pending, approved, completed, failed)
extraction_status: string (pending, in_progress, completed, failed)
extraction_error: text|null
```

### Relationships
```
ProcessedInvoice
  - belongsTo(Warehouse) → warehouse()
  - belongsTo(User, 'transfer_approved_by') → transferApprovedBy()
```

## Routes

### Web Routes (routes/web.php)
```
POST   /admin/ai/invoices/{id}/upload-pdf
POST   /admin/ai/invoices/{id}/convert-to-items
GET    /admin/ai/invoices/{id}/select-warehouse
POST   /admin/ai/invoices/{id}/approve-warehouse-transfer
```

### API Routes (routes/api.php)
```
POST   /api/ai/invoices/{invoiceId}/upload-pdf
POST   /api/ai/invoices/{invoiceId}/convert-to-items
POST   /api/ai/invoices/{invoiceId}/transfer-to-warehouse
GET    /api/ai/invoices/{invoiceId}/available-warehouses
```

## Views

### show.blade.php (Updated)
```
- PDF Upload Section (Stage: Uploaded)
- Convert to Items Section (After PDF upload)
- Warehouse Transfer Section (After extraction)
- Action Buttons (Existing)
```

### select-warehouse.blade.php (New)
```
- Items Summary Table
- Warehouse Selection (Radio buttons)
- Transfer Information
- Confirmation Button
```

## Error Handling

All services include:
- Try-catch blocks
- Detailed error logging
- User-friendly error messages
- Database transaction rollback on failure
- Validation error responses

## Security

- File validation (PDF format, size limit)
- Tenant isolation (files in tenant directories)
- Private storage (files not publicly accessible)
- Authorization checks (user permissions)
- Warehouse validation (tenant ownership)
- Audit trail (track approvals)


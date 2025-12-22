# Multi-Step Invoice Workflow - Quick Reference

## 🚀 Quick Start

### View Invoices
```
GET /admin/ai/invoices
GET /admin/ai/invoices?workflow_stage=uploaded
GET /admin/ai/invoices?search=INV-001
```

### View Invoice Details
```
GET /admin/ai/invoices/{id}
```

### Approve for Processing (First Approval)
```
POST /admin/ai/invoices/{id}/approve-for-processing
POST /api/ai/invoices/{id}/approve-for-processing
```

### Approve for Inventory (Second Approval)
```
POST /admin/ai/invoices/{id}/approve-for-inventory
POST /api/ai/invoices/{id}/approve-for-inventory
```

## 📊 Workflow Stages

| Stage | Icon | Description | Next Action |
|-------|------|-------------|------------|
| uploaded | 📤 | PDF uploaded, awaiting review | Approve for Processing |
| approved_for_processing | ✓ | First approval done, OCR starting | System processes |
| processing | ⚙️ | OCR extraction in progress | System completes |
| processed | 📊 | OCR complete, data extracted | Approve for Inventory |
| approved_for_inventory | ✓ | Second approval done, adding items | System completes |
| completed | ✅ | Workflow finished | Done |

## 🔍 Filtering

### By Workflow Stage
```php
ProcessedInvoice::uploaded()->get();
ProcessedInvoice::approvedForProcessing()->get();
ProcessedInvoice::processing()->get();
ProcessedInvoice::processed()->get();
ProcessedInvoice::approvedForInventory()->get();
ProcessedInvoice::completed()->get();
```

### By Search
```php
ProcessedInvoice::where('invoice_number', 'like', '%INV%')->get();
```

## 👤 Approval Tracking

### Get First Approver
```php
$invoice->approvedForProcessingBy->name;
$invoice->approved_for_processing_at;
```

### Get Second Approver
```php
$invoice->approvedForInventoryBy->name;
$invoice->approved_for_inventory_at;
```

## 📝 Model Methods

### Relationships
```php
$invoice->approvedForProcessingBy(); // User who approved for processing
$invoice->approvedForInventoryBy();  // User who approved for inventory
```

### Scopes
```php
$invoice->uploaded();                // Uploaded stage
$invoice->approvedForProcessing();   // Approved for processing stage
$invoice->processing();              // Processing stage
$invoice->processed();               // Processed stage
$invoice->approvedForInventory();    // Approved for inventory stage
$invoice->completed();               // Completed stage
```

## 🔐 Authorization

All endpoints require:
- User authentication (Sanctum token for API)
- User authorization (can view/update invoice)
- Correct workflow stage for transitions

## 📱 Mobile App Integration

### Upload Invoice
```
POST /api/ai/documents/upload
Content-Type: multipart/form-data
- document_type: "invoice"
- file: <PDF file>
- branch_id: <branch_id>
```

### Check Status
```
GET /api/ai/documents/{documentId}/status
```

### Approve for Processing
```
POST /api/ai/invoices/{invoiceId}/approve-for-processing
```

### Approve for Inventory
```
POST /api/ai/invoices/{invoiceId}/approve-for-inventory
```

## 🛠️ Troubleshooting

**Q: Workflow stage not updating?**
A: Check database migration ran and workflow_stage column exists

**Q: Approval buttons not showing?**
A: Verify workflow_stage value matches expected stage

**Q: Approval history empty?**
A: Check approved_for_processing_by and approved_for_inventory_by are set

**Q: Items not added to inventory?**
A: Check addItemsToInventory() method implementation

## 📚 Related Files

- Model: `app/Models/ProcessedInvoice.php`
- Controller: `app/Http/Controllers/AIManagementController.php`
- API: `app/Http/Controllers/Api/AIDocumentController.php`
- Service: `app/Services/AIDocumentProcessingService.php`
- Views: `resources/views/admin/ai/invoices/`
- Routes: `routes/web.php`, `routes/api.php`


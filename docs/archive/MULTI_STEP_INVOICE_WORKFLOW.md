# Multi-Step Invoice Approval Workflow

## Overview

The Invoice Processing feature now includes a comprehensive multi-step approval workflow that ensures quality control and proper authorization at each stage of invoice processing.

## Workflow Stages

### 1. **Uploaded** (📤)
- **Trigger**: Invoice PDF is uploaded from mobile app or web interface
- **Action**: System creates ProcessedInvoice record with `workflow_stage = 'uploaded'`
- **Admin View**: PDF preview displayed on invoice detail page
- **Next Step**: Admin clicks "Approve for OCR Processing" button

### 2. **Approved for Processing** (✓)
- **Trigger**: Admin approves invoice for OCR processing
- **Action**: 
  - Sets `workflow_stage = 'approved_for_processing'`
  - Records `approved_for_processing_by` (user ID)
  - Records `approved_for_processing_at` (timestamp)
  - Triggers OCR extraction process
- **Next Step**: System automatically processes to "Processing" stage

### 3. **Processing** (⚙️)
- **Trigger**: OCR extraction is in progress
- **Action**:
  - Sets `workflow_stage = 'processing'`
  - Records `processing_started_at` (timestamp)
  - Extracts text and data from PDF
- **Next Step**: Automatically transitions to "Processed" when complete

### 4. **Processed** (📊)
- **Trigger**: OCR extraction completed successfully
- **Action**:
  - Sets `workflow_stage = 'processed'`
  - Records `processing_completed_at` (timestamp)
  - Displays extracted invoice items
- **Admin View**: Shows extracted data with confidence scores
- **Next Step**: Admin clicks "Approve for Inventory Upload" button

### 5. **Approved for Inventory** (✓)
- **Trigger**: Admin approves inventory upload
- **Action**:
  - Sets `workflow_stage = 'approved_for_inventory'`
  - Records `approved_for_inventory_by` (user ID)
  - Records `approved_for_inventory_at` (timestamp)
  - Adds items to inventory system
- **Next Step**: Automatically transitions to "Completed"

### 6. **Completed** (✅)
- **Trigger**: Inventory upload successful
- **Action**:
  - Sets `workflow_stage = 'completed'`
  - Records `inventory_uploaded_at` (timestamp)
  - Records `items_added_to_inventory` (count)
- **Status**: Invoice processing fully complete

## Database Schema

New columns added to `processed_invoices` table:
- `workflow_stage` - Current stage in the workflow
- `approved_for_processing_by` - User ID of first approver
- `approved_for_processing_at` - Timestamp of first approval
- `approved_for_inventory_by` - User ID of second approver
- `approved_for_inventory_at` - Timestamp of second approval
- `processing_started_at` - When OCR started
- `processing_completed_at` - When OCR completed
- `inventory_uploaded_at` - When items added to inventory
- `items_added_to_inventory` - Count of items added

## API Endpoints

### Get Invoice Details
```
GET /api/ai/invoices/{invoiceId}
```

### Approve for Processing (First Approval)
```
POST /api/ai/invoices/{invoiceId}/approve-for-processing
```

### Approve for Inventory (Second Approval)
```
POST /api/ai/invoices/{invoiceId}/approve-for-inventory
```

## Web Routes

### View Invoice Details
```
GET /admin/ai/invoices/{id}
```

### Approve for Processing
```
POST /admin/ai/invoices/{id}/approve-for-processing
```

### Approve for Inventory
```
POST /admin/ai/invoices/{id}/approve-for-inventory
```

## Features

✅ PDF preview before processing
✅ Two-step approval process
✅ Workflow progress visualization
✅ Approval history tracking
✅ Conditional data display based on stage
✅ Filtering by workflow stage
✅ Approval user tracking
✅ Timestamp tracking for audit trail


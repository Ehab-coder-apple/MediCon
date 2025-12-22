# AI & Document Processing - API Documentation

## Base URL
```
http://127.0.0.1:8000/api/ai
```

## Authentication
All endpoints require Sanctum token authentication:
```
Authorization: Bearer {token}
```

## Document Processing

### Upload Document
**POST** `/documents/upload`

Upload and process a document (invoice or prescription).

**Request:**
```
Content-Type: multipart/form-data

{
  "document_type": "invoice|prescription",
  "file": <binary file>,
  "branch_id": 1
}
```

**Response:**
```json
{
  "message": "Document uploaded and processing started",
  "document_id": 1,
  "status": "pending"
}
```

### Get Document Status
**GET** `/documents/{documentId}/status`

Check processing status of uploaded document.

**Response:**
```json
{
  "id": 1,
  "status": "completed|processing|failed",
  "document_type": "invoice|prescription",
  "processed_at": "2024-11-28T10:30:00Z",
  "error": null
}
```

## Invoice Management

### Get Invoice Details
**GET** `/invoices/{invoiceId}`

Retrieve processed invoice with all line items.

**Response:**
```json
{
  "id": 1,
  "invoice_number": "INV-001",
  "invoice_date": "2024-11-28",
  "supplier_name": "Pharma Supplier Inc",
  "total_amount": 5000.00,
  "status": "pending_review",
  "items": [
    {
      "id": 1,
      "product_name": "Paracetamol 500mg",
      "quantity": 100,
      "unit_price": 10.00,
      "total_price": 1000.00,
      "confidence_score": 95
    }
  ]
}
```

### Approve Invoice
**POST** `/invoices/{invoiceId}/approve`

Approve a processed invoice for inventory addition.

**Request:**
```json
{
  "notes": "Approved - all items verified"
}
```

**Response:**
```json
{
  "message": "Invoice approved"
}
```

## Prescription Management

### Get Prescription Details
**GET** `/prescriptions/{checkId}`

Retrieve prescription check with medication availability.

**Response:**
```json
{
  "id": 1,
  "patient_name": "John Doe",
  "prescription_date": "2024-11-28",
  "checked_at": "2024-11-28T10:30:00Z",
  "medications": [
    {
      "id": 1,
      "medication_name": "Paracetamol",
      "dosage": "500mg",
      "quantity_prescribed": 10,
      "availability_status": "in_stock",
      "available_quantity": 50,
      "product_id": 1
    }
  ]
}
```

## Product Information

### Search Products
**GET** `/products/search?query=paracetamol&branch_id=1`

Search for products by name or code.

**Query Parameters:**
- `query` (required): Search term
- `branch_id` (required): Branch ID

**Response:**
```json
[
  {
    "id": 1,
    "name": "Paracetamol 500mg",
    "code": "PARA-500",
    "manufacturer": "Pharma Co",
    "selling_price": 10.00,
    "active_quantity": 100,
    "information": {
      "active_ingredients": ["Paracetamol"],
      "side_effects": ["Nausea", "Dizziness"],
      "indications": ["Fever", "Pain relief"]
    }
  }
]
```

### Get Product Information
**GET** `/products/{productId}/info`

Get detailed pharmaceutical information for a product.

**Response:**
```json
{
  "id": 1,
  "name": "Paracetamol 500mg",
  "code": "PARA-500",
  "manufacturer": "Pharma Co",
  "selling_price": 10.00,
  "active_quantity": 100,
  "information": {
    "id": 1,
    "active_ingredients": ["Paracetamol"],
    "side_effects": ["Nausea", "Dizziness"],
    "indications": ["Fever", "Pain relief"],
    "dosage_information": "Adults: 500mg every 4-6 hours",
    "contraindications": ["Liver disease"],
    "drug_interactions": ["Warfarin"],
    "storage_requirements": ["Room temperature", "Dry place"]
  }
}
```

### Get Alternative Products
**GET** `/products/{productId}/alternatives?branch_id=1`

Get alternative products when requested medication unavailable.

**Query Parameters:**
- `branch_id` (required): Branch ID

**Response:**
```json
[
  {
    "id": 2,
    "name": "Ibuprofen 400mg",
    "manufacturer": "Pharma Co",
    "available_quantity": 50,
    "price": 12.00,
    "shelf_location": "Shelf A-5",
    "similarity_score": 85
  }
]
```

### Update Product Information
**POST** `/products/{productId}/info`

Add or update pharmaceutical information for a product.

**Request:**
```json
{
  "active_ingredients": ["Paracetamol"],
  "side_effects": ["Nausea", "Dizziness"],
  "indications": ["Fever", "Pain relief"],
  "dosage_information": "Adults: 500mg every 4-6 hours",
  "contraindications": ["Liver disease"],
  "drug_interactions": ["Warfarin"],
  "storage_requirements": ["Room temperature", "Dry place"]
}
```

**Response:**
```json
{
  "message": "Product information updated",
  "data": {
    "id": 1,
    "product_id": 1,
    "active_ingredients": ["Paracetamol"],
    "source": "manual_entry"
  }
}
```

## Error Responses

### 400 Bad Request
```json
{
  "message": "Validation failed",
  "errors": {
    "document_type": ["The document_type field is required"]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "Processing failed",
  "error": "OCR service unavailable"
}
```

## Rate Limiting

- 60 requests per minute per user
- 1000 requests per hour per user

## Pagination

List endpoints support pagination:
```
GET /api/ai/invoices?page=1&per_page=15
```

Response includes:
```json
{
  "data": [...],
  "current_page": 1,
  "per_page": 15,
  "total": 100,
  "last_page": 7
}
```


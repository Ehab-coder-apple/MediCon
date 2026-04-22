# Barcode Scanner Integration - Technical Specification

## API Endpoints to Create

### 1. Barcode Lookup
```
GET /api/products/by-barcode/{barcode}
Authentication: Bearer Token (Sanctum)

Response (200):
{
  "success": true,
  "product": {
    "id": 1,
    "name": "Paracetamol 500mg",
    "barcode": "5901234123457",
    "code": "PARA-500",
    "manufacturer": "Pharma Inc",
    "selling_price": 2.50,
    "cost_price": 1.50,
    "category": "Pain Relief",
    "batches": [
      {
        "id": 1,
        "batch_number": "B001",
        "quantity": 100,
        "expiry_date": "2026-12-31",
        "is_expired": false,
        "is_expiring_soon": false
      }
    ],
    "total_stock": 100,
    "is_low_stock": false,
    "warehouse_stock": [
      {
        "warehouse_id": 1,
        "warehouse_name": "On Shelf",
        "quantity": 100
      }
    ]
  }
}

Response (404):
{
  "success": false,
  "error": "Product not found"
}
```

### 2. Quick Sale Creation
```
POST /api/sales/quick-create
Authentication: Bearer Token (Sanctum)

Request:
{
  "customer_id": 1,
  "items": [
    {
      "product_id": 1,
      "batch_id": 1,
      "quantity": 2,
      "unit_price": 2.50
    }
  ],
  "payment_method": "cash",
  "discount_amount": 0,
  "tax_percentage": 0
}

Response (201):
{
  "success": true,
  "sale": {
    "id": 1,
    "invoice_number": "INV-2025-001",
    "total_amount": 5.00,
    "status": "completed",
    "items_count": 1
  }
}
```

### 3. Stock In (Receiving)
```
POST /api/inventory/stock-in
Authentication: Bearer Token (Sanctum)

Request:
{
  "product_id": 1,
  "quantity": 50,
  "batch_number": "B002",
  "expiry_date": "2026-12-31",
  "warehouse_id": 1,
  "reference": "PO-001"
}

Response (201):
{
  "success": true,
  "message": "Stock added successfully",
  "new_quantity": 150
}
```

### 4. Stock Out (Manual Deduction)
```
POST /api/inventory/stock-out
Authentication: Bearer Token (Sanctum)

Request:
{
  "product_id": 1,
  "quantity": 5,
  "reason": "damage",
  "warehouse_id": 1,
  "notes": "Damaged during transport"
}

Response (201):
{
  "success": true,
  "message": "Stock deducted successfully",
  "new_quantity": 95
}
```

### 5. Real-time Stock Levels
```
GET /api/inventory/stock-levels?product_id=1&warehouse_id=1
Authentication: Bearer Token (Sanctum)

Response (200):
{
  "success": true,
  "product_id": 1,
  "total_stock": 95,
  "by_warehouse": [
    {
      "warehouse_id": 1,
      "warehouse_name": "On Shelf",
      "quantity": 95
    }
  ],
  "by_batch": [
    {
      "batch_id": 1,
      "batch_number": "B001",
      "quantity": 95,
      "expiry_date": "2026-12-31"
    }
  ]
}
```

---

## Database Changes

### Add Barcode Field to Products
```sql
ALTER TABLE products ADD COLUMN barcode VARCHAR(255) UNIQUE NULLABLE;
ALTER TABLE products ADD INDEX idx_barcode (barcode);
```

### New Table: Inventory Transactions
```sql
CREATE TABLE inventory_transactions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  warehouse_id BIGINT,
  batch_id BIGINT,
  transaction_type ENUM('stock_in', 'stock_out', 'sale', 'purchase'),
  quantity INT NOT NULL,
  reference VARCHAR(255),
  notes TEXT,
  created_by BIGINT,
  created_at TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
  FOREIGN KEY (batch_id) REFERENCES batches(id)
);
```

---

## Mobile App Integration

### Scanner Flow
1. User taps "Scan Barcode"
2. Camera opens (or external scanner input)
3. Barcode captured → Call `GET /api/products/by-barcode/{barcode}`
4. Display product details + available batches
5. User selects quantity and batch
6. Item added to cart
7. User confirms → Call `POST /api/sales/quick-create`
8. Sale completed, inventory updated

### Offline Support
- Queue barcode scans locally (SQLite)
- When online, sync with backend
- Reuse existing sync pattern from attendance app

---

## External Scanner Support

### Bluetooth/USB Scanner
- Simulates keyboard input
- Works with existing text input field
- No additional code needed

### Network Scanner
- Can send HTTP requests directly to API
- Configure scanner to POST to `/api/products/by-barcode`
- Requires API key authentication

### Serial Port Scanner
- Requires native module (complex)
- Not recommended for MVP

---

## Error Handling

### Scenarios to Handle
1. **Barcode not found** - Return 404
2. **Out of stock** - Return 422 with message
3. **Expired batch** - Warn user, allow override
4. **Multiple batches** - Show selection UI
5. **Invalid barcode format** - Return 400
6. **Duplicate barcode** - Prevent during product creation

---

## Security Considerations

1. **Authentication** - All endpoints require Sanctum token
2. **Authorization** - Check user role (pharmacist, sales staff)
3. **Tenant isolation** - Ensure data belongs to user's tenant
4. **Rate limiting** - Prevent barcode scanning abuse
5. **Audit logging** - Log all inventory transactions

---

## Performance Optimization

1. **Barcode lookup** - Index on barcode field
2. **Stock queries** - Cache warehouse stock levels
3. **Batch selection** - Eager load batches with product
4. **Inventory transactions** - Batch insert for multiple items
5. **API response** - Return only necessary fields

---

## Testing Strategy

### Unit Tests
- Barcode validation logic
- Inventory calculation
- Batch selection logic

### Integration Tests
- Barcode lookup API
- Sale creation with inventory update
- Stock in/out operations

### End-to-End Tests
- Full scanning workflow
- Multiple items in one sale
- Offline sync

### External Scanner Tests
- Bluetooth scanner simulation
- Network scanner integration
- Keyboard input handling


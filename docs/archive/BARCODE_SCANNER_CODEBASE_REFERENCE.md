# Barcode Scanner - Codebase Reference Guide

## Existing Models to Leverage

### Product Model
**Location:** `app/Models/Product.php`
**Key Fields:** `id`, `name`, `code`, `cost_price`, `selling_price`, `is_active`
**Key Methods:**
- `batches()` - Get all batches
- `activeBatches()` - Get non-expired batches
- `getTotalQuantityAttribute()` - Total stock
- `getActiveQuantityAttribute()` - Active stock
- `getIsLowStockAttribute()` - Check if low stock

**To Add:** `barcode` field

### Batch Model
**Location:** `app/Models/Batch.php`
**Key Fields:** `product_id`, `batch_number`, `expiry_date`, `quantity`, `cost_price`
**Key Methods:**
- `getIsExpiredAttribute()` - Check if expired
- `getIsExpiringSoonAttribute()` - Check if expiring soon
- `scopeActive()` - Get active batches

### Sale & SaleItem Models
**Location:** `app/Models/Sale.php`, `app/Models/SaleItem.php`
**Key Features:**
- Automatic inventory deduction on sale creation
- `SaleItem::updateInventory()` - Deducts from WarehouseStock
- `SaleItem::restoreInventory()` - Restores on deletion
- Batch tracking with FIFO support

**To Reuse:** Inventory update logic

### WarehouseStock Model
**Location:** `app/Models/WarehouseStock.php`
**Key Fields:** `warehouse_id`, `product_id`, `batch_id`, `quantity`
**Purpose:** Track stock by warehouse and batch

**To Leverage:** Multi-warehouse support

### Purchase & PurchaseItem Models
**Location:** `app/Models/Purchase.php`, `app/Models/PurchaseItem.php`
**Key Features:**
- `PurchaseItem::createOrUpdateBatch()` - Auto-creates batches
- Automatic batch creation on purchase item save
- Batch quantity updates

**To Reuse:** Stock-in logic

---

## Existing API Controllers

### ProductInformationController
**Location:** `app/Http/Controllers/Api/ProductInformationController.php`
**Methods:**
- `search()` - Search by name or code
- `getProductInfo()` - Get product details
- `getAlternatives()` - Get alternative products

**To Extend:** Add barcode lookup method

### SaleController
**Location:** `app/Http/Controllers/SaleController.php`
**Methods:**
- `create()` - Show sale form
- `store()` - Create sale
- `getProductDetails()` - AJAX product lookup

**To Extend:** Add API endpoint for quick sale creation

### PurchaseController
**Location:** `app/Http/Controllers/PurchaseController.php`
**Methods:**
- `store()` - Create purchase
- `getProductDetails()` - Get product info

**To Extend:** Add API endpoint for stock-in

---

## Existing Patterns to Follow

### 1. Inventory Update Pattern (SaleItem)
```php
// Automatic on save
protected static function boot() {
    parent::boot();
    static::created(function ($saleItem) {
        $saleItem->updateInventory();
    });
}

public function updateInventory(): void {
    // Deduct from WarehouseStock
    // Update Batch quantity
}
```

### 2. Batch Management Pattern (PurchaseItem)
```php
public function createOrUpdateBatch(): ?Batch {
    // Find or create batch
    // Update quantity
    // Return batch
}
```

### 3. API Response Pattern
```php
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => 'Operation successful'
]);
```

### 4. Authorization Pattern
```php
$this->authorize('view', $model);
$this->authorize('update', $model);
```

---

## Existing Routes to Reference

### API Routes
**Location:** `routes/api.php`
- `GET /api/products/search` - Product search
- `GET /api/products/{productId}/info` - Product info
- `POST /api/ai/invoices/{invoiceId}/approve` - Approval pattern

### Web Routes
**Location:** `routes/web.php`
- `GET /sales/product-lookup` - Product lookup
- `POST /admin/sales` - Sale creation
- `POST /admin/purchases` - Purchase creation

---

## Existing Validation Patterns

### Product Search Validation
```php
$request->validate([
    'query' => 'required|string|min:2',
    'branch_id' => 'required|exists:branches,id',
]);
```

### Sale Creation Validation
```php
$validated = $request->validate([
    'customer_id' => 'required|exists:customers,id',
    'items' => 'required|array|min:1',
    'items.*.product_id' => 'required|exists:products,id',
    'items.*.quantity' => 'required|integer|min:1',
]);
```

---

## Existing Error Handling

### Authorization Errors
```php
$this->authorize('update', $product);
// Throws AuthorizationException if unauthorized
```

### Validation Errors
```php
$request->validate([...]);
// Throws ValidationException if invalid
```

### Not Found Errors
```php
Product::findOrFail($id);
// Throws ModelNotFoundException if not found
```

---

## Mobile App Integration Points

### Existing Offline Support
**Location:** `MediConAttendance/src/services/offlineDB.ts`
- SQLite local storage
- Automatic sync when online
- Network connectivity detection

**To Reuse:** Same pattern for barcode scanning

### Existing API Service
**Location:** `MediConAttendance/src/services/apiService.ts`
- Bearer token authentication
- Error handling
- Request/response interceptors

**To Reuse:** For new barcode endpoints

### Existing Navigation
**Location:** `MediConAttendance/App.tsx`
- Screen management
- State management
- Safe area handling

**To Extend:** Add barcode scanner screen

---

## Key Files to Modify

### Backend
1. `database/migrations/` - Add barcode field
2. `app/Models/Product.php` - Add barcode field
3. `app/Http/Controllers/Api/ProductInformationController.php` - Add barcode lookup
4. `app/Http/Controllers/Api/SaleController.php` - Add quick sale API
5. `routes/api.php` - Add new routes

### Mobile App
1. `MediConAttendance/App.tsx` - Add barcode scanner screen
2. `MediConAttendance/src/services/apiService.ts` - Add barcode endpoints
3. `MediConAttendance/src/services/offlineDB.ts` - Add barcode queue

---

## Testing Files to Create

### Backend Tests
- `tests/Feature/BarcodeProductLookupTest.php`
- `tests/Feature/QuickSaleCreationTest.php`
- `tests/Feature/InventoryTransactionTest.php`

### Mobile Tests
- `MediConAttendance/__tests__/barcode-scanner.test.ts`
- `MediConAttendance/__tests__/barcode-api.test.ts`

---

## Documentation Files to Create

- `BARCODE_SCANNER_SETUP.md` - Setup instructions
- `BARCODE_SCANNER_API_DOCS.md` - API documentation
- `BARCODE_SCANNER_TROUBLESHOOTING.md` - Troubleshooting guide


# 🎮 Barcode Scanner Simulator & Testing Guide

## ✅ Testing Options Available

You have **3 ways** to test the barcode scanner without a physical device:

---

## Option 1: Browser Console Simulator (Easiest) ⚡

### What It Is:
Simulate barcode scans directly in your browser using JavaScript console.

### How to Use:

**Step 1: Open MediCon Web App**
```
1. Go to http://localhost:8000
2. Navigate to Sales → Create Invoice
3. Click on the barcode input field
```

**Step 2: Open Browser Console**
```
Mac: Cmd + Option + J
Windows: Ctrl + Shift + J
```

**Step 3: Paste This Code**
```javascript
// Simulate barcode scan
const barcodeField = document.querySelector('input[placeholder*="barcode"]');
if (barcodeField) {
  barcodeField.value = '5901234123457';
  barcodeField.dispatchEvent(new Event('input', { bubbles: true }));
  barcodeField.dispatchEvent(new Event('change', { bubbles: true }));
  console.log('✅ Barcode scanned: 5901234123457');
} else {
  console.log('❌ Barcode field not found');
}
```

**Step 4: Press Enter**
- Product should appear automatically
- Select quantity and batch
- Click "Add to Cart"

### Advantages:
- ✅ No hardware needed
- ✅ Instant testing
- ✅ Works in any browser
- ✅ Test multiple barcodes quickly

### Test Barcodes:
```
5901234123457  - Test Medicine
5901234123458  - Paracetamol
5901234123459  - Aspirin
MED001         - Product code fallback
```

---

## Option 2: Automated Test Suite (Comprehensive) 🧪

### What It Is:
Run 9 automated tests that verify all barcode scanner functionality.

### How to Run:

**Step 1: Open Terminal**
```bash
cd /Users/ehabkhorshed/Desktop/Documents/augment-projects/MediCon
```

**Step 2: Run All Tests**
```bash
php artisan test tests/Feature/BarcodeScannerTest.php --no-coverage
```

**Step 3: View Results**
```
✓ test_lookup_product_by_barcode
✓ test_lookup_product_by_code_fallback
✓ test_barcode_not_found
✓ test_get_product_details
✓ test_check_stock_available
✓ test_check_stock_insufficient
✓ test_quick_sale_creation
✓ test_quick_sale_insufficient_stock
✓ test_unauthorized_access

9 tests passed ✅
```

### Run Specific Tests:
```bash
# Test barcode lookup only
php artisan test tests/Feature/BarcodeScannerTest.php::test_lookup_product_by_barcode

# Test stock checking
php artisan test tests/Feature/BarcodeScannerTest.php::test_check_stock_available

# Test sale creation
php artisan test tests/Feature/BarcodeScannerTest.php::test_quick_sale_creation
```

### Advantages:
- ✅ Comprehensive testing
- ✅ All scenarios covered
- ✅ Automated verification
- ✅ Repeatable testing
- ✅ CI/CD ready

---

## Option 3: Manual API Testing (Advanced) 🔧

### What It Is:
Test API endpoints directly using curl or Postman.

### How to Use:

**Step 1: Get Authentication Token**
```bash
# Login and get token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

**Step 2: Test Barcode Lookup**
```bash
curl -X GET "http://localhost:8000/api/products/by-barcode/5901234123457" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Step 3: Test Stock Check**
```bash
curl -X POST "http://localhost:8000/api/products/1/check-stock" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 5,
    "batch_id": 1
  }'
```

**Step 4: Test Quick Sale**
```bash
curl -X POST "http://localhost:8000/api/sales/quick-create" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "batch_id": 1,
        "quantity": 5,
        "unit_price": 100.00
      }
    ],
    "customer_name": "Test Customer",
    "payment_method": "cash",
    "paid_amount": 500.00
  }'
```

### Using Postman:
1. Download Postman (free)
2. Create new request
3. Set method to GET/POST
4. Enter URL
5. Add Authorization header
6. Send request

### Advantages:
- ✅ Direct API testing
- ✅ Full control
- ✅ See raw responses
- ✅ Test edge cases
- ✅ Integration testing

---

## Option 4: Browser Extension Simulator 🧩

### What It Is:
Use a browser extension to simulate keyboard input.

### Recommended Extensions:

**1. Keyboard Simulator**
- Chrome Web Store: "Keyboard Simulator"
- Allows simulating keyboard events
- Perfect for testing keyboard input

**2. Postman**
- Chrome Web Store: "Postman"
- Full API testing tool
- Can test all endpoints

**3. REST Client**
- VS Code Extension: "REST Client"
- Test APIs directly in VS Code
- Create .http files for testing

### How to Use Keyboard Simulator:
1. Install extension
2. Open MediCon Sales page
3. Click barcode field
4. Use extension to send text
5. Product appears automatically

---

## 🎯 Quick Testing Workflow

### Test 1: Barcode Lookup (2 minutes)
```javascript
// In browser console
const barcodeField = document.querySelector('input[placeholder*="barcode"]');
barcodeField.value = '5901234123457';
barcodeField.dispatchEvent(new Event('input', { bubbles: true }));
// Product should appear ✅
```

### Test 2: Add to Cart (2 minutes)
```javascript
// Select quantity
document.querySelector('input[name="quantity"]').value = '5';
// Click Add to Cart button
document.querySelector('button:contains("Add to Cart")').click();
// Item should appear in cart ✅
```

### Test 3: Checkout (2 minutes)
```javascript
// Click Checkout button
document.querySelector('button:contains("Checkout")').click();
// Sale should complete ✅
```

---

## 📊 Test Data Available

### Test Products:
```
ID: 1
Name: Test Medicine
Barcode: 5901234123457
Code: MED001
Price: $100.00
Stock: 50 units
Batch: BATCH001
Expiry: 2025-06-15
```

### Test User:
```
Email: test@example.com
Password: password
Tenant: Test Pharmacy
```

### Test Tenant:
```
Name: Test Pharmacy
Slug: test-pharmacy
Email: test@pharmacy.com
```

---

## 🚀 Recommended Testing Path

### For Quick Testing (5 minutes):
1. Use Browser Console Simulator
2. Test barcode lookup
3. Test add to cart
4. Test checkout

### For Comprehensive Testing (15 minutes):
1. Run automated test suite
2. Use browser console for manual testing
3. Test multiple barcodes
4. Test error scenarios

### For Full Integration Testing (30 minutes):
1. Run automated tests
2. Use browser console simulator
3. Use API testing with curl
4. Test with Postman
5. Verify database changes

---

## 🐛 Troubleshooting

### Console Simulator Not Working?
```javascript
// Check if field exists
console.log(document.querySelector('input[placeholder*="barcode"]'));

// Try alternative selector
const inputs = document.querySelectorAll('input');
inputs.forEach((input, i) => {
  console.log(i, input.placeholder, input.name);
});
```

### Tests Failing?
```bash
# Check database
php artisan tinker
>>> Product::all();
>>> Batch::all();

# Reset database
php artisan migrate:fresh --seed
```

### API Not Responding?
```bash
# Check server status
php artisan serve

# Check logs
tail -f storage/logs/laravel.log
```

---

## ✅ Testing Checklist

- [ ] Browser console simulator works
- [ ] Barcode lookup returns product
- [ ] Product details display correctly
- [ ] Batch selection works
- [ ] Add to cart works
- [ ] Cart updates correctly
- [ ] Checkout completes
- [ ] Inventory updates
- [ ] Invoice generates
- [ ] All 9 tests pass

---

## 📚 Related Documentation

- `BARCODE_READER_QUICK_START.md` - Quick start guide
- `BARCODE_READER_MEDICON_WORKFLOW.md` - Usage workflow
- `BARCODE_SCANNER_TECHNICAL_SPEC.md` - API specifications
- `tests/Feature/BarcodeScannerTest.php` - Test code

---

**Ready to test? Start with the Browser Console Simulator! 🚀**


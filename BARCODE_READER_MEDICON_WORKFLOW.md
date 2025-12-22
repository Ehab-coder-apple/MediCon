# 📱 Barcode Reader Workflow in MediCon - Step by Step

## Complete Workflow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    BARCODE SCANNER WORKFLOW                 │
└─────────────────────────────────────────────────────────────┘

START
  ↓
[1] Open MediCon Web App
  ↓
[2] Navigate to Sales → Create Invoice
  ↓
[3] Barcode Scanner Component Loads
  ↓
[4] Click Barcode Input Field
  ↓
[5] Scan Product Barcode
  ↓
[6] System Looks Up Product
  ├─ Searches by barcode first
  └─ Falls back to product code
  ↓
[7] Product Details Display
  ├─ Product name
  ├─ Price
  ├─ Available batches
  └─ Stock levels
  ↓
[8] Select Quantity & Batch
  ├─ Enter quantity
  ├─ Choose batch (if multiple)
  └─ Check expiry date
  ↓
[9] Click "Add to Cart"
  ↓
[10] Product Added to Sale Items
  ├─ Item appears in cart
  ├─ Subtotal updates
  └─ Ready for next scan
  ↓
[11] Repeat Steps 4-10 for More Products
  ↓
[12] Review Sale Items
  ├─ Check quantities
  ├─ Verify prices
  └─ Confirm total
  ↓
[13] Enter Customer Details (Optional)
  ├─ Select existing customer
  └─ Or create walk-in customer
  ↓
[14] Click "Checkout"
  ↓
[15] Sale Completed
  ├─ Invoice generated
  ├─ Inventory updated
  └─ Transaction logged
  ↓
END ✅
```

---

## Detailed Steps with Screenshots

### Step 1: Open MediCon Web App

**URL:** `http://localhost:8000` (or your MediCon server)

**What You See:**
- MediCon dashboard
- Main navigation menu on left
- Sales option in menu

---

### Step 2: Navigate to Sales

**Click:** Sales → Create Invoice

**What You See:**
- Invoice creation page
- Product search area at top
- Barcode scanner icon 📱
- Empty cart below

---

### Step 3: Click Barcode Input Field

**Location:** Top of page, next to barcode icon

**What You See:**
- Input field is focused (cursor visible)
- Field is ready for input
- Keyboard cursor blinking

---

### Step 4: Scan Product Barcode

**Action:** Point scanner at barcode and press trigger

**What Happens:**
1. Scanner reads barcode
2. Sends text to input field
3. System automatically searches
4. Product details appear

**Example Barcode:** `5901234123457`

---

### Step 5: Product Details Appear

**You See:**
```
┌─────────────────────────────────────┐
│ Product: Test Medicine              │
│ Price: $100.00                      │
│ Stock: 50 units available           │
│                                     │
│ Available Batches:                  │
│ ☐ BATCH001 - Exp: 2025-06-15       │
│   Quantity: 50                      │
│                                     │
│ [Select Batch] [Add to Cart]        │
└─────────────────────────────────────┘
```

---

### Step 6: Select Quantity & Batch

**Actions:**
1. Click on batch checkbox to select
2. Enter quantity in quantity field
3. Verify expiry date

**Example:**
- Batch: BATCH001
- Quantity: 5
- Expiry: 2025-06-15 ✅

---

### Step 7: Click "Add to Cart"

**Button Location:** Bottom right of product details

**What Happens:**
1. Product added to cart
2. Cart updates with item
3. Subtotal recalculates
4. Input field clears
5. Ready for next scan

---

### Step 8: Cart Shows Item

**You See:**
```
┌─────────────────────────────────────┐
│ SALE ITEMS                          │
├─────────────────────────────────────┤
│ Test Medicine                       │
│ Qty: 5 × $100.00 = $500.00         │
│ Batch: BATCH001 (Exp: 2025-06-15)  │
│ [Remove]                            │
├─────────────────────────────────────┤
│ Subtotal: $500.00                   │
│ Tax: $50.00                         │
│ Total: $550.00                      │
└─────────────────────────────────────┘
```

---

### Step 9: Scan More Products (Optional)

**Repeat Steps 4-8** for additional products

**Example Multi-Item Sale:**
```
Item 1: Test Medicine × 5 = $500.00
Item 2: Paracetamol × 10 = $200.00
Item 3: Aspirin × 3 = $75.00
─────────────────────────────
Total: $775.00
```

---

### Step 10: Enter Customer Details

**Options:**
1. **Select Existing Customer**
   - Click "Select Customer"
   - Choose from list
   - Click "Select"

2. **Create Walk-in Customer**
   - Click "New Customer"
   - Enter name
   - Click "Create"

3. **Skip (Anonymous Sale)**
   - Leave blank
   - Proceed to checkout

---

### Step 11: Review Before Checkout

**Verify:**
- ✅ All items correct
- ✅ Quantities correct
- ✅ Prices correct
- ✅ Total amount correct
- ✅ Customer selected (if needed)

---

### Step 12: Click "Checkout"

**Button Location:** Bottom right of page

**What Happens:**
1. Sale is processed
2. Inventory is updated
3. Invoice is generated
4. Transaction is logged
5. Success message appears

---

### Step 13: Sale Complete

**You See:**
```
┌─────────────────────────────────────┐
│ ✅ SALE COMPLETED SUCCESSFULLY      │
├─────────────────────────────────────┤
│ Invoice #: INV-2025-001234          │
│ Total: $775.00                      │
│ Change: $225.00 (if paid $1000)     │
│                                     │
│ [Print Invoice] [New Sale]          │
└─────────────────────────────────────┘
```

---

## Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Focus barcode field | `Tab` or click field |
| Clear field | `Ctrl+A` then `Delete` |
| Remove item from cart | Click [Remove] button |
| Proceed to checkout | `Enter` or click button |
| Print invoice | `Ctrl+P` |

---

## Common Scanning Scenarios

### Scenario 1: Single Product Sale
```
Scan: 5901234123457
↓
Product: Test Medicine
↓
Qty: 1, Batch: BATCH001
↓
Add to Cart
↓
Checkout
↓
Done! ✅
```

### Scenario 2: Multiple Same Product
```
Scan: 5901234123457
↓
Product: Test Medicine
↓
Qty: 5, Batch: BATCH001
↓
Add to Cart
↓
Checkout
↓
Done! ✅
```

### Scenario 3: Multiple Different Products
```
Scan: 5901234123457 → Add to Cart
↓
Scan: 5901234123458 → Add to Cart
↓
Scan: 5901234123459 → Add to Cart
↓
Review Cart (3 items)
↓
Checkout
↓
Done! ✅
```

---

## What Happens Behind the Scenes

### When You Scan:
1. **Barcode Captured** - Scanner sends text to input field
2. **API Call** - System calls `/api/products/by-barcode/{barcode}`
3. **Product Lookup** - Database searches for matching product
4. **Details Retrieved** - Product info, batches, stock levels
5. **Display Updated** - Product details shown to user

### When You Add to Cart:
1. **Validation** - Check quantity and batch availability
2. **Cart Updated** - Item added to sale items list
3. **Totals Recalculated** - Subtotal, tax, total updated
4. **Field Cleared** - Ready for next scan

### When You Checkout:
1. **Sale Created** - Sale record created in database
2. **Items Added** - All cart items added to sale
3. **Inventory Updated** - Stock deducted from warehouse
4. **Invoice Generated** - Invoice created automatically
5. **Transaction Logged** - Activity logged for audit trail

---

## Tips for Fast Scanning

### ⚡ Speed Tips:
1. **Pre-focus field** - Click barcode field before scanning
2. **Batch selection** - Select batch before scanning next
3. **Quantity entry** - Have quantities ready
4. **Smooth scanning** - Scan at consistent angle
5. **Quick checkout** - Review once, then checkout

### 🎯 Accuracy Tips:
1. **Clean lens** - Keep scanner lens clean
2. **Proper distance** - Scan from 2-6 inches
3. **Steady hand** - Hold scanner steady
4. **Good lighting** - Ensure adequate lighting
5. **Verify product** - Check product name matches

---

**Ready to start scanning? Follow the workflow above! 🚀**


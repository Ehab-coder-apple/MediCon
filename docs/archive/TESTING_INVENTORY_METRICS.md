# Testing Inventory Metrics - Complete Guide

## Overview

This guide provides step-by-step instructions for testing the 4 new inventory metric cards on the admin dashboard.

## Prerequisites

- Laravel application running (`php artisan serve`)
- Database configured and migrations run
- Admin user account created

## Step 1: Seed Test Data

### Option A: Quick Seed (Recommended)
```bash
php artisan seed:inventory-metrics
```

This creates 12 test products without affecting existing data.

### Option B: Full Database Reset
```bash
php artisan migrate:fresh --seed
```

This resets the entire database and seeds all data including test products.

### Option C: Fresh Test Data Only
```bash
php artisan seed:inventory-metrics --fresh
```

This truncates products/batches and creates fresh test data.

## Step 2: Access the Dashboard

1. Open browser and navigate to: `http://127.0.0.1:8000/admin/dashboard`
2. Login with admin credentials if not already logged in
3. You should see the admin dashboard with the new inventory metrics section

## Step 3: Verify the Inventory Metrics Cards

### Expected Display

Below the "User Statistics" section, you should see 4 new cards:

```
┌──────────────────────────────────────────────────────────────┐
│ Inventory Metrics                                            │
├──────────────────────────────────────────────────────────────┤
│ ⚠️ Expired    │ ⏰ Nearly    │ 📉 Low Stock │ 🚫 Out of Stock │
│ Products     │ Expired     │ Products     │ Products        │
│              │ Products    │              │                 │
│      3       │      3      │      3       │       3         │
└──────────────────────────────────────────────────────────────┘
```

### Card Details

| Card | Expected Count | Color | Icon |
|------|---|---|---|
| Expired Products | 3 | Red (#dc2626) | ⚠️ |
| Nearly Expired Products | 3 | Amber (#f59e0b) | ⏰ |
| Low Stock Products | 3 | Orange (#f97316) | 📉 |
| Out of Stock Products | 3 | Red (#ef4444) | 🚫 |

## Step 4: Test Responsive Design

### Mobile View (< 768px)
```bash
# In browser DevTools:
1. Press F12 to open DevTools
2. Click device toggle (mobile icon)
3. Select iPhone 12 or similar
4. Verify cards stack vertically
5. Verify text is readable
6. Verify icons display correctly
```

Expected: Cards should stack in 1 column on mobile

### Tablet View (768px - 1024px)
```bash
# In browser DevTools:
1. Select iPad or similar device
2. Verify cards display in 2 columns
3. Verify spacing is appropriate
```

Expected: Cards should display in 2 columns on tablet

### Desktop View (> 1024px)
```bash
# In browser DevTools:
1. Select "Responsive" and set width to 1200px+
2. Or just use full browser window
3. Verify cards display in 4 columns
4. Verify spacing and alignment
```

Expected: Cards should display in 4 columns on desktop

## Step 5: Verify Test Data in Database

### Using Artisan Tinker
```bash
php artisan tinker

# Check expired products
>>> App\Models\Product::whereIn('code', ['EXP-ANT-001', 'EXP-COUGH-001', 'EXP-VIT-001'])->count()
3

# Check nearly expired products
>>> App\Models\Product::whereIn('code', ['NEAR-PAIN-001', 'NEAR-DIG-001', 'NEAR-ALLERGY-001'])->count()
3

# Check low stock products
>>> App\Models\Product::whereIn('code', ['LOW-INS-001', 'LOW-BP-001', 'LOW-CREAM-001'])->count()
3

# Check out of stock products
>>> App\Models\Product::whereIn('code', ['OOS-CARD-001', 'OOS-MIGR-001', 'OOS-SLEEP-001'])->count()
3

# Exit tinker
>>> exit
```

### Using Database Query
```bash
# Check all test products
SELECT code, name, alert_quantity FROM products 
WHERE code LIKE 'EXP-%' OR code LIKE 'NEAR-%' OR code LIKE 'LOW-%' OR code LIKE 'OOS-%';

# Check batches for expired products
SELECT p.code, p.name, b.batch_number, b.expiry_date, b.quantity 
FROM products p 
JOIN batches b ON p.id = b.product_id 
WHERE p.code LIKE 'EXP-%';
```

## Step 6: Test Metric Calculations

### Expired Products Calculation
```bash
php artisan tinker

# Should return 3 (products with expired batches)
>>> App\Models\Product::whereHas('batches', function($q) { 
    $q->where('expiry_date', '<=', now()); 
})->distinct()->count()
3
```

### Nearly Expired Products Calculation
```bash
# Should return 3 (products expiring within 30 days)
>>> App\Models\Product::whereHas('batches', function($q) { 
    $q->where('expiry_date', '>', now())
      ->where('expiry_date', '<=', now()->addDays(30)); 
})->distinct()->count()
3
```

### Low Stock Products Calculation
```bash
# Should return 3 (products below alert threshold)
>>> $products = App\Models\Product::with('batches')->get();
>>> $products->filter(function($p) { return $p->is_low_stock; })->count()
3
```

### Out of Stock Products Calculation
```bash
# Should return 3 (products with zero quantity)
>>> $products = App\Models\Product::with('batches')->get();
>>> $products->filter(function($p) { return $p->active_quantity == 0; })->count()
3
```

## Step 7: Test Dynamic Updates

### Add a New Expired Product
```bash
php artisan tinker

>>> $product = App\Models\Product::create([
    'name' => 'Test Expired Product',
    'code' => 'TEST-EXP-001',
    'category' => 'Test',
    'cost_price' => 5.00,
    'selling_price' => 10.00,
    'alert_quantity' => 20,
]);

>>> App\Models\Batch::create([
    'product_id' => $product->id,
    'batch_number' => 'TEST-BATCH-001',
    'expiry_date' => now()->subDays(10),
    'quantity' => 50,
    'cost_price' => 5.00,
]);

>>> exit
```

**Expected Result**: Refresh dashboard, "Expired Products" count should increase to 4

### Add a New Out of Stock Product
```bash
php artisan tinker

>>> $product = App\Models\Product::create([
    'name' => 'Test Out of Stock',
    'code' => 'TEST-OOS-001',
    'category' => 'Test',
    'cost_price' => 8.00,
    'selling_price' => 15.00,
    'alert_quantity' => 25,
]);

>>> App\Models\Batch::create([
    'product_id' => $product->id,
    'batch_number' => 'TEST-OOS-BATCH-001',
    'expiry_date' => now()->addMonths(12),
    'quantity' => 0,
    'cost_price' => 8.00,
]);

>>> exit
```

**Expected Result**: Refresh dashboard, "Out of Stock Products" count should increase to 4

## Step 8: Test Cache Clearing

```bash
# Clear application cache
php artisan cache:clear

# Refresh dashboard in browser
# Verify metrics still display correctly
```

## Troubleshooting

### Issue: Cards show 0 for all metrics
**Solution**:
1. Verify test data was seeded: `php artisan seed:inventory-metrics`
2. Check database: `php artisan tinker` → `App\Models\Product::count()`
3. Clear cache: `php artisan cache:clear`
4. Refresh browser (Ctrl+F5)

### Issue: Duplicate batch number error
**Solution**:
```bash
php artisan seed:inventory-metrics --fresh
```

### Issue: Cards not displaying
**Solution**:
1. Check browser console for JavaScript errors (F12)
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Verify view file exists: `resources/views/admin/dashboard.blade.php`
4. Clear view cache: `php artisan view:clear`

### Issue: Metrics not updating after adding products
**Solution**:
1. Clear cache: `php artisan cache:clear`
2. Refresh browser page (Ctrl+F5 or Cmd+Shift+R)
3. Check that products have correct expiry dates and quantities

## Test Checklist

- [ ] Seed test data successfully
- [ ] Dashboard displays 4 inventory metric cards
- [ ] All cards show count of 3
- [ ] Cards display correct colors
- [ ] Cards display correct icons
- [ ] Mobile view: cards stack in 1 column
- [ ] Tablet view: cards display in 2 columns
- [ ] Desktop view: cards display in 4 columns
- [ ] Test data exists in database
- [ ] Metric calculations are correct
- [ ] Adding new products updates metrics
- [ ] Cache clearing doesn't break display
- [ ] No console errors in browser
- [ ] No errors in Laravel logs

## Next Steps

After successful testing:
1. Document any issues found
2. Test with production-like data volume
3. Consider adding click-through to detailed views
4. Plan for additional metrics or features
5. Set up automated testing for metrics


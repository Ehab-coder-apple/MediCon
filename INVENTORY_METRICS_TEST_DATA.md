# Inventory Metrics Test Data Guide

This guide explains how to use the inventory metrics test data for testing the admin dashboard inventory cards.

## Overview

The inventory metrics test data creates 12 pharmaceutical products across 4 categories to test the dashboard's inventory monitoring cards:

1. **Expired Products** (3 products)
2. **Nearly Expired Products** (3 products)
3. **Low Stock Products** (3 products)
4. **Out of Stock Products** (3 products)

## Quick Start

### Option 1: Run Full Database Seed (Includes Test Data)

```bash
php artisan migrate:fresh --seed
```

This will:
- Reset the database
- Run all migrations
- Seed all data including the inventory metrics test data

### Option 2: Seed Only Inventory Metrics Test Data

```bash
php artisan seed:inventory-metrics
```

This command:
- Creates 12 test products with appropriate batches
- Does NOT truncate existing data
- Can be run multiple times (uses `firstOrCreate` to prevent duplicates)

### Option 3: Seed with Fresh Data (Truncate First)

```bash
php artisan seed:inventory-metrics --fresh
```

This command:
- Truncates products and batches tables
- Creates fresh test data
- Useful for resetting test data

## Test Data Details

### 1. Expired Products (3)

Products with batches that have already passed their expiry date.

| Product Name | Code | Expiry Dates | Quantity |
|---|---|---|---|
| Expired Antibiotic Syrup | EXP-ANT-001 | 60 days ago, 30 days ago | 45, 30 |
| Expired Cough Syrup | EXP-COUGH-001 | 60 days ago, 30 days ago | 45, 30 |
| Expired Vitamin C Tablets | EXP-VIT-001 | 60 days ago, 30 days ago | 45, 30 |

**Dashboard Card**: Shows count of products with expired batches (Red card with ⚠️ icon)

### 2. Nearly Expired Products (3)

Products with batches expiring within the next 30 days.

| Product Name | Code | Expiry Dates | Quantity |
|---|---|---|---|
| Nearly Expired Pain Relief | NEAR-PAIN-001 | +15 days, +25 days | 50, 35 |
| Nearly Expired Digestive Aid | NEAR-DIG-001 | +15 days, +25 days | 50, 35 |
| Nearly Expired Allergy Relief | NEAR-ALLERGY-001 | +15 days, +25 days | 50, 35 |

**Dashboard Card**: Shows count of products expiring within 30 days (Amber card with ⏰ icon)

### 3. Low Stock Products (3)

Products where total quantity is below the alert threshold.

| Product Name | Code | Alert Qty | Batch Qty | Total |
|---|---|---|---|---|
| Low Stock Insulin Injection | LOW-INS-001 | 50 | 15, 10 | 25 |
| Low Stock Blood Pressure Monitor | LOW-BP-001 | 30 | 15, 10 | 25 |
| Low Stock Antibiotic Cream | LOW-CREAM-001 | 40 | 15, 10 | 25 |

**Dashboard Card**: Shows count of products below alert threshold (Orange card with 📉 icon)

### 4. Out of Stock Products (3)

Products with zero quantity across all batches.

| Product Name | Code | Alert Qty | Batch Qty |
|---|---|---|---|
| Out of Stock Cardiac Medicine | OOS-CARD-001 | 25 | 0 |
| Out of Stock Migraine Relief | OOS-MIGR-001 | 20 | 0 |
| Out of Stock Sleep Aid | OOS-SLEEP-001 | 15 | 0 |

**Dashboard Card**: Shows count of products with zero quantity (Red card with 🚫 icon)

## Dashboard Cards

The admin dashboard displays 4 inventory metric cards:

### Card 1: Expired Products
- **Color**: Red (#dc2626)
- **Icon**: ⚠️
- **Shows**: Count of products with expired batches
- **Expected Value**: 3 (with test data)

### Card 2: Nearly Expired Products
- **Color**: Amber (#f59e0b)
- **Icon**: ⏰
- **Shows**: Count of products expiring within 30 days
- **Expected Value**: 3 (with test data)

### Card 3: Low Stock Products
- **Color**: Orange (#f97316)
- **Icon**: 📉
- **Shows**: Count of products below alert threshold
- **Expected Value**: 3 (with test data)

### Card 4: Out of Stock Products
- **Color**: Red (#ef4444)
- **Icon**: 🚫
- **Shows**: Count of products with zero quantity
- **Expected Value**: 3 (with test data)

## Testing Workflow

1. **Seed the test data**:
   ```bash
   php artisan seed:inventory-metrics
   ```

2. **Navigate to admin dashboard**:
   ```
   http://127.0.0.1:8000/admin/dashboard
   ```

3. **Verify the metrics cards**:
   - Check that all 4 cards display the correct counts
   - Verify card colors and icons are displayed correctly
   - Confirm responsive layout on mobile/tablet/desktop

4. **View detailed inventory**:
   - Click on inventory management to see the test products
   - Verify batches are created with correct expiry dates
   - Check quantities match the test data

## Files Created

- `database/seeders/InventoryMetricsTestSeeder.php` - Main seeder class
- `app/Console/Commands/SeedInventoryMetrics.php` - Artisan command
- `database/seeders/DatabaseSeeder.php` - Updated to include new seeder

## Resetting Test Data

To remove test data and start fresh:

```bash
# Option 1: Reset entire database
php artisan migrate:fresh --seed

# Option 2: Truncate and reseed only inventory metrics
php artisan seed:inventory-metrics --fresh

# Option 3: Manually delete test products
php artisan tinker
>>> App\Models\Product::whereIn('code', ['EXP-ANT-001', 'EXP-COUGH-001', ...])->delete();
```

## Troubleshooting

### Cards show 0 for all metrics
- Verify test data was seeded: `php artisan seed:inventory-metrics`
- Check database connection is working
- Verify products table has test data: `php artisan tinker` → `App\Models\Product::count()`

### Duplicate batch numbers error
- Run with `--fresh` flag: `php artisan seed:inventory-metrics --fresh`
- Or manually truncate: `php artisan tinker` → `App\Models\Batch::truncate()`

### Dashboard not showing updated counts
- Clear cache: `php artisan cache:clear`
- Refresh browser page (Ctrl+F5 or Cmd+Shift+R)

## Next Steps

After testing with this data, you can:
1. Create additional test scenarios
2. Test inventory alerts and notifications
3. Test batch management features
4. Test stock transfer workflows
5. Test sales with low/out of stock products


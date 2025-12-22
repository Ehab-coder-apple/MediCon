# Quick Start - Inventory Metrics Testing

## 🚀 Get Started in 2 Minutes

### Step 1: Seed Test Data
```bash
php artisan seed:inventory-metrics
```

### Step 2: View Dashboard
Open browser: `http://127.0.0.1:8000/admin/dashboard`

### Step 3: See the Results
You should see 4 new cards below the user statistics:
- **Expired Products**: 3 (Red card with ⚠️)
- **Nearly Expired**: 3 (Amber card with ⏰)
- **Low Stock**: 3 (Orange card with 📉)
- **Out of Stock**: 3 (Red card with 🚫)

---

## 📋 Test Data Created

### 12 Pharmaceutical Products

**Expired Products (3)**
- Expired Antibiotic Syrup (EXP-ANT-001)
- Expired Cough Syrup (EXP-COUGH-001)
- Expired Vitamin C Tablets (EXP-VIT-001)

**Nearly Expired Products (3)**
- Nearly Expired Pain Relief (NEAR-PAIN-001)
- Nearly Expired Digestive Aid (NEAR-DIG-001)
- Nearly Expired Allergy Relief (NEAR-ALLERGY-001)

**Low Stock Products (3)**
- Low Stock Insulin Injection (LOW-INS-001)
- Low Stock Blood Pressure Monitor (LOW-BP-001)
- Low Stock Antibiotic Cream (LOW-CREAM-001)

**Out of Stock Products (3)**
- Out of Stock Cardiac Medicine (OOS-CARD-001)
- Out of Stock Migraine Relief (OOS-MIGR-001)
- Out of Stock Sleep Aid (OOS-SLEEP-001)

---

## 🎨 Dashboard Cards

| Card | Color | Icon | Metric |
|------|-------|------|--------|
| Expired Products | Red (#dc2626) | ⚠️ | Products with expired batches |
| Nearly Expired | Amber (#f59e0b) | ⏰ | Products expiring within 30 days |
| Low Stock | Orange (#f97316) | 📉 | Products below alert threshold |
| Out of Stock | Red (#ef4444) | 🚫 | Products with zero quantity |

---

## 🔧 Common Commands

### Seed Test Data
```bash
# Add test data (doesn't affect existing data)
php artisan seed:inventory-metrics

# Reset and create fresh test data
php artisan seed:inventory-metrics --fresh

# Full database reset with all data
php artisan migrate:fresh --seed
```

### Verify Test Data
```bash
php artisan tinker

# Check if test products exist
>>> App\Models\Product::where('code', 'LIKE', 'EXP-%')->count()
3

# Exit tinker
>>> exit
```

### Clear Cache
```bash
php artisan cache:clear
php artisan view:clear
```

---

## 📱 Responsive Testing

### Mobile (< 768px)
- Cards stack in 1 column
- Full width on small screens

### Tablet (768px - 1024px)
- Cards display in 2 columns
- Balanced spacing

### Desktop (> 1024px)
- Cards display in 4 columns
- Optimal layout

---

## ✅ Testing Checklist

- [ ] Seed test data: `php artisan seed:inventory-metrics`
- [ ] Navigate to admin dashboard
- [ ] See 4 inventory metric cards
- [ ] All cards show count of 3
- [ ] Cards have correct colors
- [ ] Cards have correct icons
- [ ] Test on mobile view
- [ ] Test on tablet view
- [ ] Test on desktop view
- [ ] Refresh page - metrics still show
- [ ] Clear cache - metrics still show

---

## 🐛 Troubleshooting

### Cards show 0
```bash
# Reseed the data
php artisan seed:inventory-metrics --fresh

# Clear cache
php artisan cache:clear

# Refresh browser (Ctrl+F5)
```

### Duplicate batch error
```bash
# Use --fresh flag
php artisan seed:inventory-metrics --fresh
```

### Cards not visible
```bash
# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Refresh browser
```

---

## 📚 Full Documentation

For detailed information, see:
- `INVENTORY_METRICS_TEST_DATA.md` - Test data details
- `INVENTORY_METRICS_IMPLEMENTATION_SUMMARY.md` - Implementation details
- `TESTING_INVENTORY_METRICS.md` - Complete testing guide

---

## 🎯 What Was Created

### Backend
- ✅ 4 new methods in AdminDashboardController
- ✅ Metrics calculated from database queries
- ✅ Real-time data updates

### Frontend
- ✅ 4 new statistical cards on dashboard
- ✅ Color-coded for quick identification
- ✅ Responsive design (mobile/tablet/desktop)
- ✅ Emoji icons for visual appeal

### Test Data
- ✅ 12 pharmaceutical products
- ✅ 3 products for each metric category
- ✅ Realistic batch data with expiry dates
- ✅ Easy to seed and reset

### Tools
- ✅ Artisan command: `seed:inventory-metrics`
- ✅ Optional `--fresh` flag for clean data
- ✅ Comprehensive documentation

---

## 🚀 Next Steps

1. Test the dashboard with seeded data
2. Verify metrics update when products change
3. Test on different screen sizes
4. Consider adding:
   - Click-through to detailed views
   - Historical trend charts
   - Email notifications
   - Export functionality

---

**Ready to test?** Run: `php artisan seed:inventory-metrics`


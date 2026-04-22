# 🎨 Dashboard Card Styling Guide

## Card Design Pattern

### User Metrics Cards (Top Border)
```html
<div class="p-6 rounded-lg shadow-md border-t-4 border-[COLOR] bg-white text-slate-900">
    <div class="text-3xl font-extrabold tracking-tight text-[COLOR]">{{ $metric }}</div>
    <div class="mt-2 text-sm md:text-base text-slate-600 font-semibold">Label</div>
</div>
```

### Inventory Metrics Cards (Left Border)
```html
<div class="p-6 rounded-lg shadow-md border-l-4 border-[COLOR] bg-white">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-3xl font-extrabold tracking-tight text-[COLOR]">{{ $metric }}</div>
            <div class="mt-2 text-sm md:text-base text-slate-600 font-semibold">Label</div>
        </div>
        <div class="text-4xl opacity-40">🎯</div>
    </div>
</div>
```

## Color Palette Reference

### User Metrics
```
Total Users:     border-slate-700,  text-slate-900
Admin Users:     border-emerald-600, text-emerald-600
Pharmacists:     border-cyan-600,   text-cyan-600
Sales Staff:     border-amber-600,  text-amber-600
```

### Inventory Metrics
```
Expired:         border-red-600,    text-red-600
Nearly Expired:  border-amber-600,  text-amber-600
Low Stock:       border-orange-600, text-orange-600
Out of Stock:    border-red-700,    text-red-700
```

## Button Styling Pattern

### Primary Buttons
```html
<a href="{{ route('...') }}" class="bg-slate-700 hover:bg-slate-800 text-white font-semibold py-5 px-6 rounded-xl text-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 hover:scale-105 transition-all duration-200">
    <i class="fas fa-icon text-2xl mb-2 block"></i>
    <div class="text-base font-semibold">Button Label</div>
    <div class="text-sm text-slate-100 mt-1">Description</div>
</a>
```

### Status Buttons
```
Success:    bg-emerald-600 hover:bg-emerald-700
Info:       bg-cyan-600 hover:bg-cyan-700
Warehouse:  bg-teal-600 hover:bg-teal-700
Warning:    bg-amber-600 hover:bg-amber-700
```

## WhatsApp Messaging Buttons

### WhatsApp Dashboard
```
Background: bg-emerald-600
Hover:      hover:bg-emerald-700
Icon:       fab fa-whatsapp
```

### Send Message
```
Background: bg-cyan-600
Hover:      hover:bg-cyan-700
Icon:       fas fa-paper-plane
```

### Bulk Message
```
Background: bg-teal-600
Hover:      hover:bg-teal-700
Icon:       fas fa-broadcast-tower
```

## Invoicing & Sales Buttons

### Point of Sale
```
Background: bg-slate-700
Hover:      hover:bg-slate-800
Icon:       fas fa-cash-register
```

### Invoice Management
```
Background: bg-emerald-600
Hover:      hover:bg-emerald-700
Icon:       fas fa-file-invoice
```

### Sales Reports
```
Background: bg-cyan-600
Hover:      hover:bg-cyan-700
Icon:       fas fa-chart-line
```

## System Management Buttons

### Primary Management
```
Manage Users:        bg-slate-700 hover:bg-slate-800
Manage Categories:   bg-cyan-600 hover:bg-cyan-700
Manage Subcats:      bg-teal-600 hover:bg-teal-700
Manage Locations:    bg-amber-600 hover:bg-amber-700
Manage Warehouses:   bg-emerald-600 hover:bg-emerald-700
Stock Transfers:     bg-slate-600 hover:bg-slate-700
View Reports:        bg-emerald-700 hover:bg-emerald-800
System Settings:     bg-slate-700 hover:bg-slate-800
Manage Products:     bg-amber-600 hover:bg-amber-700
```

## Responsive Grid

### User Metrics
```
Mobile:  grid-cols-1
Tablet:  md:grid-cols-2
Desktop: lg:grid-cols-4
Gap:     gap-6 lg:gap-8
```

### Inventory Metrics
```
Mobile:  grid-cols-1
Tablet:  md:grid-cols-2
Desktop: lg:grid-cols-4
Gap:     gap-6 lg:gap-8
```

### WhatsApp & Invoicing
```
Mobile:  grid-cols-1
Tablet:  md:grid-cols-3
Desktop: lg:grid-cols-3
Gap:     gap-4
```

### System Management
```
Mobile:  grid-cols-1
Tablet:  md:grid-cols-2
Desktop: lg:grid-cols-3
Gap:     gap-4
```

## Hover Effects

### Cards
```
Shadow:     shadow-md → hover:shadow-lg
Transform:  hover:-translate-y-0.5
Scale:      hover:scale-105
Duration:   transition-all duration-200
```

### Buttons
```
Shadow:     shadow-md → hover:shadow-lg
Transform:  hover:-translate-y-1
Scale:      hover:scale-105
Duration:   transition-all duration-200
```

## Text Colors

### Headings
```
Primary:   text-slate-900
Secondary: text-slate-600
```

### Buttons
```
Primary:   text-white
Secondary: text-slate-100
Tertiary:  text-slate-50
```

## Background Colors

### Cards
```
Main:      bg-white
Light:     bg-slate-50
Hover:     bg-slate-100
```

## Border Styles

### Cards
```
Top Border:    border-t-4 border-[COLOR]
Left Border:   border-l-4 border-[COLOR]
Regular:       border border-slate-200
```

## Accessibility Features

- ✅ Contrast ratios: 4.5:1 minimum
- ✅ Focus states visible
- ✅ Keyboard navigation
- ✅ Color not only indicator
- ✅ Clear visual hierarchy

## Implementation Tips

1. Use consistent spacing (p-6)
2. Use consistent shadows (shadow-md)
3. Use consistent rounded corners (rounded-lg, rounded-xl)
4. Use consistent transitions (duration-200)
5. Use consistent hover effects
6. Maintain responsive grid structure
7. Keep text hierarchy clear
8. Use semantic color meanings


# Invoice PDF Processing - Styling Quick Reference

## 🎨 Professional Color Palette

### Primary Colors
```
Slate-700:  #334155  (Primary buttons, borders, text)
Slate-900:  #0f172a  (Headings, primary text)
Slate-600:  #475569  (Secondary text)
```

### Status Colors
```
Emerald-600: #059669 (Success, completed)
Cyan-600:    #0891b2 (Information, approved)
Teal-600:    #0d9488 (Warehouse operations)
Amber-600:   #b45309 (Warnings, processing)
Orange-600:  #ea580c (Processing, alerts)
```

### Neutral Colors
```
Slate-50:   #f8fafc  (Light backgrounds)
Slate-100:  #f1f5f9  (Hover backgrounds)
Slate-200:  #e2e8f0  (Borders)
Slate-300:  #cbd5e1  (Disabled states)
White:      #ffffff  (Card backgrounds)
```

## 🎯 Component Color Usage

### Buttons
```
Primary:    bg-slate-700 hover:bg-slate-800
Success:    bg-emerald-600 hover:bg-emerald-700
Warehouse:  bg-teal-600 hover:bg-teal-700
Info:       bg-cyan-600 hover:bg-cyan-700
```

### Status Badges
```
Uploaded:              bg-slate-100 text-slate-800
Approved Processing:   bg-cyan-100 text-cyan-800
Processing:            bg-amber-100 text-amber-800
Processed:             bg-orange-100 text-orange-800
Approved Inventory:    bg-teal-100 text-teal-800
Completed:             bg-emerald-100 text-emerald-800
```

### Card Borders
```
Primary:    border-t-4 border-slate-700
PDF:        border-l-4 border-cyan-600
Items:      border-l-4 border-emerald-600
Warehouse:  border-l-4 border-teal-600
```

### Alert Boxes
```
Success:    bg-emerald-50 border-emerald-200 text-emerald-800
Info:       bg-cyan-50 border-cyan-200 text-cyan-800
Warning:    bg-amber-50 border-amber-200 text-amber-800
Error:      bg-red-50 border-red-200 text-red-800
```

## 📋 Tailwind Classes Reference

### Text Colors
```
text-slate-900    (Primary headings)
text-slate-600    (Secondary text)
text-slate-500    (Tertiary text)
text-emerald-600  (Success text)
text-cyan-800     (Info text)
text-teal-800     (Warehouse text)
```

### Background Colors
```
bg-white          (Card backgrounds)
bg-slate-50       (Light backgrounds)
bg-slate-100      (Hover backgrounds)
bg-emerald-50     (Success backgrounds)
bg-cyan-50        (Info backgrounds)
bg-teal-50        (Warehouse backgrounds)
```

### Border Colors
```
border-slate-200  (Default borders)
border-slate-700  (Primary borders)
border-cyan-600   (Info borders)
border-teal-600   (Warehouse borders)
border-emerald-600 (Success borders)
```

### Hover States
```
hover:bg-slate-800
hover:bg-emerald-700
hover:bg-teal-700
hover:border-teal-600
hover:bg-teal-50
```

## ⚡ Quick Implementation

### New Button
```html
<button class="px-6 py-3 bg-slate-700 text-white font-semibold 
               rounded-lg hover:bg-slate-800 transition duration-200">
    Action
</button>
```

### Status Badge
```html
<span class="px-3 py-1 bg-emerald-100 text-emerald-800 
             rounded-full text-sm font-semibold">
    ✅ Completed
</span>
```

### Card with Border
```html
<div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-slate-700">
    <h3 class="text-lg font-bold text-slate-900">Title</h3>
</div>
```

### Alert Box
```html
<div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
    <p class="text-emerald-800 font-semibold">Success Message</p>
</div>
```

## 🎨 Workflow Stage Colors

| Stage | Badge Color | Button Color | Border Color |
|-------|-------------|--------------|--------------|
| Uploaded | Slate | Slate-700 | Slate-700 |
| Approved | Cyan | Cyan-600 | Cyan-600 |
| Processing | Amber | Amber-600 | Amber-600 |
| Processed | Orange | Orange-600 | Orange-600 |
| Approved Inv | Teal | Teal-600 | Teal-600 |
| Completed | Emerald | Emerald-600 | Emerald-600 |

## 📱 Responsive Classes

```
grid-cols-1 md:grid-cols-2 lg:grid-cols-4
px-4 md:px-6 lg:px-8
text-sm md:text-base lg:text-lg
```

## ✨ Transitions & Effects

```
transition duration-200          (Smooth color transitions)
hover:shadow-lg                  (Hover shadow effect)
focus:outline-none focus:ring-2  (Focus states)
focus:ring-slate-700             (Focus ring color)
```

## 🔍 Accessibility Checklist

- ✅ Contrast ratio 4.5:1 minimum
- ✅ Focus states visible
- ✅ Color not only indicator
- ✅ Readable on all backgrounds
- ✅ Consistent across components

## 📚 Related Documentation

- `INVOICE_PDF_COLOR_PALETTE.md` - Detailed specifications
- `INVOICE_PDF_STYLING_IMPROVEMENTS.md` - Implementation guide
- `STYLING_BEFORE_AFTER.md` - Visual comparison


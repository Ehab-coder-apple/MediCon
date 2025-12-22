<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order {{ $purchase->reference_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 15px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 20px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .company-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .company-info h1 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 0;
            line-height: 1;
        }
        .company-info p {
            color: #666;
            font-size: 11px;
            margin-top: 2px;
        }
        .document-title {
            text-align: right;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .document-title h2 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 0;
            line-height: 1;
        }
        .document-title .ref-number {
            font-size: 13px;
            color: #666;
            font-weight: bold;
            margin-top: 2px;
        }
        .info-section {
            margin-bottom: 150px;
            width: 100%;
            page-break-inside: avoid;
        }
        .info-box {
            float: left;
            width: 48%;
            margin-right: 4%;
            box-sizing: border-box;
            margin-bottom: 0;
        }
        .info-box:last-child {
            margin-right: 0;
            float: right;
            width: 48%;
            margin-bottom: 0;
        }
        .info-box h3 {
            color: #2563eb;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 6px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .info-box p {
            font-size: 12px;
            margin-bottom: 3px;
            color: #333;
        }
        .info-box .label {
            color: #666;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            clear: both;
        }
        table thead {
            background-color: #f3f4f6;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table td {
            padding: 10px;
            font-size: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        table tbody tr:last-child td {
            border-bottom: 2px solid #2563eb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .total-box {
            width: 280px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-row.grand-total {
            border-bottom: none;
            border-top: 2px solid #2563eb;
            padding-top: 10px;
            font-weight: bold;
            font-size: 15px;
            color: #2563eb;
        }
        .notes-section {
            background-color: #f9fafb;
            padding: 12px;
            border-left: 4px solid #2563eb;
            margin-bottom: 20px;
        }
        .notes-section h3 {
            color: #2563eb;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 6px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .notes-section p {
            font-size: 12px;
            color: #333;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
            margin-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ $purchase->tenant?->pharmacy_name ?? 'MediCon' }}</h1>
            </div>
            <div class="document-title">
                <h2>Purchase Order</h2>
                <div class="ref-number">{{ $purchase->reference_number }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <h3>Supplier Information</h3>
                <p><strong>{{ $purchase->supplier->name }}</strong></p>
                <p class="label">Contact Person: {{ $purchase->supplier->contact_person }}</p>
                <p class="label">Phone: {{ $purchase->supplier->phone }}</p>
                <p class="label">Email: {{ $purchase->supplier->email }}</p>
            </div>
            <div class="info-box">
                <h3>Order Details</h3>
                <p><span class="label">Purchase Date:</span> <strong>{{ $purchase->purchase_date->format('M d, Y') }}</strong></p>
                <p><span class="label">Status:</span> <span class="status-badge status-{{ strtolower($purchase->status) }}">{{ ucfirst($purchase->status) }}</span></p>
                <p><span class="label">Created By:</span> <strong>{{ $purchase->user->name }}</strong></p>
                <p><span class="label">Created On:</span> <strong>{{ $purchase->created_at->format('M d, Y H:i') }}</strong></p>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->purchaseItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <span class="label">Code: {{ $item->product->code }}</span>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-right"><strong>${{ number_format($item->total_cost, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>${{ number_format($purchase->total_cost, 2) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span>Grand Total:</span>
                    <span>${{ number_format($purchase->total_cost, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes Section -->
        @if($purchase->notes)
        <div class="notes-section">
            <h3>Notes</h3>
            <p>{{ $purchase->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This is an automatically generated document. Generated on {{ now()->format('M d, Y H:i:s') }}</p>
            <p>MediCon Pharmacy Management System</p>
        </div>
    </div>
</body>
</html>


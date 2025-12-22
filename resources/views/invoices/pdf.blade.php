<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-info h1 {
            font-size: 28px;
            color: #007bff;
            margin-bottom: 5px;
        }
        .company-info p {
            color: #666;
            font-size: 12px;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .invoice-info p {
            font-size: 12px;
            color: #666;
        }
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .billing-column {
            flex: 1;
        }
        .billing-column h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .billing-column p {
            font-size: 12px;
            margin-bottom: 5px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table thead {
            background-color: #f5f5f5;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        table td {
            padding: 12px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }
        .summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .summary-box {
            width: 300px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 12px;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-draft {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .status-sent {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ config('app.pharmacy_name', 'MediCon Pharmacy') }}</h1>
                <p>{{ config('app.pharmacy_address', '123 Medical Center Dr') }}</p>
                <p>{{ config('app.pharmacy_phone', '+1-555-0123') }}</p>
                <p>{{ config('app.pharmacy_email', 'info@medicon.com') }}</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                @if($invoice->due_date)
                    <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                @endif
            </div>
        </div>

        <!-- Billing Info -->
        <div class="billing-section">
            <div class="billing-column">
                <h3>Bill To:</h3>
                @if($invoice->customer)
                    <p><strong>{{ $invoice->customer->name }}</strong></p>
                    @if($invoice->customer->phone)
                        <p>{{ $invoice->customer->phone }}</p>
                    @endif
                    @if($invoice->customer->email)
                        <p>{{ $invoice->customer->email }}</p>
                    @endif
                @else
                    <p><strong>Walk-in Customer</strong></p>
                @endif
                @if($invoice->delivery_address)
                    <p>{{ $invoice->delivery_address }}</p>
                @endif
            </div>
            <div class="billing-column" style="text-align: right;">
                <h3>Status:</h3>
                <p><span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></p>
                <p style="margin-top: 10px;"><strong>Payment Status:</strong></p>
                <p>{{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Batch/Expiry</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name }}</strong><br>
                            <span style="color: #999; font-size: 11px;">{{ $item->product_code }}</span>
                        </td>
                        <td>
                            @if($item->batch_number)
                                {{ $item->batch_number }}<br>
                            @endif
                            @if($item->expiry_date)
                                <span style="font-size: 11px;">Exp: {{ $item->formatted_expiry_date }}</span>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right;"><strong>${{ number_format($item->total_price, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                    <div class="summary-row">
                        <span>Discount @if($invoice->discount_percentage > 0)({{ $invoice->discount_percentage }}%)@endif:</span>
                        <span>-${{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                @endif
                @if($invoice->tax_amount > 0)
                    <div class="summary-row">
                        <span>Tax @if($invoice->tax_percentage > 0)({{ $invoice->tax_percentage }}%)@endif:</span>
                        <span>${{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                @endif
                @if($invoice->delivery_fee > 0)
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span>${{ number_format($invoice->delivery_fee, 2) }}</span>
                    </div>
                @endif
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>${{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                @if($invoice->paid_amount > 0)
                    <div class="summary-row">
                        <span>Paid:</span>
                        <span>-${{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                @endif
                @if($invoice->balance_due > 0)
                    <div class="summary-row total">
                        <span>Balance Due:</span>
                        <span>${{ number_format($invoice->balance_due, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
            <div style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border-left: 3px solid #007bff;">
                <h4 style="font-size: 12px; margin-bottom: 5px;">Notes:</h4>
                <p style="font-size: 11px;">{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top: 10px;">Generated on {{ now()->format('M d, Y g:i A') }}</p>
        </div>
    </div>
</body>
</html>


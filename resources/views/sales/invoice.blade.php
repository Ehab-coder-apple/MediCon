<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: flex-end;
        }
        .action-buttons button,
        .action-buttons a {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .btn-print {
            background-color: #6c757d;
            color: white;
        }
        .btn-print:hover {
            background-color: #5a6268;
        }
        .btn-return {
            background-color: #ff9800;
            color: white;
        }
        .btn-return:hover {
            background-color: #e68900;
        }
        @media print {
            .action-buttons {
                display: none;
            }
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .company-tagline {
            color: #666;
            font-size: 14px;
        }
        .invoice-info {
            text-align: right;
            flex: 1;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .invoice-details {
            font-size: 14px;
            color: #666;
        }
        .billing-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .billing-section {
            flex: 1;
            margin-right: 20px;
        }
        .billing-section:last-child {
            margin-right: 0;
        }
        .section-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 300px;
        }
        .totals-table td {
            padding: 8px 12px;
            border: none;
        }
        .totals-table .total-row {
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #333;
            color: #007bff;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .print-button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .invoice-container {
                border: none;
                padding: 0;
                max-width: none;
            }
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .batch-info {
            font-size: 11px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        @php
            $routePrefix = '';
            if (auth()->user() && auth()->user()->hasRole('admin')) {
                $routePrefix = 'admin.';
            } elseif (auth()->user() && auth()->user()->hasRole('pharmacist')) {
                $routePrefix = 'pharmacist.';
            }

            // Find the invoice associated with this sale
            $invoice = \App\Models\Invoice::where('invoice_number', $sale->invoice_number)->first();
        @endphp

        @if($sale->status === 'completed' && $invoice)
            <a href="{{ route($routePrefix . 'invoices.return.create', $invoice) }}" class="btn-return">
                ↶ Create Return
            </a>
        @endif

        <button class="btn-print" onclick="window.print()">🖨 Print Invoice</button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-name">MediCon Pharmacy</div>
                <div class="company-tagline">Your Health, Our Priority</div>
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    123 Medical Street, Healthcare City<br>
                    Phone: (555) 123-4567 | Email: info@medicon.com
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-details">
                    <strong>{{ $sale->invoice_number }}</strong><br>
                    Date: {{ $sale->sale_date->format('M d, Y') }}<br>
                    Time: {{ $sale->created_at->format('H:i A') }}<br>
                    <span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-info">
            <div class="billing-section">
                <div class="section-title">Bill To:</div>
                @if($sale->customer)
                    <strong>{{ $sale->customer->name }}</strong><br>
                    @if($sale->customer->phone)
                        Phone: {{ $sale->customer->phone }}<br>
                    @endif
                    @if($sale->customer->email)
                        Email: {{ $sale->customer->email }}<br>
                    @endif
                    @if($sale->customer->address)
                        {{ $sale->customer->address }}
                    @endif
                @else
                    <strong>Walk-in Customer</strong>
                @endif
            </div>
            <div class="billing-section">
                <div class="section-title">Served By:</div>
                <strong>{{ $sale->user->name }}</strong><br>
                {{ $sale->user->role->display_name ?? 'Staff' }}<br>
                MediCon Pharmacy
            </div>
            <div class="billing-section">
                <div class="section-title">Payment Info:</div>
                <strong>Method:</strong> {{ ucfirst($sale->payment_method) }}<br>
                <strong>Amount Paid:</strong> ${{ number_format($sale->paid_amount, 2) }}<br>
                <strong>Change:</strong> ${{ number_format($sale->change_amount, 2) }}
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small>Code: {{ $item->product->code }}</small>
                        @if($item->batch)
                            <br><span class="batch-info">Batch: {{ $item->batch->batch_number }} | Exp: {{ $item->batch->expiry_date->format('M Y') }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->discount_amount, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                @if($sale->discount_amount > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-${{ number_format($sale->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($sale->tax_amount > 0)
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">+${{ number_format($sale->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total:</td>
                    <td class="text-right">${{ number_format($sale->total_price, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment Summary -->
        <div class="payment-info">
            <div class="section-title">Payment Summary</div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>Total Amount:</span>
                <span><strong>${{ number_format($sale->total_price, 2) }}</strong></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>Amount Paid ({{ ucfirst($sale->payment_method) }}):</span>
                <span><strong>${{ number_format($sale->paid_amount, 2) }}</strong></span>
            </div>
            <div style="display: flex; justify-content: space-between; border-top: 1px solid #ddd; padding-top: 10px;">
                <span>Change Given:</span>
                <span><strong>${{ number_format($sale->change_amount, 2) }}</strong></span>
            </div>
        </div>

        @if($sale->notes)
        <div style="margin-bottom: 30px;">
            <div class="section-title">Notes:</div>
            <p style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 0;">
                {{ $sale->notes }}
            </p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for choosing MediCon Pharmacy!</strong></p>
            <p>For any queries regarding this invoice, please contact us at (555) 123-4567</p>
            <p style="margin-top: 15px; font-size: 10px;">
                This is a computer-generated invoice. No signature required.<br>
                Generated on {{ now()->format('M d, Y \a\t H:i A') }}
            </p>
        </div>
    </div>

    <script>
        // Auto-print functionality (optional)
        function autoPrint() {
            if (confirm('Would you like to print this invoice?')) {
                window.print();
            }
        }
        
        // Uncomment the line below to auto-print when page loads
        // window.onload = autoPrint;
    </script>
</body>
</html>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('invoices.index') }}" class="mr-4 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-file-invoice text-blue-500 mr-2"></i>
                    Invoice {{ $invoice->invoice_number }}
                </h2>
            </div>
            <div class="flex space-x-3">
                @if($invoice->status === 'draft')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-paper-plane mr-1"></i>
                            Mark as Sent
                        </button>
                    </form>
                @endif

                @if($invoice->payment_status !== 'paid' && $invoice->status !== 'cancelled')
                    <button type="button" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm transition-colors payment-btn"
                            data-invoice-id="{{ $invoice->id }}" data-balance="{{ $invoice->balance_due }}">
                        <i class="fas fa-credit-card mr-1"></i>
                        Add Payment
                    </button>
                @endif

                @if($invoice->customer && $invoice->customer->phone && $invoice->status !== 'cancelled')
                    <form method="POST" action="{{ route('invoices.send-whatsapp', $invoice) }}" class="inline"
                          onsubmit="return confirm('Send this invoice to {{ $invoice->customer->name }} via WhatsApp?')">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fab fa-whatsapp mr-1"></i>
                            Send via WhatsApp
                        </button>
                    </form>
                @endif

                @if($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                    <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" class="inline"
                          onsubmit="return confirm('Are you sure you want to cancel this invoice?')">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-ban mr-1"></i>
                            Cancel
                        </button>
                    </form>
                @endif

                @if($invoice->status === 'completed' || $invoice->payment_status === 'paid')
                    @php
                        $routePrefix = '';
                        if (auth()->user()->hasRole('admin')) {
                            $routePrefix = 'admin.';
                        } elseif (auth()->user()->hasRole('pharmacist')) {
                            $routePrefix = 'pharmacist.';
                        }
                    @endphp
                    <a href="{{ route($routePrefix . 'invoices.return.create', $invoice) }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                        <i class="fas fa-undo mr-1"></i>
                        Create Return
                    </a>
                @endif

                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition-colors" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i>
                    Print
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Invoice Header -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-4">
                <div class="p-6">
                    <!-- Title and Status Row -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">INVOICE</h1>
                            <p class="text-sm text-gray-600">{{ $invoice->invoice_number }}</p>
                        </div>
                        <div class="text-right space-y-1">
                            <div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $invoice->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $invoice->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $invoice->status_color === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $invoice->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $invoice->status_color === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $invoice->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </div>
                            <div>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $invoice->payment_status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $invoice->payment_status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $invoice->payment_status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $invoice->payment_status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- From and To in compact format -->
                    <div class="grid grid-cols-2 gap-6 mb-4">
                        <!-- From -->
                        <div>
                            <h4 class="text-xs font-semibold text-gray-900 uppercase mb-1">From:</h4>
                            <div class="text-xs text-gray-700 leading-tight">
                                <p class="font-medium">{{ config('app.pharmacy_name', 'MediCon Pharmacy') }}</p>
                                <p>{{ config('app.pharmacy_address', '123 Medical Center Dr') }}</p>
                                <p>{{ config('app.pharmacy_phone', '+1-555-0123') }}</p>
                                <p>{{ config('app.pharmacy_email', 'info@medicon.com') }}</p>
                            </div>
                        </div>

                        <!-- To -->
                        <div>
                            <h4 class="text-xs font-semibold text-gray-900 uppercase mb-1">To:</h4>
                            <div class="text-xs text-gray-700 leading-tight">
                                @if($invoice->customer)
                                    <p class="font-medium">{{ $invoice->customer->name }}</p>
                                    @if($invoice->customer->phone)
                                        <p>{{ $invoice->customer->phone }}</p>
                                    @endif
                                    @if($invoice->customer->email)
                                        <p>{{ $invoice->customer->email }}</p>
                                    @endif
                                    @if($invoice->delivery_address)
                                        <p class="mt-1">{{ $invoice->delivery_address }}</p>
                                    @endif
                                @else
                                    <p class="font-medium">Walk-in Customer</p>
                                    @if($invoice->delivery_address)
                                        <p class="mt-1">{{ $invoice->delivery_address }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details in compact format -->
                    <div class="grid grid-cols-4 gap-4 text-xs">
                        <div>
                            <h4 class="font-semibold text-gray-900">Invoice Date</h4>
                            <p class="text-gray-700">{{ $invoice->invoice_date->format('M d, Y') }}</p>
                        </div>
                        @if($invoice->due_date)
                            <div>
                                <h4 class="font-semibold text-gray-900">Due Date</h4>
                                <p class="text-gray-700 {{ $invoice->is_overdue ? 'text-red-600 font-medium' : '' }}">
                                    {{ $invoice->due_date->format('M d, Y') }}
                                    @if($invoice->is_overdue)
                                        <span class="block text-xs">({{ abs($invoice->days_until_due) }} days overdue)</span>
                                    @elseif($invoice->payment_status !== 'paid')
                                        <span class="block text-xs">({{ $invoice->days_until_due }} days left)</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                        <div>
                            <h4 class="font-semibold text-gray-900">Delivery</h4>
                            <p class="text-gray-700">{{ ucfirst($invoice->delivery_method) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Batch/Expiry
                                </th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Qty
                                </th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Unit Price
                                </th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-2">
                                        <div>
                                            <div class="text-xs font-medium text-gray-900">{{ $item->product_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product_code }}</div>
                                            @if($item->product_description)
                                                <div class="text-xs text-gray-400">{{ $item->product_description }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs">
                                        @if($item->batch_number)
                                            <div class="text-gray-900">{{ $item->batch_number }}</div>
                                        @endif
                                        @if($item->expiry_date)
                                            <div class="{{ $item->is_expired ? 'text-red-600' : 'text-gray-500' }}">
                                                Exp: {{ $item->formatted_expiry_date }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-center text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-right text-gray-900">
                                        ${{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-right text-gray-900">
                                        ${{ number_format($item->total_price, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-end">
                        <div class="w-full max-w-sm">
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">${{ number_format($invoice->subtotal, 2) }}</span>
                                </div>

                                @if($invoice->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">
                                            Discount
                                            @if($invoice->discount_percentage > 0)
                                                ({{ $invoice->discount_percentage }}%)
                                            @endif:
                                        </span>
                                        <span class="font-medium text-red-600">-${{ number_format($invoice->discount_amount, 2) }}</span>
                                    </div>
                                @endif

                                @if($invoice->tax_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">
                                            Tax
                                            @if($invoice->tax_percentage > 0)
                                                ({{ $invoice->tax_percentage }}%)
                                            @endif:
                                        </span>
                                        <span class="font-medium">${{ number_format($invoice->tax_amount, 2) }}</span>
                                    </div>
                                @endif

                                @if($invoice->delivery_fee > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Delivery Fee:</span>
                                        <span class="font-medium">${{ number_format($invoice->delivery_fee, 2) }}</span>
                                    </div>
                                @endif

                                <div class="border-t border-gray-300 my-1 pt-1">
                                    <div class="flex justify-between font-bold text-base">
                                        <span>Total:</span>
                                        <span>${{ number_format($invoice->total_amount, 2) }}</span>
                                    </div>
                                </div>

                                @if($invoice->paid_amount > 0)
                                    <div class="flex justify-between text-green-600 text-sm">
                                        <span>Paid:</span>
                                        <span>-${{ number_format($invoice->paid_amount, 2) }}</span>
                                    </div>
                                @endif

                                @if($invoice->balance_due > 0)
                                    <div class="flex justify-between font-bold text-base {{ $invoice->is_overdue ? 'text-red-600' : 'text-blue-600' }}">
                                        <span>Balance Due:</span>
                                        <span>${{ number_format($invoice->balance_due, 2) }}</span>
                                    </div>
                                @endif
                            </div>

                            @if($invoice->payment_method)
                                <div class="mt-3 pt-2 border-t border-gray-200">
                                    <div class="text-xs text-gray-600 space-y-0.5">
                                        <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</p>
                                        @if($invoice->payment_reference)
                                            <p><strong>Reference:</strong> {{ $invoice->payment_reference }}</p>
                                        @endif
                                        @if($invoice->paid_at)
                                            <p><strong>Paid On:</strong> {{ $invoice->paid_at->format('M d, Y g:i A') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($invoice->notes)
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <h4 class="font-medium text-gray-900 text-sm mb-1">Notes:</h4>
                            <p class="text-gray-700 text-xs">{{ $invoice->notes }}</p>
                        </div>
                    @endif

                    @if($invoice->terms_conditions)
                        <div class="mt-3">
                            <h4 class="font-medium text-gray-900 text-sm mb-1">Terms & Conditions:</h4>
                            <p class="text-xs text-gray-600">{{ $invoice->terms_conditions }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Process Payment</h3>
                <form id="paymentForm" method="POST" action="{{ route('invoices.process-payment', $invoice) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount to Pay</label>
                        <input type="number" name="payment_amount" id="paymentAmount" 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               step="0.01" min="0.01" max="{{ $invoice->balance_due }}" 
                               value="{{ $invoice->balance_due }}" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="payment_method" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="insurance">Insurance</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference (Optional)</label>
                        <input type="text" name="payment_reference" 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Transaction reference...">
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancelPayment" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            Process Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentModal = document.getElementById('paymentModal');
            const cancelPayment = document.getElementById('cancelPayment');
            const paymentBtn = document.querySelector('.payment-btn');

            // Open payment modal
            if (paymentBtn) {
                paymentBtn.addEventListener('click', function() {
                    paymentModal.classList.remove('hidden');
                });
            }

            // Close payment modal
            cancelPayment.addEventListener('click', function() {
                paymentModal.classList.add('hidden');
            });

            // Close modal when clicking outside
            paymentModal.addEventListener('click', function(e) {
                if (e.target === paymentModal) {
                    paymentModal.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

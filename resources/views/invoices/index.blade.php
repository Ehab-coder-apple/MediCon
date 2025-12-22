<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="mr-4 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-file-invoice text-blue-500 mr-2"></i>
                    {{ __('Invoice Management') }}
                </h2>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('invoices.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                    <i class="fas fa-plus mr-1"></i>
                    New Invoice
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Invoice number, customer...">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="viewed" {{ request('status') === 'viewed' ? 'selected' : '' }}>Viewed</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                            <select name="payment_status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Payment Status</option>
                                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                            <select name="customer_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                <i class="fas fa-search mr-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('invoices.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                <i class="fas fa-times mr-1"></i>
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    @if($invoices->count() > 0)
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Invoice
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Payment
                                    </th>
                                    <th class="sticky right-0 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider z-10">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $invoice->invoice_number }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $invoice->items->count() }} items
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $invoice->customer ? $invoice->customer->name : 'Walk-in Customer' }}
                                            </div>
                                            @if($invoice->customer && $invoice->customer->phone)
                                                <div class="text-sm text-gray-500">
                                                    {{ $invoice->customer->phone }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $invoice->invoice_date->format('M d, Y') }}
                                            </div>
                                            @if($invoice->due_date)
                                                <div class="text-sm text-gray-500">
                                                    Due: {{ $invoice->due_date->format('M d, Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                ${{ number_format($invoice->total_amount, 2) }}
                                            </div>
                                            @if($invoice->paid_amount > 0)
                                                <div class="text-sm text-gray-500">
                                                    Paid: ${{ number_format($invoice->paid_amount, 2) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $invoice->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $invoice->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $invoice->status_color === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $invoice->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $invoice->status_color === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                                {{ $invoice->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $invoice->payment_status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $invoice->payment_status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $invoice->payment_status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $invoice->payment_status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}
                                            </span>
                                            @if($invoice->is_overdue)
                                                <div class="text-xs text-red-600 mt-1">
                                                    {{ abs($invoice->days_until_due) }} days overdue
                                                </div>
                                            @elseif($invoice->due_date && $invoice->payment_status !== 'paid')
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $invoice->days_until_due }} days left
                                                </div>
                                            @endif
                                        </td>
                                        <td class="sticky right-0 bg-white px-6 py-4 whitespace-nowrap text-sm font-medium z-10 border-l border-gray-200">
                                            <div class="flex flex-wrap gap-3">
                                                <!-- View Button -->
                                                <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900 hover:underline font-semibold">View</a>

                                                <!-- Edit Button (Draft only) -->
                                                @if($invoice->status === 'draft')
                                                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-green-600 hover:text-green-900 hover:underline font-semibold">Edit</a>
                                                @endif

                                                <!-- WhatsApp Button (Has customer with phone) -->
                                                @if($invoice->customer && $invoice->customer->phone && $invoice->status !== 'cancelled')
                                                    <form method="POST" action="{{ route('invoices.send-whatsapp', $invoice) }}" class="inline" onsubmit="return confirm('Send this invoice to {{ $invoice->customer->name }} via WhatsApp?')">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900 hover:underline font-semibold">WhatsApp</button>
                                                    </form>
                                                @endif

                                                <!-- Payment Button (Unpaid only) -->
                                                @if($invoice->payment_status !== 'paid')
                                                    <button type="button" class="text-purple-600 hover:text-purple-900 hover:underline font-semibold payment-btn" data-invoice-id="{{ $invoice->id }}" data-balance="{{ $invoice->balance_due }}">Payment</button>
                                                @endif

                                                <!-- Delete Button (Draft only) -->
                                                @if($invoice->status === 'draft')
                                                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this invoice?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 hover:underline font-semibold">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $invoices->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <div class="w-12 h-12 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-file-invoice text-gray-400 text-xl"></i>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">No invoices found</h3>
                            <p class="text-sm text-gray-500 mb-4">Get started by creating your first invoice</p>
                            <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>
                                Create Invoice
                            </a>
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
                <form id="paymentForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount to Pay</label>
                        <input type="number" name="payment_amount" id="paymentAmount" 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               step="0.01" min="0.01" required>
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
            const paymentForm = document.getElementById('paymentForm');
            const paymentAmount = document.getElementById('paymentAmount');
            const cancelPayment = document.getElementById('cancelPayment');
            const paymentBtns = document.querySelectorAll('.payment-btn');

            // Open payment modal
            paymentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const invoiceId = this.dataset.invoiceId;
                    const balance = parseFloat(this.dataset.balance);
                    
                    paymentForm.action = `/invoices/${invoiceId}/payment`;
                    paymentAmount.value = balance.toFixed(2);
                    paymentAmount.max = balance.toFixed(2);
                    
                    paymentModal.classList.remove('hidden');
                });
            });

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

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $customer->name }}
            </h2>
            <div class="space-x-2">
                @php
                    $routePrefix = auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isPharmacist() ? 'pharmacist' : 'sales-staff');
                @endphp
                <a href="{{ route($routePrefix . '.customers.edit', $customer) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Customer
                </a>
                <a href="{{ route($routePrefix . '.customers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Customers
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Customer Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Customer Details
                    </h1>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Customer Name</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $customer->name }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Phone</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $customer->phone ?? 'Not provided' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Email</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $customer->email ?? 'Not provided' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Customer Since</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $customer->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    
                    <!-- Customer Statistics -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $customer->sales->count() }}</div>
                            <div class="text-sm text-blue-800">Total Sales</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">${{ number_format($customer->total_purchase_amount, 2) }}</div>
                            <div class="text-sm text-green-800">Total Spent</div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Tabs for History -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex">
                        <button class="tab-button active py-4 px-6 text-sm font-medium text-blue-600 border-b-2 border-blue-600" data-tab="sales">
                            Purchase History ({{ $customer->sales->count() }})
                        </button>
                    </nav>
                </div>

                <!-- Sales History Tab -->
                <div id="sales-tab" class="tab-content p-6 lg:p-8">
                    <h2 class="text-xl font-medium text-gray-900 mb-4">Purchase History</h2>
                    
                    @if($customer->sales->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Invoice
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Served By
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($customer->sales as $sale)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $sale->sale_date->format('M d, Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $sale->created_at->format('H:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $sale->total_products }} items</div>
                                            <div class="text-sm text-gray-500">{{ $sale->total_quantity }} qty</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-green-600">${{ number_format($sale->total_price, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ ucfirst($sale->payment_method) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $sale->user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route($routePrefix . '.sales.show', $sale) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                            <a href="{{ route($routePrefix . '.sales.invoice', $sale) }}" class="text-green-600 hover:text-green-900" target="_blank">Invoice</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No purchases found for this customer.</p>
                        </div>
                    @endif
                </div>


            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.dataset.tab;
                    
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
                        btn.classList.add('text-gray-500', 'border-transparent');
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active', 'text-blue-600', 'border-blue-600');
                    this.classList.remove('text-gray-500', 'border-transparent');
                    
                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Show selected tab content
                    document.getElementById(tabName + '-tab').classList.remove('hidden');
                });
            });
        });
    </script>
</x-app-layout>

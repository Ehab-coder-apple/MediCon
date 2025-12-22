<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center py-4 px-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sales') }}
            </h2>
            <div class="ml-12">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.sales.create') : (auth()->user()->isPharmacist() ? route('pharmacist.sales.create') : route('sales-staff.sales.create')) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-sm transition-colors">
                    💰 New Sale
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Today's Sales Summary -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Today's Sales Summary</h3>
                </div>
                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6" id="todaysSummary">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600" id="todaysSalesCount">-</div>
                            <div class="text-sm text-gray-500">Total Sales</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600" id="todaysRevenue">-</div>
                            <div class="text-sm text-gray-500">Revenue</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600" id="todaysItems">-</div>
                            <div class="text-sm text-gray-500">Items Sold</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600" id="todaysCustomers">-</div>
                            <div class="text-sm text-gray-500">Total Customers</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Sales Management
                    </h1>
                    <p class="mt-2 text-gray-500">
                        View and manage all sales transactions
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
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
                                        Items
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Payment
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
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
                                @forelse($sales as $sale)
                                <tr class="{{ $sale->status === 'completed' ? 'bg-green-50' : ($sale->status === 'cancelled' ? 'bg-red-50' : 'bg-yellow-50') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $sale->created_at->format('H:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($sale->customer)
                                            <div class="text-sm text-gray-900">{{ $sale->customer->name }}</div>
                                            @if($sale->customer->phone)
                                                <div class="text-sm text-gray-500">{{ $sale->customer->phone }}</div>
                                            @endif
                                        @else
                                            <div class="text-sm text-gray-500">Walk-in Customer</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sale->sale_date->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sale->sale_items_count }} items</div>
                                        <div class="text-sm text-gray-500">{{ $sale->total_quantity }} qty</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-green-600">${{ number_format($sale->total_price, 2) }}</div>
                                        @if($sale->discount_amount > 0)
                                            <div class="text-sm text-red-500">-${{ number_format($sale->discount_amount, 2) }} discount</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst($sale->payment_method) }}</div>
                                        <div class="text-sm text-gray-500">Paid: ${{ number_format($sale->paid_amount, 2) }}</div>
                                        @if($sale->change_amount > 0)
                                            <div class="text-sm text-blue-500">Change: ${{ number_format($sale->change_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($sale->status === 'completed') bg-green-100 text-green-800
                                            @elseif($sale->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sale->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $sale->user->role->display_name ?? 'Staff' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @php
                                            $routePrefix = auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isPharmacist() ? 'pharmacist' : 'sales-staff');
                                        @endphp
                                        <a href="{{ route($routePrefix . '.sales.show', $sale) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="{{ route($routePrefix . '.sales.invoice', $sale) }}" class="text-green-600 hover:text-green-900 mr-3" target="_blank">Invoice</a>
                                        @if($sale->status === 'pending')
                                            <a href="{{ route($routePrefix . '.sales.edit', $sale) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route($routePrefix . '.sales.destroy', $sale) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Are you sure you want to delete this sale?')">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                        No sales found. <a href="{{ auth()->user()->isAdmin() ? route('admin.sales.create') : (auth()->user()->isPharmacist() ? route('pharmacist.sales.create') : route('sales-staff.sales.create')) }}" class="text-blue-600 hover:text-blue-900">Create your first sale</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $sales->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load today's sales summary
        document.addEventListener('DOMContentLoaded', function() {
            loadTodaysSummary();
        });

        async function loadTodaysSummary() {
            try {
                const routePrefix = '{{ auth()->user()->isAdmin() ? "admin" : (auth()->user()->isPharmacist() ? "pharmacist" : "sales-staff") }}';
                const response = await fetch(`/${routePrefix}/sales-summary/today`);
                const data = await response.json();

                document.getElementById('todaysSalesCount').textContent = data.total_sales;
                document.getElementById('todaysRevenue').textContent = '$' + parseFloat(data.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById('todaysItems').textContent = data.total_items || 0;
                document.getElementById('todaysCustomers').textContent = data.total_customers || 0;
            } catch (error) {
                console.error('Error loading today\'s summary:', error);
                document.getElementById('todaysSalesCount').textContent = '0';
                document.getElementById('todaysRevenue').textContent = '$0.00';
                document.getElementById('todaysItems').textContent = '0';
                document.getElementById('todaysCustomers').textContent = '0';
            }
        }
    </script>
</x-app-layout>

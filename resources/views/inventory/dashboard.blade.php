<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Inventory Dashboard') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('inventory.alerts') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    View Alerts
                </a>
                <a href="{{ route('inventory.report') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Full Report
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $totalProducts }}</div>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">Total Products</div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-green-600">{{ $activeBatches }}</div>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">Active Batches</div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-purple-600">${{ number_format($totalValue, 2) }}</div>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">Inventory Value</div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-red-600">{{ $lowStockProducts->count() }}</div>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">Low Stock Alerts</div>
                </div>
            </div>

            <!-- Alerts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Low Stock Products -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-red-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-red-800">Low Stock Products</h3>
                    </div>
                    <div class="p-6">
                        @if($lowStockProducts->count() > 0)
                            <div class="space-y-3">
                                @foreach($lowStockProducts->take(5) as $product)
                                <div class="flex justify-between items-center p-3 bg-red-50 rounded">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->code }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-red-600">{{ $product->active_quantity }}</div>
                                        <div class="text-xs text-gray-500">Alert: {{ $product->alert_quantity }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @if($lowStockProducts->count() > 5)
                                <div class="mt-4 text-center">
                                    <a href="{{ route('inventory.alerts') }}" class="text-blue-600 hover:text-blue-900">
                                        View all {{ $lowStockProducts->count() }} low stock products
                                    </a>
                                </div>
                            @endif
                        @else
                            <p class="text-gray-500 text-center py-4">No low stock products</p>
                        @endif
                    </div>
                </div>

                <!-- Expiring Soon Batches -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-yellow-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-yellow-800">Expiring Soon</h3>
                    </div>
                    <div class="p-6">
                        @if($expiringSoonBatches->count() > 0)
                            <div class="space-y-3">
                                @foreach($expiringSoonBatches as $batch)
                                <div class="flex justify-between items-center p-3 bg-yellow-50 rounded">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $batch->product->name }}</div>
                                        <div class="text-sm text-gray-500">Batch: {{ $batch->batch_number }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-yellow-600">{{ $batch->days_until_expiry }} days</div>
                                        <div class="text-xs text-gray-500">Qty: {{ $batch->quantity }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">No batches expiring soon</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Expired Batches -->
            @if($expiredBatches->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 bg-red-100 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-red-800">Expired Batches</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expired</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($expiredBatches as $batch)
                                <tr class="bg-red-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $batch->product->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $batch->batch_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-red-600">{{ abs($batch->days_until_expiry) }} days ago</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $batch->quantity }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Out of Stock Products -->
            @if($outOfStockProducts->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-gray-100 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800">Out of Stock Products</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($outOfStockProducts as $product)
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                            <div class="text-sm text-gray-500">{{ $product->code }}</div>
                            <div class="text-sm text-red-600 mt-1">Out of Stock</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

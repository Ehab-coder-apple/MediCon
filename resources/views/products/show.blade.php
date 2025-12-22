<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $product->name }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                }
            @endphp
            <div class="space-x-2">
                <a href="{{ route('admin.stock-receiving.quick-add', $product) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    ➕ Add Stock
                </a>
                <a href="{{ route($routePrefix . 'products.edit', $product) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Product
                </a>
                <a href="{{ route($routePrefix . 'products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Products
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Product Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Product Details
                    </h1>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Product Name</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $product->name }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Category</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $product->category }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Product Code</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $product->code }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Cost Price</h3>
                            <p class="mt-1 text-lg text-gray-900">${{ number_format($product->cost_price, 2) }}</p>
                            <p class="text-xs text-gray-500">Original purchase price</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Net Price</h3>
                            <p class="mt-1 text-lg text-blue-600">${{ number_format($product->net_price, 2) }}</p>
                            <p class="text-xs text-gray-500">After bonus adjustments</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Selling Price</h3>
                            <p class="mt-1 text-lg text-green-600 font-semibold">${{ number_format($product->selling_price, 2) }}</p>
                            <p class="text-xs text-gray-500">Customer invoice price</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Alert Quantity</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $product->alert_quantity }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Stock</h3>
                            <p class="mt-1 text-lg {{ $product->is_low_stock ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $product->active_quantity }}
                                @if($product->is_low_stock)
                                    <span class="text-sm text-red-500">(Low Stock!)</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">On-Shelf (Sellable) Stock</h3>
                            <p class="mt-1 text-lg text-emerald-600">
                                {{ $product->on_shelf_quantity }}
                            </p>
                            <p class="text-xs text-gray-500">Available for sale from On Shelf and other sellable warehouses</p>
                        </div>


                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1">
                                @if($product->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Storage Location</h3>
                            @if($product->location)
                                <p class="mt-1 text-lg text-gray-900">
                                    📍 {{ $product->location->full_location }}
                                </p>
                                <div class="mt-2 text-xs text-gray-500 space-y-1">
                                    <div><strong>Zone:</strong> {{ $product->location->zone }}</div>
                                    @if($product->location->cabinet_shelf)
                                        <div><strong>Cabinet/Shelf:</strong> {{ $product->location->cabinet_shelf }}</div>
                                    @endif
                                    @if($product->location->row_level)
                                        <div><strong>Row/Level:</strong> {{ $product->location->row_level }}</div>
                                    @endif
                                    @if($product->location->position_side)
                                        <div><strong>Position/Side:</strong> {{ $product->location->position_side }}</div>
                                    @endif
                                </div>
                                @if($product->location->description)
                                    <p class="mt-2 text-sm text-gray-600">{{ $product->location->description }}</p>
                                @endif
                            @else
                                <p class="mt-1 text-lg text-gray-400">No location assigned</p>
                                <p class="text-xs text-gray-500">Assign a location when editing this product</p>
                            @endif
                        </div>
                    </div>

                    <!-- Profit Analysis Section -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">💰 Profit Analysis</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white rounded-lg p-4 border">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Based on Cost Price</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Profit per unit:</span>
                                        <span class="text-sm font-medium text-gray-900">${{ number_format($product->cost_profit_per_unit, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Profit margin:</span>
                                        <span class="text-sm font-medium {{ $product->cost_profit_margin >= 20 ? 'text-green-600' : ($product->cost_profit_margin >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ number_format($product->cost_profit_margin, 1) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <h4 class="text-sm font-medium text-blue-700 mb-2">Based on Net Price (Recommended)</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Profit per unit:</span>
                                        <span class="text-sm font-medium text-blue-600">${{ number_format($product->net_profit_per_unit, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Profit margin:</span>
                                        <span class="text-sm font-medium {{ $product->net_profit_margin >= 20 ? 'text-green-600' : ($product->net_profit_margin >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ number_format($product->net_profit_margin, 1) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($product->net_price < $product->cost_price)
                            <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <span class="text-green-500">🎁</span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-700">
                                            <strong>Bonus Benefit:</strong> Your net cost is lower than the original cost price due to bonus quantities received!
                                            Savings: ${{ number_format($product->cost_price - $product->net_price, 2) }} per unit
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

            <!-- Warehouse Stock Breakdown -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Warehouse Stock Breakdown
                    </h1>
                </div>

                <div class="p-6 lg:p-8">
                    @php
                        $warehouseStocks = $product->warehouseStocks
                            ->where('quantity', '>', 0)
                            ->sortByDesc('quantity');
                    @endphp

                    @if($warehouseStocks->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Warehouse
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sellable?
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($warehouseStocks as $stock)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ optional($stock->warehouse)->name ?? 'Unknown' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ optional(optional($stock->warehouse)->branch)->name ?? 'All Branches' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ optional($stock->warehouse)->type ? ucfirst(str_replace('_', ' ', $stock->warehouse->type)) : 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if(optional($stock->warehouse)->is_sellable)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Yes
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        No
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $stock->quantity }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No warehouse stock records found for this product yet.</p>
                    @endif
                </div>
            </div>


                    @if($product->description)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500">Description</h3>
                            <p class="mt-1 text-gray-900">{{ $product->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Batches Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-medium text-gray-900">
                            Product Batches
                        </h1>
                        <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Add New Batch
                        </button>
                    </div>
                </div>

                <div class="p-6 lg:p-8">
                    @if($product->batches->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Batch Number
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Expiry Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($product->batches as $batch)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $batch->quantity }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $batch->expiry_date->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $batch->days_until_expiry }} days</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($batch->is_expired)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Expired
                                                </span>
                                            @elseif($batch->is_expiring_soon)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Expiring Soon
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            <button class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No batches found for this product.</p>
                            <button class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Add First Batch
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

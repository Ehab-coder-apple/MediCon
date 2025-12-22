<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Batch: {{ $batch->batch_number }}
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
                <a href="{{ route($routePrefix . 'batches.edit', $batch) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Batch
                </a>
                <a href="{{ route($routePrefix . 'batches.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Batches
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Batch Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Batch Details
                    </h1>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Product</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->product->name }}</p>
                            <p class="text-sm text-gray-500">{{ $batch->product->code }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Batch Number</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->batch_number }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Quantity</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->quantity }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Expiry Date</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->expiry_date->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-500">
                                @if($batch->is_expired)
                                    Expired {{ abs($batch->days_until_expiry) }} days ago
                                @else
                                    {{ $batch->days_until_expiry }} days remaining
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Cost Price</h3>
                            <p class="mt-1 text-lg text-gray-900">
                                @if($batch->cost_price)
                                    ${{ number_format($batch->cost_price, 2) }}
                                @else
                                    ${{ number_format($batch->product->cost_price, 2) }} <span class="text-sm text-gray-500">(default)</span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1">
                                @if($batch->is_expired)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Expired
                                    </span>
                                @elseif($batch->is_expiring_soon)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Expiring Soon
                                    </span>
                                @elseif($batch->quantity == 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Out of Stock
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->created_at->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $batch->created_at->diffForHumans() }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->updated_at->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $batch->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Related Product Information
                    </h1>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Product Name</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->product->name }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Category</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->product->category }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Selling Price</h3>
                            <p class="mt-1 text-lg text-gray-900">${{ number_format($batch->product->selling_price, 2) }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Alert Quantity</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $batch->product->alert_quantity }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Product Stock</h3>
                            <p class="mt-1 text-lg {{ $batch->product->is_low_stock ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $batch->product->active_quantity }}
                                @if($batch->product->is_low_stock)
                                    <span class="text-sm text-red-500">(Low Stock!)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($batch->product->description)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500">Product Description</h3>
                            <p class="mt-1 text-gray-900">{{ $batch->product->description }}</p>
                        </div>
                    @endif

                    <div class="mt-6">
                        @php
                            $routePrefix = '';
                            if (auth()->user()->hasRole('admin')) {
                                $routePrefix = 'admin.';
                            } elseif (auth()->user()->hasRole('pharmacist')) {
                                $routePrefix = 'pharmacist.';
                            }
                        @endphp
                        <a href="{{ route($routePrefix . 'products.show', $batch->product) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            View Full Product Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

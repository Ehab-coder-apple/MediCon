<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Warehouse Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">{{ $warehouse->name }}</h1>
                        <p class="mt-1 text-gray-500">{{ ucfirst(str_replace('_', ' ', $warehouse->type)) }} warehouse</p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                            Edit
                        </a>
                        <a href="{{ route('admin.warehouses.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded">
                            Back
                        </a>
                    </div>
                </div>

                <div class="p-6 lg:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">General Information</h3>
                        <p><span class="font-medium text-gray-700">Name:</span> {{ $warehouse->name }}</p>
                        <p><span class="font-medium text-gray-700">Type:</span> {{ ucfirst(str_replace('_', ' ', $warehouse->type)) }}</p>
                        <p><span class="font-medium text-gray-700">Branch:</span> {{ $warehouse->branch?->name ?? 'All Branches' }}</p>
                        <p><span class="font-medium text-gray-700">Sellable:</span> 
                            @if($warehouse->is_sellable)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Yes</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No</span>
                            @endif
                        </p>
                        <p><span class="font-medium text-gray-700">System Warehouse:</span> 
                            @if($warehouse->is_system)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">System</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Custom</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Notes / Specifications</h3>
                        <div class="p-4 bg-gray-50 rounded border border-gray-200 text-sm text-gray-700 whitespace-pre-line">
                            {{ $warehouse->specifications ?: 'No additional notes provided.' }}
                        </div>
                    </div>
                </div>

                <div class="p-6 lg:p-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Stock in this Warehouse</h3>

                    @if($warehouse->stocks->isEmpty())
                        <p class="text-sm text-gray-500">No stock is currently assigned to this warehouse.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Expiry</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($warehouse->stocks as $stock)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $stock->product?->name }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $stock->batch?->batch_number ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ optional($stock->batch?->expiry_date)->format('Y-m-d') ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $stock->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


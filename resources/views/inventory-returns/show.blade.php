<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventory Return: {{ $inventoryReturn->reference_number }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route($routePrefix . 'inventory-returns.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Inventory Returns
                </a>
                @if($inventoryReturn->status === 'pending')
                    <form action="{{ route($routePrefix . 'inventory-returns.destroy', $inventoryReturn) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this inventory return?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Return Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Inventory Return Details
                    </h1>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Reference Number</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $inventoryReturn->reference_number }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Return Date</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $inventoryReturn->return_date->format('M d, Y') }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Cost</h3>
                            <p class="mt-1 text-2xl font-bold text-green-600">${{ number_format($inventoryReturn->total_cost, 2) }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1">
                                @if($inventoryReturn->status === 'pending')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Pending</span>
                                @elseif($inventoryReturn->status === 'approved')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">Approved</span>
                                @elseif($inventoryReturn->status === 'completed')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Completed</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Cancelled</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Warehouse</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $inventoryReturn->warehouse->name }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Supplier</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $inventoryReturn->supplier->name }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created By</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $inventoryReturn->user->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Return Items -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Returned Items ({{ $inventoryReturn->total_items }})
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Product</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Batch</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Quantity</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Total Cost</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Reason</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryReturn->items as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $item->product->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->batch_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">${{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-semibold">${{ number_format($item->total_cost, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                            {{ str_replace('_', ' ', ucfirst($item->reason)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Additional Notes -->
            @if($inventoryReturn->notes)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Additional Notes</h2>
                    </div>
                    <div class="p-6 lg:p-8">
                        <p class="text-gray-700">{{ $inventoryReturn->notes }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>


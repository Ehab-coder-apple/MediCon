<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventory Returns
            </h2>
            <div class="space-x-2">
                <a href="{{ route($routePrefix . 'inventory-returns.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Return from Inventory
                </a>
                <a href="{{ route($routePrefix . 'purchase-returns.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Purchase Returns
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @if($inventoryReturns->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Reference</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Warehouse</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Supplier</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Return Date</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Items</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Total Cost</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventoryReturns as $return)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $return->reference_number }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $return->warehouse->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $return->supplier->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $return->return_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $return->total_items }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 font-semibold">${{ number_format($return->total_cost, 2) }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($return->status === 'pending')
                                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Pending</span>
                                            @elseif($return->status === 'approved')
                                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Approved</span>
                                            @elseif($return->status === 'completed')
                                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Completed</span>
                                            @else
                                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm space-x-2">
                                            <a href="{{ route($routePrefix . 'inventory-returns.show', $return) }}" class="text-blue-600 hover:text-blue-900 font-semibold">View</a>
                                            @if($return->status === 'pending')
                                                <form action="{{ route($routePrefix . 'inventory-returns.destroy', $return) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this inventory return?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4">
                        {{ $inventoryReturns->links() }}
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        No inventory returns found.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>


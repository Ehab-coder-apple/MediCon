<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center py-4 px-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('New Stock Transfer') }}
            </h2>
            <a href="{{ route('admin.stock-transfers.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800">
                1 Back to list
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8">
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc pl-5 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.stock-transfers.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="from_warehouse_id" class="block text-sm font-medium text-gray-700">From Warehouse</label>
                            <select name="from_warehouse_id" id="from_warehouse_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select source warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ old('from_warehouse_id', $fromWarehouseId) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="to_warehouse_id" class="block text-sm font-medium text-gray-700">To Warehouse</label>
                            <select name="to_warehouse_id" id="to_warehouse_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select destination warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700">Reference (optional)</label>
                            <input type="text" name="reference" id="reference" value="{{ old('reference') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason (optional)</label>
                            <input type="text" name="reason" id="reason" value="{{ old('reason') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Items</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Specify the products, batches (optional) and quantities to move. You can leave unused rows empty.
                        </p>

                        @php
                            $oldItems = old('items', [
                                ['product_id' => '', 'batch_id' => '', 'quantity' => ''],
                                ['product_id' => '', 'batch_id' => '', 'quantity' => ''],
                                ['product_id' => '', 'batch_id' => '', 'quantity' => ''],
                            ]);
                        @endphp

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product ID</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch ID (optional)</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($oldItems as $index => $item)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <input type="number" name="items[{{ $index }}][product_id]" min="1"
                                                       value="{{ $item['product_id'] ?? '' }}"
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <input type="number" name="items[{{ $index }}][batch_id]" min="1"
                                                       value="{{ $item['batch_id'] ?? '' }}"
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <input type="number" name="items[{{ $index }}][quantity]" min="1"
                                                       value="{{ $item['quantity'] ?? '' }}"
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-sm">
                            Save Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Return Request - {{ $purchase->reference_number }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                }
            @endphp
            <a href="{{ route($routePrefix . 'purchases.show', $purchase) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Purchase Order
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Purchase Summary -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Purchase Order Summary</h3>
                </div>
                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Reference Number</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchase->reference_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Supplier</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchase->supplier->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Items</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchase->purchaseItems->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Return Request Form -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Return Items</h3>
                    <p class="text-sm text-gray-600 mt-2">Select items to return and provide reason for each return.</p>
                </div>

                <form action="{{ route($routePrefix . 'purchase-returns.store', $purchase) }}" method="POST" class="p-6 lg:p-8">
                    @csrf

                    <div class="mb-6">
                        <label for="return_date" class="block text-sm font-medium text-gray-700 mb-2">Return Date</label>
                        <input type="date" id="return_date" name="return_date" value="{{ old('return_date', today()->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        @error('return_date')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchaseItems as $index => $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->product->code }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-semibold">{{ $item->quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="hidden" name="items[{{ $index }}][purchase_item_id]" value="{{ $item->id }}">
                                        <input type="number" name="items[{{ $index }}][quantity]" min="1" max="{{ $item->quantity }}" class="w-20 px-2 py-1 border border-gray-300 rounded" required>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select name="items[{{ $index }}][batch_id]" class="w-32 px-2 py-1 border border-gray-300 rounded" required>
                                            <option value="">Select Batch</option>
                                            @if($item->batch)
                                                <option value="{{ $item->batch->id }}" selected>{{ $item->batch->batch_number }}</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select name="items[{{ $index }}][reason]" class="w-32 px-2 py-1 border border-gray-300 rounded" required>
                                            <option value="">Select Reason</option>
                                            <option value="damaged">Damaged</option>
                                            <option value="expired">Expired</option>
                                            <option value="wrong_item">Wrong Item</option>
                                            <option value="quality_issue">Quality Issue</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" name="items[{{ $index }}][notes]" placeholder="Optional notes" class="w-32 px-2 py-1 border border-gray-300 rounded">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Add any additional notes about this return..."></textarea>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route($routePrefix . 'purchases.show', $purchase) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                            Create Return Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


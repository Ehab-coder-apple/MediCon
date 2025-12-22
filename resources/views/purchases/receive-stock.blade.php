<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Receive Stock - Purchase Order: {{ $purchase->reference_number }}
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
                    @php
                        $totalOrderedCost = $purchase->purchaseItems->sum('total_cost');
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
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
                        <div>
                            <p class="text-sm text-gray-600">Total Ordered Cost</p>
                            <p class="text-lg font-semibold text-blue-600">{{ number_format($totalOrderedCost, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Received Cost</p>
                            <p class="text-lg font-semibold text-green-600" id="totalReceivedCost">0.00</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Receiving Form -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Receive Items</h3>
                    <p class="text-sm text-gray-600 mt-2">Enter the actual quantities received and batch information for each item.</p>
                </div>

                <form action="{{ route($routePrefix . 'purchases.process-receive-stock', $purchase) }}" method="POST" class="p-6 lg:p-8">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ordered Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchase->purchaseItems as $index => $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->product->code }}</div>
                                        </div>
                                        <input type="hidden" name="items[{{ $index }}][purchase_item_id]" value="{{ $item->id }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-semibold">{{ $item->quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" name="items[{{ $index }}][received_quantity]" value="{{ $item->quantity }}" min="0" class="w-20 px-2 py-1 border border-gray-300 rounded" required>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" name="items[{{ $index }}][batch_number]" placeholder="e.g., BATCH001" class="w-32 px-2 py-1 border border-gray-300 rounded" required>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="date" name="items[{{ $index }}][expiry_date]" class="w-32 px-2 py-1 border border-gray-300 rounded" required>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route($routePrefix . 'purchases.show', $purchase) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Confirm Stock Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Store unit costs for each item
        const itemCosts = {
            @foreach($purchase->purchaseItems as $index => $item)
                {{ $index }}: {{ $item->unit_cost }},
            @endforeach
        };

        // Function to calculate total received cost
        function calculateTotalReceivedCost() {
            let totalReceivedCost = 0;

            // Get all received quantity inputs
            document.querySelectorAll('input[name*="[received_quantity]"]').forEach((input, index) => {
                const receivedQty = parseFloat(input.value) || 0;
                const unitCost = itemCosts[index] || 0;
                totalReceivedCost += receivedQty * unitCost;
            });

            // Update the display
            document.getElementById('totalReceivedCost').textContent = totalReceivedCost.toFixed(2);
        }

        // Add event listeners to all received quantity inputs
        document.querySelectorAll('input[name*="[received_quantity]"]').forEach(input => {
            input.addEventListener('change', calculateTotalReceivedCost);
            input.addEventListener('input', calculateTotalReceivedCost);
        });

        // Calculate on page load
        calculateTotalReceivedCost();
    </script>
</x-app-layout>


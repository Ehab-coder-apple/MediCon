<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Purchase Order') }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                } elseif (auth()->user()->hasRole('sales_staff')) {
                    $routePrefix = 'sales-staff.';
                }
            @endphp
            <div class="flex space-x-2 ml-auto">
                <a href="{{ route($routePrefix . 'purchases.show', $purchase) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    View Purchase
                </a>
                <a href="{{ route($routePrefix . 'purchases.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Purchase Orders
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Edit Purchase Order #{{ $purchase->reference_number }}
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Update purchase order details and status
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    <form action="{{ route($routePrefix . 'purchases.update', $purchase) }}" method="POST" id="purchaseForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Supplier Selection -->
                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                                <select name="supplier_id" id="supplier_id" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                    <option value="">Select a supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ (old('supplier_id', $purchase->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Purchase Date -->
                            <div>
                                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date</label>
                                <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                @error('purchase_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                    <option value="pending" {{ old('status', $purchase->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ old('status', $purchase->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $purchase->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Reference Number (Read-only) -->
                        <div class="mb-6">
                            <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                            <input type="text" id="reference_number" value="{{ $purchase->reference_number }}" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50" 
                                readonly>
                        </div>

                        <!-- Notes -->
                        <div class="mb-8">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="4" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                placeholder="Additional notes about this purchase order">{{ old('notes', $purchase->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Purchase Items (Editable) -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Purchase Items</h3>
                                <button type="button" id="addItemBtn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Add Item
                                </button>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider flex-1">Product</th>
                                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Quantity</th>
                                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Unit Cost</th>
                                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Total</th>
                                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="purchaseItemsTable" class="bg-white divide-y divide-gray-200">
                                            @foreach($purchase->purchaseItems as $index => $item)
                                            <tr class="purchase-item" data-item-id="{{ $item->id }}">
                                                <td class="px-4 py-4 flex-1">
                                                    <select name="items[{{ $index }}][product_id]" class="product-select w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-gray-900" required>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                                {{ $product->name }} ({{ $product->code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-2 py-4 whitespace-nowrap">
                                                    <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}"
                                                        class="quantity-input w-16 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                        min="1" required>
                                                </td>
                                                <td class="px-2 py-4 whitespace-nowrap">
                                                    <input type="number" name="items[{{ $index }}][unit_cost]" value="{{ $item->unit_cost }}"
                                                        class="unit-cost-input w-16 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                        step="0.01" min="0" required>
                                                </td>
                                                <td class="px-2 py-4 whitespace-nowrap">
                                                    <span class="total-cost text-sm font-medium text-gray-900">${{ number_format($item->total_cost, 2) }}</span>
                                                </td>
                                                <td class="px-2 py-4 whitespace-nowrap">
                                                    <button type="button" class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">
                                                        Delete
                                                    </button>
                                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
                                    <div class="text-lg font-semibold text-gray-900">
                                        Total Cost: $<span id="totalCost">{{ number_format($purchase->total_cost, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <p class="mt-2 text-sm text-gray-500">
                                <strong>Note:</strong> You can edit quantities, unit costs, batch numbers, and expiry dates. You can also add new items or remove existing ones.
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route($routePrefix . 'purchases.show', $purchase) }}" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = {{ count($purchase->purchaseItems) }};

        // Add new item row
        document.getElementById('addItemBtn').addEventListener('click', function() {
            const tableBody = document.getElementById('purchaseItemsTable');
            const newRow = document.createElement('tr');
            newRow.className = 'purchase-item';
            newRow.innerHTML = `
                <td class="px-4 py-4 flex-1">
                    <select name="items[${itemIndex}][product_id]" class="product-select w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-gray-900" required>
                        <option value="">Select a product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code }})</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-2 py-4 whitespace-nowrap">
                    <input type="number" name="items[${itemIndex}][quantity]" value="1"
                        class="quantity-input w-16 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        min="1" required>
                </td>
                <td class="px-2 py-4 whitespace-nowrap">
                    <input type="number" name="items[${itemIndex}][unit_cost]" value="0.00"
                        class="unit-cost-input w-16 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        step="0.01" min="0" required>
                </td>
                <td class="px-2 py-4 whitespace-nowrap">
                    <span class="total-cost text-sm font-medium text-gray-900">$0.00</span>
                </td>
                <td class="px-2 py-4 whitespace-nowrap">
                    <button type="button" class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">
                        Delete
                    </button>
                </td>
            `;

            tableBody.appendChild(newRow);
            itemIndex++;

            // Add event listeners to new row
            addRowEventListeners(newRow);
            calculateTotal();
        });

        // Remove item row
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                const row = e.target.closest('tr');
                const itemId = row.dataset.itemId;

                if (itemId) {
                    // Add hidden input to mark for deletion
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'deleted_items[]';
                    hiddenInput.value = itemId;
                    document.getElementById('purchaseForm').appendChild(hiddenInput);
                }

                row.remove();
                calculateTotal();
            }
        });

        // Add event listeners to existing rows
        document.querySelectorAll('.purchase-item').forEach(row => {
            addRowEventListeners(row);
        });

        function addRowEventListeners(row) {
            const quantityInput = row.querySelector('.quantity-input');
            const unitCostInput = row.querySelector('.unit-cost-input');

            quantityInput.addEventListener('input', function() {
                updateRowTotal(row);
                calculateTotal();
            });

            unitCostInput.addEventListener('input', function() {
                updateRowTotal(row);
                calculateTotal();
            });
        }

        function updateRowTotal(row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const unitCost = parseFloat(row.querySelector('.unit-cost-input').value) || 0;
            const total = quantity * unitCost;

            row.querySelector('.total-cost').textContent = '$' + total.toFixed(2);
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.purchase-item').forEach(row => {
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const unitCost = parseFloat(row.querySelector('.unit-cost-input').value) || 0;
                total += quantity * unitCost;
            });

            document.getElementById('totalCost').textContent = total.toFixed(2);
        }

        // Initialize calculations
        document.querySelectorAll('.purchase-item').forEach(row => {
            updateRowTotal(row);
        });
        calculateTotal();
    </script>
</x-app-layout>

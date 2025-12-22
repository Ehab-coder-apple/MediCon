<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Purchase Order') }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                }
            @endphp
            <a href="{{ route($routePrefix . 'purchases.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Purchase Orders
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Create New Purchase Order
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Create a purchase order for supplier inventory
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    <form action="{{ route($routePrefix . 'purchases.store') }}" method="POST" id="purchaseForm">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Supplier Selection -->
                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                                <select name="supplier_id" id="supplier_id" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                    <option value="">Select a supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
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
                                <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                @error('purchase_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Reference Number -->
                            <div>
                                <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number', $referenceNumber) }}"
                                        class="flex-1 border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500"
                                        required readonly>
                                    <button type="button" id="regenerateRef"
                                        class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 text-sm hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                </div>
                                @error('reference_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Purchase Items -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Purchase Items</h3>
                                <button type="button" id="addItem" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Add Item
                                </button>
                            </div>

                            <div id="itemsContainer">
                                <!-- Items will be added here dynamically -->
                            </div>

                            @error('items')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-8">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="3" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Total Summary -->
                        <div class="mb-8 p-4 bg-gray-50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-medium text-gray-900">Total Cost:</span>
                                <span id="totalCost" class="text-2xl font-bold text-green-600">$0.00</span>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route($routePrefix . 'purchases.index') }}"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = 0;
        const products = @json($products);

        document.getElementById('addItem').addEventListener('click', function() {
            addPurchaseItem();
        });

        function addPurchaseItem() {
            const container = document.getElementById('itemsContainer');
            const itemHtml = `
                <div class="purchase-item border border-gray-200 rounded-lg p-4 mb-4" data-index="${itemIndex}">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-md font-medium text-gray-900">Item ${itemIndex + 1}</h4>
                        <button type="button" class="remove-item text-red-600 hover:text-red-900">Remove</button>
                    </div>
                    
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Product</label>
                            <select name="items[${itemIndex}][product_id]" class="product-select mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">Select product</option>
                                ${products.map(product => `<option value="${product.id}" data-cost="${product.cost_price}">${product.name} (${product.code})</option>`).join('')}
                            </select>
                        </div>

                        <div class="w-24">
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="items[${itemIndex}][quantity]" class="quantity-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" min="1" required>
                        </div>

                        <div class="w-24">
                            <label class="block text-sm font-medium text-gray-700">Unit Cost</label>
                            <input type="number" name="items[${itemIndex}][unit_cost]" class="unit-cost-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" step="0.01" min="0" required>
                        </div>

                        <div class="w-24">
                            <label class="block text-sm font-medium text-gray-700">Total</label>
                            <input type="text" class="item-total mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                        </div>
                    </div>
                    

                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', itemHtml);
            itemIndex++;
            
            // Add event listeners to the new item
            const newItem = container.lastElementChild;
            addItemEventListeners(newItem);
        }

        function addItemEventListeners(item) {
            // Remove item button
            item.querySelector('.remove-item').addEventListener('click', function() {
                item.remove();
                calculateTotal();
            });
            
            // Product selection
            item.querySelector('.product-select').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const costPrice = selectedOption.dataset.cost;
                if (costPrice) {
                    item.querySelector('.unit-cost-input').value = costPrice;
                    calculateItemTotal(item);
                }
            });
            
            // Quantity and unit cost changes
            item.querySelector('.quantity-input').addEventListener('input', function() {
                calculateItemTotal(item);
            });
            
            item.querySelector('.unit-cost-input').addEventListener('input', function() {
                calculateItemTotal(item);
            });
        }

        function calculateItemTotal(item) {
            const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
            const unitCost = parseFloat(item.querySelector('.unit-cost-input').value) || 0;
            const total = quantity * unitCost;
            
            item.querySelector('.item-total').value = '$' + total.toFixed(2);
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.purchase-item').forEach(item => {
                const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
                const unitCost = parseFloat(item.querySelector('.unit-cost-input').value) || 0;
                total += quantity * unitCost;
            });
            
            document.getElementById('totalCost').textContent = '$' + total.toFixed(2);
        }

        // Regenerate reference number
        document.getElementById('regenerateRef').addEventListener('click', function() {
            const button = this;
            const input = document.getElementById('reference_number');

            // Show loading state
            button.disabled = true;
            button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            // Generate new reference number (simple client-side generation)
            const now = new Date();
            const dateStr = now.getFullYear().toString() +
                           (now.getMonth() + 1).toString().padStart(2, '0') +
                           now.getDate().toString().padStart(2, '0');
            const randomSeq = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
            const newRef = `PO-${dateStr}-${randomSeq}`;

            // Update input
            input.value = newRef;

            // Reset button
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
            }, 500);
        });

        // Add first item by default
        addPurchaseItem();
    </script>
</x-app-layout>

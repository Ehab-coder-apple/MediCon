<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Receive Stock') }}
            </h2>
            <a href="{{ route('admin.stock-receiving.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to History
            </a>
        </div>
    </x-slot>

    <div class="p-6">
        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Stock Receiving Form -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">📦 New Stock Receipt</h3>
            </div>

            <form action="{{ route('admin.stock-receiving.store') }}" method="POST" id="stockReceivingForm">
                @csrf
                
                <div class="p-6 space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Supplier (Optional)
                            </label>
                            <select name="supplier_id" id="supplier_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Reference Number (Optional)
                            </label>
                            <input type="text" name="reference_number" id="reference_number" 
                                   value="{{ old('reference_number') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="e.g., PO-2024-001">
                        </div>

                        <div>
                            <label for="received_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Received Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="received_date" id="received_date" 
                                   value="{{ old('received_date', now()->format('Y-m-d')) }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (Optional)
                        </label>
                        <textarea name="notes" id="notes" rows="3" 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Any additional notes about this stock receipt...">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Items Section -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-medium text-gray-900">Stock Items</h4>
                            <button type="button" id="addItemBtn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                + Add Item
                            </button>
                        </div>

                        <div id="itemsContainer">
                            <!-- Items will be added here dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('admin.stock-receiving.index') }}"
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Receive Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Dynamic Items -->
    <script>
        let itemIndex = 0;
        const products = @json($products);

        document.getElementById('addItemBtn').addEventListener('click', function() {
            addItemRow();
        });

        function addItemRow() {
            const container = document.getElementById('itemsContainer');
            const itemHtml = `
                <div class="item-row border border-gray-200 rounded-lg p-4 mb-4" data-index="${itemIndex}">
                    <div class="flex justify-between items-start mb-4">
                        <h5 class="text-md font-medium text-gray-900">Item ${itemIndex + 1}</h5>
                        <button type="button" class="text-red-600 hover:text-red-800 remove-item">
                            ✕ Remove
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Product <span class="text-red-500">*</span>
                            </label>
                            <select name="items[${itemIndex}][product_id]" class="product-select w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Product</option>
                                ${products.map(product => `<option value="${product.id}" data-cost="${product.cost_price}">${product.name} (${product.code})</option>`).join('')}
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Batch Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="items[${itemIndex}][batch_number]" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="e.g., BATCH001" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Expiry Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="items[${itemIndex}][expiry_date]" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   min="{{ now()->addDay()->format('Y-m-d') }}" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Paid Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="items[${itemIndex}][quantity]"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   min="1" placeholder="0" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Bonus Quantity
                                <span class="text-sm text-gray-500">(Free)</span>
                            </label>
                            <input type="number" name="items[${itemIndex}][bonus_quantity]"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                   min="0" placeholder="0" value="0">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cost Price (Optional)
                            </label>
                            <input type="number" name="items[${itemIndex}][cost_price]"
                                   class="cost-price w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Bonus Notes Row -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Bonus Notes (Optional)
                        </label>
                        <input type="text" name="items[${itemIndex}][bonus_notes]"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="e.g., Buy 10 get 2 free, Promotional offer, etc.">
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', itemHtml);
            
            // Add event listeners
            const newRow = container.lastElementChild;
            newRow.querySelector('.remove-item').addEventListener('click', function() {
                newRow.remove();
            });
            
            // Auto-fill cost price when product is selected
            newRow.querySelector('.product-select').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const costPrice = selectedOption.getAttribute('data-cost');
                if (costPrice) {
                    newRow.querySelector('.cost-price').value = costPrice;
                }
            });
            
            itemIndex++;
        }

        // Add first item row on page load
        document.addEventListener('DOMContentLoaded', function() {
            addItemRow();
        });
    </script>
</x-app-layout>

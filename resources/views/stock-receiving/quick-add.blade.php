<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Quick Add Stock') }} - {{ $product->name }}
            </h2>
            <a href="{{ route('products.show', $product) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Product
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Product Information -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">📦 Product Information</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Product Name:</span>
                            <p class="text-sm text-gray-900">{{ $product->name }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Product Code:</span>
                            <p class="text-sm text-gray-900">{{ $product->code }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Category:</span>
                            <p class="text-sm text-gray-900">{{ $product->category }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Current Stock:</span>
                            <p class="text-sm {{ $product->is_low_stock ? 'text-red-600' : 'text-green-600' }}">
                                {{ $product->active_quantity }} units
                                @if($product->is_low_stock)
                                    <span class="text-red-500 text-xs">(Low Stock)</span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Current Cost Price:</span>
                            <p class="text-sm text-gray-900">${{ number_format($product->cost_price, 2) }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Alert Quantity:</span>
                            <p class="text-sm text-gray-900">{{ $product->alert_quantity }} units</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Add Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">➕ Add Stock to Inventory</h3>
                    </div>

                    <form action="{{ route('admin.stock-receiving.process-quick-add', $product) }}" method="POST">
                        @csrf
                        
                        <div class="p-6 space-y-6">
                            <!-- Supplier Selection -->
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

                            <!-- Stock Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Batch Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="batch_number" id="batch_number" 
                                           value="{{ old('batch_number') }}"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="e.g., BATCH001" required>
                                    <p class="text-xs text-gray-500 mt-1">Enter the batch number from the product packaging</p>
                                </div>

                                <div>
                                    <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Expiry Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="expiry_date" id="expiry_date" 
                                           value="{{ old('expiry_date') }}"
                                           min="{{ now()->addDay()->format('Y-m-d') }}"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           required>
                                    <p class="text-xs text-gray-500 mt-1">Must be a future date</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                        Paid Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="quantity" id="quantity"
                                           value="{{ old('quantity') }}"
                                           min="1"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="0" required>
                                    <p class="text-xs text-gray-500 mt-1">Paid units to add</p>
                                </div>

                                <div>
                                    <label for="bonus_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                        Bonus Quantity
                                        <span class="text-sm text-green-600">(Free)</span>
                                    </label>
                                    <input type="number" name="bonus_quantity" id="bonus_quantity"
                                           value="{{ old('bonus_quantity', 0) }}"
                                           min="0"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                           placeholder="0">
                                    <p class="text-xs text-green-600 mt-1">Free bonus units</p>
                                </div>

                                <div>
                                    <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">
                                        Cost Price (Optional)
                                    </label>
                                    <input type="number" name="cost_price" id="cost_price"
                                           value="{{ old('cost_price', $product->cost_price) }}"
                                           step="0.01" min="0"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="0.00">
                                    <p class="text-xs text-gray-500 mt-1">Current: ${{ number_format($product->cost_price, 2) }}</p>
                                </div>
                            </div>

                            <!-- Bonus Notes -->
                            <div>
                                <label for="bonus_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bonus Notes (Optional)
                                </label>
                                <input type="text" name="bonus_notes" id="bonus_notes"
                                       value="{{ old('bonus_notes') }}"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="e.g., Buy 10 get 2 free, Promotional offer, etc.">
                                <p class="text-xs text-gray-500 mt-1">Describe the bonus offer or promotion</p>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes (Optional)
                                </label>
                                <textarea name="notes" id="notes" rows="3" 
                                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Any additional notes about this stock addition...">{{ old('notes') }}</textarea>
                            </div>

                            <!-- Preview -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">📋 Summary</h4>
                                <div class="text-sm text-blue-800">
                                    <p>Adding stock to: <strong>{{ $product->name }}</strong></p>
                                    <p>Current stock: <strong>{{ $product->active_quantity }} units</strong></p>
                                    <p id="paidQuantityPreview" class="hidden">Paid quantity: <strong><span id="paidQuantityAmount">0</span> units</strong></p>
                                    <p id="bonusQuantityPreview" class="hidden">Bonus quantity: <strong><span id="bonusQuantityAmount">0</span> units</strong> <span class="text-green-600">(Free)</span></p>
                                    <p id="totalAddingPreview" class="hidden">Total adding: <strong><span id="totalAddingAmount">0</span> units</strong></p>
                                    <p id="newStockPreview" class="hidden">New stock will be: <strong><span id="newStockAmount">{{ $product->active_quantity }}</span> units</strong></p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                            <a href="{{ route('products.show', $product) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                ➕ Add Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Preview -->
    <script>
        function updatePreview() {
            const paidQuantity = parseInt(document.getElementById('quantity').value) || 0;
            const bonusQuantity = parseInt(document.getElementById('bonus_quantity').value) || 0;
            const currentStock = {{ $product->active_quantity }};
            const totalAdding = paidQuantity + bonusQuantity;
            const newStock = currentStock + totalAdding;

            // Update preview elements
            const paidPreview = document.getElementById('paidQuantityPreview');
            const bonusPreview = document.getElementById('bonusQuantityPreview');
            const totalAddingPreview = document.getElementById('totalAddingPreview');
            const newStockPreview = document.getElementById('newStockPreview');

            const paidAmount = document.getElementById('paidQuantityAmount');
            const bonusAmount = document.getElementById('bonusQuantityAmount');
            const totalAddingAmount = document.getElementById('totalAddingAmount');
            const newStockAmount = document.getElementById('newStockAmount');

            if (paidQuantity > 0 || bonusQuantity > 0) {
                // Show paid quantity
                if (paidQuantity > 0) {
                    paidAmount.textContent = paidQuantity;
                    paidPreview.classList.remove('hidden');
                } else {
                    paidPreview.classList.add('hidden');
                }

                // Show bonus quantity
                if (bonusQuantity > 0) {
                    bonusAmount.textContent = bonusQuantity;
                    bonusPreview.classList.remove('hidden');
                } else {
                    bonusPreview.classList.add('hidden');
                }

                // Show total adding
                totalAddingAmount.textContent = totalAdding;
                totalAddingPreview.classList.remove('hidden');

                // Show new stock total
                newStockAmount.textContent = newStock;
                newStockPreview.classList.remove('hidden');
            } else {
                // Hide all previews
                paidPreview.classList.add('hidden');
                bonusPreview.classList.add('hidden');
                totalAddingPreview.classList.add('hidden');
                newStockPreview.classList.add('hidden');
            }
        }

        // Add event listeners
        document.getElementById('quantity').addEventListener('input', updatePreview);
        document.getElementById('bonus_quantity').addEventListener('input', updatePreview);

        // Initial update
        document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Purchase Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($analysis['total_items'] == 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">No Items to Reorder</h3>
                    <p class="text-blue-700">All products are currently in good stock. No purchase orders need to be generated at this time.</p>
                    <a href="{{ auth()->user()->hasRole('admin') ? route('admin.purchases.index') : route('pharmacist.purchases.index') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Back to Purchase Orders
                    </a>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Analysis</h3>
                        <div class="flex gap-4 mb-6">
                            <div class="flex-1 bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Items to Reorder</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ $analysis['total_items'] }}</p>
                            </div>
                            <div class="flex-1 bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Estimated Cost</p>
                                <p class="text-2xl font-bold text-blue-600">${{ number_format($analysis['total_cost_estimate'], 2) }}</p>
                            </div>
                            <div class="flex-1 bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Suppliers Available</p>
                                <p class="text-2xl font-bold text-green-600">{{ $suppliers->count() }}</p>
                            </div>
                        </div>

                        <form action="{{ auth()->user()->hasRole('admin') ? route('admin.purchases.auto-generate') : route('pharmacist.purchases.auto-generate') }}" method="POST" id="autoGenerateForm">
                            @csrf

                            @if ($errors->any())
                                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                                    <h4 class="text-red-900 font-semibold mb-2">Validation Errors:</h4>
                                    <ul class="list-disc list-inside text-red-700 text-sm">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mb-6">
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Supplier <span class="text-red-500">*</span>
                                </label>
                                <select id="supplier_id" name="supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('supplier_id') border-red-500 @enderror" required>
                                    <option value="">-- Choose a Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Items to Include</h4>

                                <!-- Filter Buttons -->
                                <div class="mb-4 flex flex-wrap gap-2">
                                    <button type="button" class="filter-btn px-4 py-2 rounded-lg font-medium text-sm transition-colors" data-filter="all" style="background-color: #3b82f6; color: white;">
                                        All Items
                                    </button>
                                    <button type="button" class="filter-btn px-4 py-2 rounded-lg font-medium text-sm transition-colors" data-filter="out_of_stock" style="background-color: #ef4444; color: white;">
                                        Out of Stock
                                    </button>
                                    <button type="button" class="filter-btn px-4 py-2 rounded-lg font-medium text-sm transition-colors" data-filter="low_stock" style="background-color: #eab308; color: black;">
                                        Low Stock
                                    </button>
                                    <button type="button" class="filter-btn px-4 py-2 rounded-lg font-medium text-sm transition-colors" data-filter="expiring_soon" style="background-color: #f97316; color: white;">
                                        Expiring Soon
                                    </button>
                                </div>

                                <!-- Filter Info -->
                                <div class="mb-4 text-sm text-gray-600">
                                    Showing <span id="visibleCount">{{ count($itemsToReorder) }}</span> of {{ count($itemsToReorder) }} items
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-4 py-2 text-left">
                                                    <input type="checkbox" id="select-all" class="rounded">
                                                </th>
                                                <th class="px-4 py-2 text-left">Product</th>
                                                <th class="px-4 py-2 text-left">Code</th>
                                                <th class="px-4 py-2 text-left">Current Qty</th>
                                                <th class="px-4 py-2 text-left">Reason</th>
                                                <th class="px-4 py-2 text-right">Qty to Order</th>
                                                <th class="px-4 py-2 text-right">Unit Cost</th>
                                                <th class="px-4 py-2 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y" id="itemsTable">
                                            @foreach($itemsToReorder as $index => $item)
                                            <tr class="hover:bg-gray-50 item-row" data-product-id="{{ $item['product_id'] }}" data-reason="{{ $item['reason'] }}">
                                                <td class="px-4 py-2">
                                                    <input type="checkbox" class="item-checkbox rounded" data-product-id="{{ $item['product_id'] }}" data-quantity="{{ $item['suggested_quantity'] }}" data-cost="{{ $item['cost_price'] }}">
                                                </td>
                                                <td class="px-4 py-2 font-medium">{{ $item['product_name'] }}</td>
                                                <td class="px-4 py-2">{{ $item['product_code'] }}</td>
                                                <td class="px-4 py-2">{{ $item['current_quantity'] }}</td>
                                                <td class="px-4 py-2">
                                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                                        @if($item['reason'] === 'out_of_stock') bg-red-100 text-red-800
                                                        @elseif($item['reason'] === 'low_stock') bg-yellow-100 text-yellow-800
                                                        @else bg-orange-100 text-orange-800
                                                        @endif">
                                                        {{ ucfirst(str_replace('_', ' ', $item['reason'])) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 text-right">
                                                    <input type="number" class="quantity-input w-20 px-2 py-1 border border-gray-300 rounded text-right" value="{{ $item['suggested_quantity'] }}" min="1">
                                                </td>
                                                <td class="px-4 py-2 text-right">${{ number_format($item['cost_price'], 2) }}</td>
                                                <td class="px-4 py-2 text-right item-total">${{ number_format($item['suggested_quantity'] * $item['cost_price'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                                    Generate Purchase Order
                                </button>
                                <a href="{{ auth()->user()->hasRole('admin') ? route('admin.purchases.index') : route('pharmacist.purchases.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Filter functionality
        let currentFilter = 'all';

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                currentFilter = this.dataset.filter;

                // Update button styles
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.style.opacity = '0.6';
                });
                this.style.opacity = '1';

                // Filter rows
                filterItems();
            });
        });

        function filterItems() {
            const rows = document.querySelectorAll('.item-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const reason = row.dataset.reason;
                let shouldShow = false;

                if (currentFilter === 'all') {
                    shouldShow = true;
                } else if (currentFilter === reason) {
                    shouldShow = true;
                }

                row.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visibleCount++;
            });

            // Update visible count
            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Set initial active button
        document.querySelector('[data-filter="all"]').style.opacity = '1';

        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox:not([style*="display: none"])');
            checkboxes.forEach(cb => {
                const row = cb.closest('tr');
                if (row.style.display !== 'none') {
                    cb.checked = this.checked;
                }
            });
        });

        // Handle form submission to build items array
        document.getElementById('autoGenerateForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const supplierId = document.getElementById('supplier_id').value;

            console.log('Form submitted. Supplier ID:', supplierId);

            if (!supplierId) {
                alert('Please select a supplier');
                console.log('No supplier selected');
                return;
            }

            // Get selected items (only from visible rows)
            const selectedItems = document.querySelectorAll('.item-row:not([style*="display: none"]) .item-checkbox:checked');

            console.log('Selected items count:', selectedItems.length);

            if (selectedItems.length === 0) {
                alert('Please select at least one item to order');
                console.log('No items selected');
                return;
            }

            // Clear any existing hidden inputs for items
            form.querySelectorAll('input[name^="items"]').forEach(input => input.remove());

            // Add hidden inputs for selected items
            let itemIndex = 0;
            selectedItems.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const productId = checkbox.dataset.productId;
                const quantityInput = row.querySelector('.quantity-input');
                const quantity = quantityInput.value;

                // Create hidden inputs for product_id
                const productIdInput = document.createElement('input');
                productIdInput.type = 'hidden';
                productIdInput.name = `items[${itemIndex}][product_id]`;
                productIdInput.value = productId;
                form.appendChild(productIdInput);

                // Create hidden inputs for quantity
                const quantityHiddenInput = document.createElement('input');
                quantityHiddenInput.type = 'hidden';
                quantityHiddenInput.name = `items[${itemIndex}][quantity]`;
                quantityHiddenInput.value = quantity;
                form.appendChild(quantityHiddenInput);

                itemIndex++;
            });

            console.log('Form data:', {
                supplier_id: supplierId,
                items_count: itemIndex,
                form_action: form.action
            });

            // Submit the form normally
            form.submit();
        });

        // Update item total when quantity changes
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const row = this.closest('tr');
                const cost = parseFloat(row.querySelector('.item-checkbox').dataset.cost);
                const quantity = parseInt(this.value) || 0;
                const total = cost * quantity;
                row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
            });
        });
    </script>
</x-app-layout>


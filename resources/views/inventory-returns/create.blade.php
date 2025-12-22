<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Inventory Return
            </h2>
            <a href="{{ route($routePrefix . 'inventory-returns.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Inventory Returns
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">owing 
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route($routePrefix . 'inventory-returns.store') }}" method="POST" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @csrf

                <!-- Form Header -->
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Return Information</h3>
                </div>

                <!-- Basic Information -->
                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Warehouse <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Warehouse</option>
                            </select>
                            @error('warehouse_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier <span class="text-red-500">*</span></label>
                            <select id="supplier_id" name="supplier_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="return_date" class="block text-sm font-medium text-gray-700">Return Date <span class="text-red-500">*</span></label>
                            <input type="date" id="return_date" name="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                            @error('return_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="p-6 lg:p-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Return Items</h3>
                    <div id="items-container">
                        <div class="item-row mb-6 p-4 border border-gray-200 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Product <span class="text-red-500">*</span></label>
                                    <select name="items[0][product_id]" class="product-select mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Batch <span class="text-red-500">*</span></label>
                                    <select name="items[0][batch_id]" class="batch-select mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                        <option value="">Select Batch</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Quantity <span class="text-red-500">*</span></label>
                                    <input type="number" name="items[0][quantity]" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Unit Cost</label>
                                    <input type="number" name="items[0][unit_cost]" step="0.01" min="0" class="unit-cost mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reason <span class="text-red-500">*</span></label>
                                    <select name="items[0][reason]" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Reason</option>
                                        <option value="slow_moving">Slow-moving stock</option>
                                        <option value="nearly_expired">Nearly expired / approaching expiry</option>
                                        <option value="damaged">Damaged goods</option>
                                        <option value="overstocked">Overstocked items</option>
                                        <option value="quality_issue">Quality issues</option>
                                        <option value="wrong_item">Wrong items ordered</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <input type="text" name="items[0][notes]" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                                </div>
                            </div>
                            <button type="button" class="mt-4 remove-item-btn bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="add-item-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Item</button>
                </div>

                <!-- Overall Notes -->
                <div class="p-6 lg:p-8 border-t border-gray-200">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Overall Notes</label>
                    <textarea id="notes" name="notes" maxlength="1000" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="p-6 lg:p-8 border-t border-gray-200 flex justify-end gap-4">
                    <a href="{{ route($routePrefix . 'inventory-returns.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</a>
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Create Return</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemCount = 1;
        let allProducts = [];
        let allBatches = [];
        let allWarehouses = [];

        const warehouseSelect = document.getElementById('warehouse_id');
        const itemsContainer = document.getElementById('items-container');
        const addItemBtn = document.getElementById('add-item-btn');

        // Load all warehouses on page load
        function loadAllWarehouses() {
            console.log('Loading all warehouses...');

            fetch('/api/all-warehouses')
                .then(response => {
                    console.log('Warehouses response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Warehouses data:', data);
                    allWarehouses = data.warehouses || [];

                    // Update warehouse select
                    warehouseSelect.innerHTML = '<option value="">Select Warehouse</option>';
                    if (allWarehouses.length > 0) {
                        allWarehouses.forEach(warehouse => {
                            const option = document.createElement('option');
                            option.value = warehouse.id;
                            option.textContent = warehouse.name;
                            warehouseSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No warehouses available';
                        warehouseSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error loading warehouses:', error);
                    warehouseSelect.innerHTML = '<option value="">Error loading warehouses</option>';
                });
        }

        // Load all products and batches on page load
        function loadAllProductsAndBatches() {
            console.log('Loading all products and batches...');

            fetch('/api/all-products-batches')
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Products and batches data:', data);
                    allProducts = data.products || [];
                    allBatches = data.batches || [];

                    // Update all product selects
                    updateAllProductSelects();
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    document.querySelectorAll('.product-select').forEach(select => {
                        select.innerHTML = '<option value="">Error loading products</option>';
                    });
                });
        }

        function updateAllProductSelects() {
            document.querySelectorAll('.product-select').forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Select Product</option>';

                if (allProducts.length > 0) {
                    allProducts.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        select.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No products available';
                    select.appendChild(option);
                }

                if (currentValue) select.value = currentValue;
            });
        }

        // Update batches when product is selected
        function updateBatches(productSelect) {
            const productId = productSelect.value;
            const batchSelect = productSelect.closest('.item-row').querySelector('.batch-select');
            const unitCostInput = productSelect.closest('.item-row').querySelector('.unit-cost');

            batchSelect.innerHTML = '<option value="">Select Batch</option>';
            unitCostInput.value = '';

            if (!productId) return;

            const productBatches = allBatches.filter(b => b.product_id == productId);

            if (productBatches.length > 0) {
                productBatches.forEach(batch => {
                    const option = document.createElement('option');
                    option.value = batch.id;
                    option.textContent = `${batch.batch_number} (Exp: ${batch.expiry_date})`;
                    option.dataset.costPrice = batch.cost_price;
                    batchSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No batches available';
                batchSelect.appendChild(option);
            }
        }

        // Update unit cost when batch is selected
        function updateUnitCost(batchSelect) {
            const selectedOption = batchSelect.options[batchSelect.selectedIndex];
            const unitCostInput = batchSelect.closest('.item-row').querySelector('.unit-cost');
            unitCostInput.value = selectedOption.dataset.costPrice || '';
        }

        // Delegate event listeners for dynamic elements
        itemsContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-select')) {
                updateBatches(e.target);
            }
            if (e.target.classList.contains('batch-select')) {
                updateUnitCost(e.target);
            }
        });

        addItemBtn.addEventListener('click', function() {
            const newRow = document.querySelector('.item-row').cloneNode(true);
            newRow.querySelectorAll('input, select, textarea').forEach(el => {
                const name = el.name.replace(/\[\d+\]/, `[${itemCount}]`);
                el.name = name;
                el.value = '';
                if (el.classList.contains('unit-cost')) {
                    el.value = '';
                }
            });
            itemsContainer.appendChild(newRow);
            attachRemoveListener(newRow.querySelector('.remove-item-btn'));
            updateAllProductSelects();
            itemCount++;
        });

        function attachRemoveListener(btn) {
            btn.addEventListener('click', function() {
                this.closest('.item-row').remove();
            });
        }

        document.querySelectorAll('.remove-item-btn').forEach(btn => attachRemoveListener(btn));

        // Load warehouses and products on page load
        loadAllWarehouses();
        loadAllProductsAndBatches();
    </script>
</x-app-layout>


<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('New Sale') }}
            </h2>
            <a href="{{ auth()->user()->isAdmin() ? route('admin.sales.index') : (auth()->user()->isPharmacist() ? route('pharmacist.sales.index') : route('sales-staff.sales.index')) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Sales
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Create New Sale
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Process a new sale with barcode scanning and inventory management
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    <form action="{{ auth()->user()->isAdmin() ? route('admin.sales.store') : (auth()->user()->isPharmacist() ? route('pharmacist.sales.store') : route('sales-staff.sales.store')) }}" method="POST" id="salesForm">
                        @csrf
                        
                        <!-- Sale Information -->
                        <div class="mb-8">
                            <!-- Customer Selection -->
                            <div class="mb-6">
                                <label for="customer_search" class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 relative">
                                        <input type="text" id="customer_search" placeholder="Search customer by name, phone, or email..."
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            autocomplete="off">
                                        <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">

                                        <!-- Search Results Dropdown -->
                                        <div id="customerSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden max-h-60 overflow-y-auto">
                                            <!-- Results will be populated here -->
                                        </div>
                                    </div>
                                    <button type="button" id="newCustomerBtn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                        New Customer
                                    </button>
                                    <button type="button" id="walkInBtn" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                        Walk-in
                                    </button>
                                </div>
                                @error('customer_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Selected Customer Display -->
                            <div id="selectedCustomer" class="hidden mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-sm font-medium text-blue-800">Selected Customer</h3>
                                        <div id="customerDetails" class="mt-1 text-sm text-blue-700"></div>
                                    </div>
                                    <button type="button" id="clearCustomer" class="text-blue-600 hover:text-blue-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- New Customer Fields -->
                            <div id="newCustomerFields" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">New Customer Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="customer_name" class="block text-sm font-medium text-gray-700">Name *</label>
                                        <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('customer_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="customer_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="tel" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('customer_phone')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="customer_email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email') }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('customer_email')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sale Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                            <!-- Sale Date -->
                            <div>
                                <label for="sale_date" class="block text-sm font-medium text-gray-700">Sale Date</label>
                                <input type="date" name="sale_date" id="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                @error('sale_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Invoice Number -->
                            <div>
                                <label for="invoice_number" class="block text-sm font-medium text-gray-700">Invoice Number</label>
                                <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number', $invoiceNumber) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required readonly>
                                @error('invoice_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Barcode Scanner -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Products</h3>
                            <x-barcode-scanner />
                        </div>

                        <!-- Sale Items -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Sale Items</h3>
                                <button type="button" id="addManualItem" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Add Item Manually
                                </button>
                            </div>

                            <div id="itemsContainer" class="space-y-4">
                                <!-- Items will be added here dynamically -->
                            </div>

                            <div id="emptyItemsMessage" class="text-center py-8 text-gray-500">
                                No items added yet. Use the barcode scanner or search above to add products.
                            </div>

                            @error('items')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select name="payment_method" id="payment_method" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                    <option value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="insurance" {{ old('payment_method') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                                    <option value="mixed" {{ old('payment_method') == 'mixed' ? 'selected' : '' }}>Mixed</option>
                                </select>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="paid_amount" class="block text-sm font-medium text-gray-700">Amount Paid</label>
                                <input type="number" name="paid_amount" id="paid_amount" value="{{ old('paid_amount') }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    step="0.01" min="0" required>
                                @error('paid_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Discount and Tax -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label for="discount_amount" class="block text-sm font-medium text-gray-700">Discount Amount</label>
                                <input type="number" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', 0) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    step="0.01" min="0">
                                @error('discount_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tax_amount" class="block text-sm font-medium text-gray-700">Tax Amount</label>
                                <input type="number" name="tax_amount" id="tax_amount" value="{{ old('tax_amount', 0) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    step="0.01" min="0">
                                @error('tax_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
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

                        <!-- Sale Summary -->
                        <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Sale Summary</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Subtotal:</span>
                                    <span id="subtotal" class="ml-2 font-medium">$0.00</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Discount:</span>
                                    <span id="discountDisplay" class="ml-2 font-medium text-red-600">-$0.00</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Tax:</span>
                                    <span id="taxDisplay" class="ml-2 font-medium">+$0.00</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Total:</span>
                                    <span id="totalAmount" class="ml-2 text-xl font-bold text-green-600">$0.00</span>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Amount Paid:</span>
                                        <span id="paidDisplay" class="ml-2 font-medium">$0.00</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Change:</span>
                                        <span id="changeAmount" class="ml-2 font-medium text-blue-600">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.sales.index') : (auth()->user()->isPharmacist() ? route('pharmacist.sales.index') : route('sales-staff.sales.index')) }}"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" id="submitSale"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" disabled>
                                Complete Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sales form management
        let itemIndex = 0;
        let saleItems = [];
        const products = @json($products);

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            initializeCustomerSearch();
            initializeCalculations();
            updateSummary();
        });

        // Customer search and selection handling
        function initializeCustomerSearch() {
            const customerSearch = document.getElementById('customer_search');
            const customerSearchResults = document.getElementById('customerSearchResults');
            const customerIdInput = document.getElementById('customer_id');
            const selectedCustomer = document.getElementById('selectedCustomer');
            const customerDetails = document.getElementById('customerDetails');
            const clearCustomerBtn = document.getElementById('clearCustomer');
            const newCustomerBtn = document.getElementById('newCustomerBtn');
            const walkInBtn = document.getElementById('walkInBtn');
            const newCustomerFields = document.getElementById('newCustomerFields');
            const customerNameInput = document.getElementById('customer_name');

            let searchTimeout;

            // Customer search
            customerSearch.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();

                if (query.length < 2) {
                    hideSearchResults();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    searchCustomers(query);
                }, 300);
            });

            // New customer button
            newCustomerBtn.addEventListener('click', function() {
                if (newCustomerFields.classList.contains('hidden')) {
                    showNewCustomerForm();
                } else {
                    hideNewCustomerForm();
                }
            });

            // Walk-in button
            walkInBtn.addEventListener('click', function() {
                clearCustomerSelection();
                customerSearch.value = 'Walk-in Customer';
                customerSearch.disabled = true;
                hideNewCustomerForm();
            });

            // Clear customer button
            clearCustomerBtn.addEventListener('click', function() {
                clearCustomerSelection();
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#customer_search') && !e.target.closest('#customerSearchResults')) {
                    hideSearchResults();
                }
            });

            async function searchCustomers(query) {
                try {
                    const routePrefix = '{{ auth()->user()->isAdmin() ? "admin" : (auth()->user()->isPharmacist() ? "pharmacist" : "sales-staff") }}';
                    const response = await fetch(`/${routePrefix}/customers/search?query=${encodeURIComponent(query)}`);
                    const data = await response.json();

                    showSearchResults(data.customers);
                } catch (error) {
                    console.error('Customer search error:', error);
                    showSearchResults([]);
                }
            }

            function showSearchResults(customers) {
                if (customers.length === 0) {
                    customerSearchResults.innerHTML = '<div class="p-3 text-gray-500 text-center">No customers found</div>';
                } else {
                    customerSearchResults.innerHTML = customers.map(customer => `
                        <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0"
                             onclick="selectCustomer(${JSON.stringify(customer).replace(/"/g, '&quot;')})">
                            <div class="font-medium text-gray-900">${customer.name}</div>
                            <div class="text-sm text-gray-500">${customer.contact_info || 'No contact info'}</div>
                        </div>
                    `).join('');
                }

                customerSearchResults.classList.remove('hidden');
            }

            function hideSearchResults() {
                customerSearchResults.classList.add('hidden');
            }

            window.selectCustomer = function(customer) {
                customerIdInput.value = customer.id;
                customerSearch.value = customer.name;
                customerDetails.innerHTML = `
                    <strong>${customer.name}</strong><br>
                    ${customer.contact_info || 'No contact info'}
                `;
                selectedCustomer.classList.remove('hidden');
                hideSearchResults();
                hideNewCustomerForm();
                customerSearch.disabled = true;
            };

            function clearCustomerSelection() {
                customerIdInput.value = '';
                customerSearch.value = '';
                customerSearch.disabled = false;
                selectedCustomer.classList.add('hidden');
                hideSearchResults();
                hideNewCustomerForm();
            }

            function showNewCustomerForm() {
                newCustomerFields.classList.remove('hidden');
                customerNameInput.required = true;
                customerSearch.disabled = true;
                selectedCustomer.classList.add('hidden');
                newCustomerBtn.textContent = 'Cancel';
                newCustomerBtn.classList.remove('bg-green-500', 'hover:bg-green-700');
                newCustomerBtn.classList.add('bg-gray-500', 'hover:bg-gray-700');
            }

            function hideNewCustomerForm() {
                newCustomerFields.classList.add('hidden');
                customerNameInput.required = false;
                customerNameInput.value = '';
                document.getElementById('customer_phone').value = '';
                document.getElementById('customer_email').value = '';
                newCustomerBtn.textContent = 'New Customer';
                newCustomerBtn.classList.remove('bg-gray-500', 'hover:bg-gray-700');
                newCustomerBtn.classList.add('bg-green-500', 'hover:bg-green-700');

                if (!customerSearch.disabled) {
                    customerSearch.disabled = false;
                }
            }
        }

        // Initialize calculation event listeners
        function initializeCalculations() {
            ['discount_amount', 'tax_amount', 'paid_amount'].forEach(id => {
                document.getElementById(id).addEventListener('input', updateSummary);
            });
        }

        // Add product to sale (called from barcode scanner)
        window.addProductToSaleForm = function(product) {
            addSaleItem(product);
        };

        // Add sale item
        function addSaleItem(product = null, quantity = 1) {
            const container = document.getElementById('itemsContainer');
            const emptyMessage = document.getElementById('emptyItemsMessage');
            
            const item = {
                index: itemIndex++,
                product: product,
                quantity: quantity,
                unit_price: product ? product.selling_price : 0,
                discount_amount: 0,
                batch_id: null
            };

            saleItems.push(item);

            const itemHtml = createSaleItemHtml(item);
            container.insertAdjacentHTML('beforeend', itemHtml);
            
            emptyMessage.classList.add('hidden');
            addItemEventListeners(item.index);
            updateSummary();
            enableSubmitButton();
        }

        // Create sale item HTML
        function createSaleItemHtml(item) {
            const product = item.product;
            const batchOptions = product && product.batches ? product.batches.map(batch => 
                `<option value="${batch.id}">Batch: ${batch.batch_number} (Qty: ${batch.quantity}, Exp: ${batch.expiry_date})</option>`
            ).join('') : '';

            return `
                <div class="sale-item border border-gray-200 rounded-lg p-4" data-index="${item.index}">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-md font-medium text-gray-900">Item ${item.index + 1}</h4>
                        <button type="button" class="remove-item text-red-600 hover:text-red-900">Remove</button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Product</label>
                            <input type="hidden" name="items[${item.index}][product_id]" value="${product ? product.id : ''}">
                            <div class="mt-1 p-2 bg-gray-100 rounded border">
                                ${product ? `
                                    <div class="font-medium">${product.name} (${product.code})</div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        <span class="text-green-600 font-medium">Sell: $${product.selling_price}</span> |
                                        <span class="text-blue-600">Net: $${product.net_price || product.cost_price}</span> |
                                        <span class="text-gray-500">Cost: $${product.cost_price}</span>
                                    </div>
                                ` : 'No product selected'}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="items[${item.index}][quantity]" class="quantity-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                value="${item.quantity}" min="1" max="${product ? product.available_quantity : 999}" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                            <input type="number" name="items[${item.index}][unit_price]" class="unit-price-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                value="${item.unit_price}" step="0.01" min="0" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total</label>
                            <input type="text" class="item-total mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Batch (Optional)</label>
                            <select name="items[${item.index}][batch_id]" class="batch-select mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Auto-select (FIFO)</option>
                                ${batchOptions}
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Item Discount</label>
                            <input type="number" name="items[${item.index}][discount_amount]" class="item-discount-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                value="${item.discount_amount}" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            `;
        }

        // Add event listeners to item
        function addItemEventListeners(index) {
            const item = document.querySelector(`[data-index="${index}"]`);
            
            // Remove item
            item.querySelector('.remove-item').addEventListener('click', function() {
                removeSaleItem(index);
            });
            
            // Update calculations
            ['quantity-input', 'unit-price-input', 'item-discount-input'].forEach(className => {
                const input = item.querySelector('.' + className);
                if (input) {
                    input.addEventListener('input', function() {
                        calculateItemTotal(index);
                        updateSummary();
                    });
                }
            });
            
            // Calculate initial total
            calculateItemTotal(index);
        }

        // Remove sale item
        function removeSaleItem(index) {
            const item = document.querySelector(`[data-index="${index}"]`);
            item.remove();
            
            saleItems = saleItems.filter(item => item.index !== index);
            
            if (saleItems.length === 0) {
                document.getElementById('emptyItemsMessage').classList.remove('hidden');
                disableSubmitButton();
            }
            
            updateSummary();
        }

        // Calculate item total
        function calculateItemTotal(index) {
            const item = document.querySelector(`[data-index="${index}"]`);
            const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
            const unitPrice = parseFloat(item.querySelector('.unit-price-input').value) || 0;
            const discount = parseFloat(item.querySelector('.item-discount-input').value) || 0;
            const total = (quantity * unitPrice) - discount;
            
            item.querySelector('.item-total').value = '$' + total.toFixed(2);
        }

        // Update sale summary
        function updateSummary() {
            let subtotal = 0;
            
            document.querySelectorAll('.sale-item').forEach(item => {
                const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
                const unitPrice = parseFloat(item.querySelector('.unit-price-input').value) || 0;
                const discount = parseFloat(item.querySelector('.item-discount-input').value) || 0;
                subtotal += (quantity * unitPrice) - discount;
            });
            
            const globalDiscount = parseFloat(document.getElementById('discount_amount').value) || 0;
            const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
            const total = subtotal - globalDiscount + tax;
            const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
            const change = Math.max(0, paid - total);
            
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('discountDisplay').textContent = '-$' + globalDiscount.toFixed(2);
            document.getElementById('taxDisplay').textContent = '+$' + tax.toFixed(2);
            document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
            document.getElementById('paidDisplay').textContent = '$' + paid.toFixed(2);
            document.getElementById('changeAmount').textContent = '$' + change.toFixed(2);
        }

        // Manual add item
        document.getElementById('addManualItem').addEventListener('click', function() {
            // For manual items, we'll create a basic item without product
            addSaleItem(null);
        });

        // Enable/disable submit button
        function enableSubmitButton() {
            document.getElementById('submitSale').disabled = false;
        }

        function disableSubmitButton() {
            document.getElementById('submitSale').disabled = true;
        }
    </script>
</x-app-layout>

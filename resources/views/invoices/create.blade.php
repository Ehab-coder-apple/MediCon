<x-app-layout>
    <x-slot name="header">
        <div style="background: linear-gradient(to right, #165A54, #0d3d38); padding: 8px 24px; margin: 0; display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <div class="flex items-center">
                <a href="{{ route('invoices.index') }}" style="margin-right: 16px; color: #a7f3d0; text-decoration: none; transition: color 0.2s;" class="hover:text-white">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h2 class="font-bold text-2xl text-white leading-tight">
                    <i class="fas fa-cash-register mr-3"></i>
                    Point of Sale System
                </h2>
            </div>
            <div style="color: #a7f3d0; font-size: 14px;">
                <i class="fas fa-calendar mr-2"></i>
                {{ date('M d, Y g:i A') }}
            </div>
        </div>
    </x-slot>

    <div class="bg-gray-100 min-h-screen -mx-6 px-6 py-6">
        <div class="max-w-full mx-auto">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <h3 class="font-bold mb-2">Validation Errors:</h3>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
                @csrf

                <!-- POS Layout: Left Panel (Products) + Right Panel (Invoice) -->
                <div style="display: flex; gap: 20px; min-height: 100vh;">
                    <!-- LEFT PANEL: Product Selection -->
                    <div style="flex: 2; background: #f8f9fa; padding: 10px; border: 1px solid #ddd;">
                        <div style="background: white; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; height: 100vh; display: flex; flex-direction: column;">
                            <!-- Header -->
                            <div style="background: linear-gradient(to right, #022C22, #013d33); padding: 16px 24px; border-radius: 15px 15px 0 0;">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-bold text-white flex items-center">
                                        <i class="fas fa-capsules mr-3 text-2xl"></i>
                                        Product Catalog
                                    </h3>
                                    <div style="color: #a8d5cc; font-size: 14px;">
                                        {{ $products->count() }} products available
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Search Bar -->
                            <div class="p-6 border-b border-gray-100">
                                <div class="relative">
                                    <input type="text" id="product_search"
                                           style="width: 100%; padding-left: 56px; padding-right: 64px; padding-top: 16px; padding-bottom: 16px; font-size: 18px; border-radius: 12px; border: 2px solid #e5e7eb; transition: all 0.2s; background-color: #f9fafb;"
                                           onfocus="this.style.borderColor='#022C22'; this.style.boxShadow='0 0 0 4px rgba(2, 44, 34, 0.1)'; this.style.backgroundColor='white';"
                                           onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'; this.style.backgroundColor='#f9fafb';"
                                           placeholder="Search products by name, code, or scan barcode..."
                                           autocomplete="off">
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center">
                                        <i class="fas fa-search text-gray-400 text-xl"></i>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-5 flex items-center space-x-2">
                                        <!-- Barcode Simulator Button (Mobile/Simulator) -->
                                        <button type="button" id="invoiceBarcodeSimulator" style="background-color: #2e7d32; color: #ffffff; padding: 12px 14px; border-radius: 8px; border: 2px solid #1b5e20; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.backgroundColor='#1b5e20'; this.style.boxShadow='0 2px 8px rgba(46, 125, 50, 0.5)';" onmouseout="this.style.backgroundColor='#2e7d32'; this.style.boxShadow='none';" title="Open barcode simulator (Mobile)">
                                            <i class="fas fa-mobile-alt" style="font-size: 22px; font-weight: bold;"></i>
                                        </button>
                                        <!-- Barcode Scanner Button (Camera/Scanner) -->
                                        <button type="button" id="invoiceBarcodeScanner" style="background-color: #1976d2; color: #ffffff; padding: 12px 14px; border-radius: 8px; border: 2px solid #0d47a1; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.backgroundColor='#0d47a1'; this.style.boxShadow='0 2px 8px rgba(25, 118, 210, 0.5)';" onmouseout="this.style.backgroundColor='#1976d2'; this.style.boxShadow='none';" title="Start barcode scanner (Camera)">
                                            <i class="fas fa-camera" style="font-size: 22px; font-weight: bold;"></i>
                                        </button>
                                    </div>

                                    <!-- Search Results Dropdown -->
                                    <div id="searchDropdown" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-80 overflow-y-auto hidden mt-2">
                                        <div id="searchResults" class="py-2">
                                            <!-- Search results will be populated here -->
                                        </div>
                                        <div id="noResults" class="px-4 py-6 text-gray-500 text-center hidden">
                                            <i class="fas fa-search text-gray-300 text-2xl mb-2"></i>
                                            <div>No products found</div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Products Grid Container -->
                            <div class="flex-1 overflow-hidden">
                                <div class="p-6 h-full">

                                    <!-- Product Grid -->
                                    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 h-full overflow-y-auto pr-2" id="productGrid">
                                        @foreach($products as $product)
                                            <div class="product-card group bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-2xl p-3 hover:border-indigo-300 hover:shadow-2xl transition-all duration-300 cursor-pointer transform hover:scale-105 hover:-translate-y-2"
                                                 data-product-id="{{ $product->id }}"
                                                 data-product-name="{{ $product->name }}"
                                                 data-product-code="{{ $product->code }}"
                                                 data-product-price="{{ $product->selling_price }}"
                                                 data-product-stock="{{ $product->active_quantity }}"
                                                 onclick="console.log('Direct onclick fired for {{ $product->name }}'); addProductToInvoice({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->code }}', {{ $product->selling_price }}, {{ $product->active_quantity }});">

                                                <!-- Product Info -->
                                                <div class="space-y-1">
                                                    <h4 class="font-bold text-gray-900 text-sm leading-tight group-hover:text-indigo-600 transition-colors line-clamp-2">
                                                        {{ $product->name }}
                                                    </h4>
                                                    <p class="text-xs text-gray-500 font-medium">{{ $product->code }}</p>

                                                    <!-- Price & Stock -->
                                                    <div class="pt-1">
                                                        <div style="font-size: 24px; font-weight: 900; color: #022C22; transition: color 0.2s; line-height: 1;" class="group-hover:text-green-800">
                                                            ${{ number_format($product->selling_price, 2) }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-0.5">
                                                            Stock: <span class="font-semibold {{ $product->active_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $product->active_quantity }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Add Button -->
                                                    <div class="pt-2">
                                                        <div style="background: linear-gradient(to right, #022C22, #013d33); color: white; text-align: center; padding: 6px 0; border-radius: 12px; font-weight: 600; font-size: 13px; transition: all 0.2s; cursor: pointer;" class="group-hover:shadow-lg">
                                                            <i class="fas fa-plus mr-2"></i>Add to Cart
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: Invoice Terminal -->
                    <div style="flex: 1; background: #ffffff; padding: 10px; border: 2px solid #10b981; border-radius: 10px;">
                        <div style="background: white; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); border: 2px solid #10b981; height: 100vh; display: flex; flex-direction: column; position: relative; z-index: 1;">

                            <!-- Invoice Header -->
                            <div style="background: linear-gradient(to right, #10b981, #059669); padding: 10px; border-radius: 15px 15px 0 0;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <h4 style="color: white; font-size: 16px; font-weight: bold; margin: 0;">
                                        🧾 Current Sale
                                    </h4>
                                    <div style="color: #d1fae5; font-size: 10px;">
                                        <div>Invoice #{{ 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) }}</div>
                                        <div id="itemCountDisplay" style="margin-top: 1px;">0 items</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Customer Selection (MOVED OUTSIDE OVERFLOW CONTAINER) -->
                            <div style="background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 6px; position: relative; z-index: 100;">
                                <label style="display: block; font-size: 10px; font-weight: 600; color: #374151; margin-bottom: 2px;">Customer ({{ count($customers) }} available)</label>
                                <select name="customer_id" id="customer_id"
                                        style="width: 100%; border-radius: 4px; border: 1px solid #d1d5db; padding: 6px; background: white; font-size: 11px; height: auto;">
                                    <option value="">👤 Walk-in</option>
                                    @forelse($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @empty
                                        <option value="" disabled>No customers available</option>
                                    @endforelse
                                </select>
                            </div>

                            <!-- Invoice Items -->
                            <div style="flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                                <div style="background: #f9fafb; padding: 4px 8px; border-bottom: 1px solid #e5e7eb;">
                                    <div style="display: grid; grid-template-columns: 3fr 0.8fr 0.8fr 1fr 0.4fr; gap: 8px; font-size: 11px; font-weight: bold; color: #6b7280; text-transform: uppercase;">
                                        <div>Product</div>
                                        <div style="text-align: center;">Qty</div>
                                        <div style="text-align: center;">Price</div>
                                        <div style="text-align: center;">Total</div>
                                        <div style="text-align: center;">×</div>
                                    </div>
                                </div>

                                <div style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; align-items: stretch; justify-content: flex-start;" id="selectedItemsContainer">
                                    <div id="selectedItemsBody" style="width: 100%; display: flex; flex-direction: column; align-items: stretch;">
                                        <div id="emptyRow" style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #9ca3af; padding: 40px 20px;">
                                            <div style="width: 40px; height: 40px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; font-size: 18px;">
                                                🛒
                                            </div>
                                            <h3 style="font-size: 14px; font-weight: 600; color: #6b7280; margin: 0 0 4px 0;">Cart is Empty</h3>
                                            <p style="font-size: 11px; text-align: center; margin: 0; color: #9ca3af;">Select products to add</p>
                                        </div>
                                    </div>
                                </div>


                            <!-- Invoice Summary & Totals -->
                            <div style="background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 6px; display: flex; flex-direction: column; gap: 4px; position: relative; z-index: 50;">

                                <!-- Totals Display -->
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1px 0;">
                                        <span style="color: #6b7280; font-weight: 500; font-size: 10px;">Subtotal</span>
                                        <span style="font-size: 11px; font-weight: bold; color: #111827;" id="subtotalDisplay">$0.00</span>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1px 0;">
                                        <span style="color: #6b7280; font-weight: 500; font-size: 10px;">Tax</span>
                                        <span style="font-size: 11px; font-weight: bold; color: #111827;" id="taxDisplay">$0.00</span>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px; background: #ecfdf5; border-radius: 4px; border: 1px solid #10b981; margin-top: 2px;">
                                        <span style="font-size: 12px; font-weight: 900; color: #065f46;">TOTAL</span>
                                        <span style="font-size: 14px; font-weight: 900; color: #065f46;" id="totalDisplay">$0.00</span>
                                    </div>
                                </div>



                                <!-- Action Buttons -->
                                <div style="display: flex; flex-direction: column; gap: 3px; padding-top: 4px;">
                                    <button type="submit"
                                            style="width: 100%; background: linear-gradient(to right, #10b981, #059669); color: white; font-weight: 700; padding: 6px 10px; border-radius: 4px; font-size: 11px; border: none; cursor: pointer; transition: all 0.2s;"
                                            id="createInvoiceBtn" disabled>
                                        🛒 COMPLETE SALE
                                    </button>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3px;">
                                        <button type="button"
                                                style="background: #6b7280; color: white; font-weight: 600; padding: 4px 6px; border-radius: 4px; font-size: 9px; border: none; cursor: pointer;">
                                            💾 Hold
                                        </button>

                                        <button type="button"
                                                style="background: #dc2626; color: white; font-weight: 600; padding: 4px 6px; border-radius: 4px; font-size: 9px; border: none; cursor: pointer;">
                                            🗑️ Clear
                                        </button>
                                    </div>
                                </div>

                                <!-- Hidden Fields -->
                                <input type="hidden" name="invoice_date" value="{{ date('Y-m-d') }}">
                                <input type="hidden" name="delivery_method" value="pickup">
                                <input type="hidden" name="delivery_address" value="">
                                <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                                <input type="hidden" name="discount_percentage" id="discount_percentage" value="0">
                                <input type="hidden" name="tax_percentage" id="tax_percentage" value="0">
                                <input type="hidden" name="delivery_fee" id="delivery_fee" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
        /* Cache buster: {{ time() }} */

        /* Ensure no badges or pseudo-elements appear on product cards */
        .product-card::before,
        .product-card::after {
            display: none !important;
        }

        /* Hide any span elements that might be badges */
        .product-card span.bg-red-500,
        .product-card span.bg-green-500,
        .product-card span.rounded-full {
            display: none !important;
        }

        /* Additional safeguard: hide all badge-like elements */
        .product-card span[class*="bg-"],
        .product-card span[class*="rounded-full"] {
            display: none !important;
        }

        /* Ensure product icon container doesn't have any pseudo-elements */
        .product-card > div:first-child::before,
        .product-card > div:first-child::after {
            display: none !important;
        }

        /* Hide any div elements with green backgrounds that might be badges */
        .product-card div[style*="background: #10b981"],
        .product-card div[style*="background: #22c55e"],
        .product-card div[style*="background: #4ade80"],
        .product-card div[style*="background: #86efac"],
        .product-card div[style*="bg-green"] {
            display: none !important;
        }

        /* Ensure Font Awesome icons don't create additional visual elements */
        .product-card i.fas::before {
            content: none !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Cache buster: {{ time() }}
        console.log('🚀 JavaScript is loading! - Version {{ time() }}');

        // Global variables
        let selectedItems = [];
        let itemCounter = 0;
        let selectedItemsBody, emptyRow, createInvoiceBtn;

        // Function to add product to invoice
        window.addProductToInvoice = function(productId, productName, productCode, productPrice, productStock) {
            console.log('=== addProductToInvoice called ===');
            console.log('Parameters:', {productId, productName, productCode, productPrice, productStock});

            if (productStock <= 0) {
                showToast('This product is out of stock!', 'error');
                return;
            }

            // Check if product already exists in selected items
            const existingItem = selectedItems.find(item => item.productId == productId);

            if (existingItem) {
                if (existingItem.quantity >= productStock) {
                    showToast('Cannot add more items. Insufficient stock!', 'error');
                    return;
                }
                existingItem.quantity++;
                updateItemRow(existingItem);
                console.log('Updated existing item:', existingItem);
            } else {
                const newItem = {
                    id: ++itemCounter,
                    productId: productId,
                    productName: productName,
                    productCode: productCode,
                    quantity: 1,
                    unitPrice: parseFloat(productPrice),
                    maxStock: productStock
                };
                selectedItems.push(newItem);
                addItemRow(newItem);
                console.log('Added new item:', newItem);
            }

            updateSummary();
            updateCreateButton();
            showToast(`Added: ${productName} - $${productPrice}`, 'success');
            console.log('Current selected items:', selectedItems);
        };

        // Helper function to show toast notifications
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'error' ? 'bg-red-500' : 'bg-green-500';
            toast.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded shadow-lg z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Function to add item row to invoice
        function addItemRow(item) {
            console.log('=== addItemRow called ===', item);

            if (emptyRow) {
                emptyRow.style.display = 'none';
            }

            const itemDiv = document.createElement('div');
            itemDiv.id = `item-row-${item.id}`;
            itemDiv.className = 'border-b border-gray-100 py-3 px-6 hover:bg-gray-50 transition-colors';

            itemDiv.innerHTML = `
                <div style="display: grid; grid-template-columns: 3fr 0.8fr 0.8fr 1fr 0.4fr; gap: 6px; align-items: center; padding: 4px 0; border-bottom: 1px solid #f3f4f6;">
                    <div style="display: flex; align-items: center; min-width: 0;">
                        <div style="width: 24px; height: 24px; background: #e0e7ff; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 8px; flex-shrink: 0;">
                            💊
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-weight: 600; color: #111827; font-size: 13px; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.productName}</div>
                            <div style="font-size: 10px; color: #6b7280; line-height: 1;">${item.productCode}</div>
                        </div>
                        <input type="hidden" name="items[${item.id}][product_id]" value="${item.productId}">
                    </div>
                    <div style="text-align: center;">
                        <input type="number"
                               style="width: 50px; padding: 4px; border: 1px solid #d1d5db; border-radius: 6px; text-align: center; font-weight: 600; font-size: 12px;"
                               value="${item.quantity}"
                               min="1"
                               max="${item.maxStock}"
                               data-item-id="${item.id}"
                               name="items[${item.id}][quantity]"
                               class="quantity-input">
                    </div>
                    <div style="text-align: center;">
                        <input type="number"
                               style="width: 55px; padding: 4px; border: 1px solid #d1d5db; border-radius: 6px; text-align: center; font-weight: 600; font-size: 12px;"
                               value="${item.unitPrice.toFixed(2)}"
                               step="0.01"
                               min="0"
                               data-item-id="${item.id}"
                               name="items[${item.id}][unit_price]"
                               class="price-input">
                    </div>
                    <div style="text-align: center;">
                        <span style="font-weight: bold; color: #059669; font-size: 13px;" class="item-total" data-item-id="${item.id}">$${(item.quantity * item.unitPrice).toFixed(2)}</span>
                    </div>
                    <div style="text-align: center;">
                        <button type="button"
                                style="background: #fee2e2; color: #dc2626; border: none; padding: 4px; border-radius: 4px; cursor: pointer; font-size: 10px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;"
                                data-item-id="${item.id}"
                                class="remove-item">
                            ×
                        </button>
                    </div>
                </div>
            `;

            if (selectedItemsBody) {
                selectedItemsBody.appendChild(itemDiv);
                console.log('Item appended successfully!');

                // Add event listeners for the new row
                const quantityInput = itemDiv.querySelector('.quantity-input');
                const priceInput = itemDiv.querySelector('.price-input');
                const removeBtn = itemDiv.querySelector('.remove-item');

                quantityInput.addEventListener('change', function() {
                    const newQuantity = parseInt(this.value);
                    if (newQuantity > item.maxStock) {
                        showToast(`Maximum available quantity is ${item.maxStock}`, 'error');
                        this.value = item.maxStock;
                        item.quantity = item.maxStock;
                    } else if (newQuantity < 1) {
                        this.value = 1;
                        item.quantity = 1;
                    } else {
                        item.quantity = newQuantity;
                    }
                    updateItemTotal(item);
                    updateSummary();
                });

                priceInput.addEventListener('change', function() {
                    const newPrice = parseFloat(this.value);
                    if (newPrice < 0) {
                        this.value = 0;
                        item.unitPrice = 0;
                    } else {
                        item.unitPrice = newPrice;
                    }
                    updateItemTotal(item);
                    updateSummary();
                });

                removeBtn.addEventListener('click', function() {
                    removeItem(item.id);
                });
            }
        }

        // Function to update existing item row
        function updateItemRow(item) {
            const row = document.getElementById(`item-row-${item.id}`);
            if (row) {
                const quantityInput = row.querySelector('.quantity-input');
                quantityInput.value = item.quantity;
                updateItemTotal(item);
            }
        }

        // Function to update item total
        function updateItemTotal(item) {
            const totalSpan = document.querySelector(`.item-total[data-item-id="${item.id}"]`);
            if (totalSpan) {
                const total = item.quantity * item.unitPrice;
                totalSpan.textContent = `$${total.toFixed(2)}`;
            }
        }

        // Function to remove item
        function removeItem(itemId) {
            const itemIndex = selectedItems.findIndex(item => item.id === itemId);
            if (itemIndex > -1) {
                selectedItems.splice(itemIndex, 1);
                const row = document.getElementById(`item-row-${itemId}`);
                if (row) row.remove();

                if (selectedItems.length === 0 && emptyRow) {
                    emptyRow.style.display = 'table-row';
                }

                updateSummary();
                updateCreateButton();
                showToast('Item removed', 'success');
            }
        }

        // Function to update summary calculations
        function updateSummary() {
            console.log('=== updateSummary called ===');
            console.log('Selected items:', selectedItems);

            const subtotal = selectedItems.reduce((sum, item) => sum + (item.quantity * item.unitPrice), 0);
            const tax = subtotal * 0.08; // 8% tax
            const total = subtotal + tax;

            console.log('Calculated values:', { subtotal, tax, total });

            // Update subtotal display
            const subtotalDisplay = document.getElementById('subtotalDisplay');
            if (subtotalDisplay) {
                subtotalDisplay.textContent = `$${subtotal.toFixed(2)}`;
                console.log('Updated subtotal display');
            } else {
                console.log('❌ subtotalDisplay element not found');
            }

            // Update tax display
            const taxDisplay = document.getElementById('taxDisplay');
            if (taxDisplay) {
                taxDisplay.textContent = `$${tax.toFixed(2)}`;
                console.log('Updated tax display');
            } else {
                console.log('❌ taxDisplay element not found');
            }

            // Update total display
            const totalDisplay = document.getElementById('totalDisplay');
            if (totalDisplay) {
                totalDisplay.textContent = `$${total.toFixed(2)}`;
                console.log('Updated total display');
            } else {
                console.log('❌ totalDisplay element not found');
            }

            // Update item count in header
            const itemCount = selectedItems.reduce((sum, item) => sum + item.quantity, 0);
            const itemCountDisplay = document.getElementById('itemCountDisplay');
            if (itemCountDisplay) {
                itemCountDisplay.textContent = `${itemCount} item${itemCount !== 1 ? 's' : ''}`;
                console.log('Updated item count display');
            } else {
                console.log('❌ itemCountDisplay element not found');
            }
            console.log('Total items:', itemCount);
        }

        // Function to update create button state
        function updateCreateButton() {
            if (createInvoiceBtn) {
                createInvoiceBtn.disabled = selectedItems.length === 0;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOM loaded successfully!');

            // Get DOM elements
            selectedItemsBody = document.getElementById('selectedItemsBody');
            emptyRow = document.getElementById('emptyRow');
            createInvoiceBtn = document.getElementById('createInvoiceBtn');

            // Search elements
            const productSearch = document.getElementById('product_search');
            const searchDropdown = document.getElementById('searchDropdown');
            const searchResults = document.getElementById('searchResults');
            const noResults = document.getElementById('noResults');

            console.log('Elements found:', {
                selectedItemsBody: !!selectedItemsBody,
                emptyRow: !!emptyRow,
                createInvoiceBtn: !!createInvoiceBtn,
                productSearch: !!productSearch,
                searchDropdown: !!searchDropdown
            });

            // All products data for search
            const allProducts = [
                @foreach($products as $product)
                {
                    id: {{ $product->id }},
                    name: "{{ $product->name }}",
                    code: "{{ $product->code }}",
                    barcode: "{{ $product->barcode ?? '' }}",
                    price: {{ $product->selling_price }},
                    stock: {{ $product->active_quantity }},
                    category: "{{ $product->category ?? '' }}"
                },
                @endforeach
            ];

            console.log('Loaded products for search:', allProducts.length);
            console.log('Sample product data:', allProducts[0]);

            // Search functionality
            let searchTimeout;
            productSearch.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();

                // Clear previous timeout
                clearTimeout(searchTimeout);

                if (searchTerm.length < 2) {
                    hideSearchDropdown();
                    return;
                }

                // Debounce search
                searchTimeout = setTimeout(() => {
                    performSearch(searchTerm);
                }, 300);
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!productSearch.contains(e.target) && !searchDropdown.contains(e.target)) {
                    hideSearchDropdown();
                }
            });

            // Hide dropdown on escape key
            productSearch.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideSearchDropdown();
                    this.blur();
                }
            });

            function performSearch(searchTerm) {
                console.log('Searching for:', searchTerm);

                const filteredProducts = allProducts.filter(product => {
                    return product.name.toLowerCase().includes(searchTerm) ||
                           product.code.toLowerCase().includes(searchTerm) ||
                           product.barcode.toLowerCase().includes(searchTerm) ||
                           product.category.toLowerCase().includes(searchTerm);
                });

                console.log('Found products:', filteredProducts.length);
                displaySearchResults(filteredProducts);
            }

            function displaySearchResults(products) {
                searchResults.innerHTML = '';

                if (products.length === 0) {
                    noResults.classList.remove('hidden');
                    searchResults.classList.add('hidden');
                } else {
                    noResults.classList.add('hidden');
                    searchResults.classList.remove('hidden');

                    products.slice(0, 10).forEach(product => { // Limit to 10 results
                        const resultItem = document.createElement('div');
                        resultItem.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                        resultItem.innerHTML = `
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">${product.name}</div>
                                    <div class="text-sm text-gray-500">${product.code} • ${product.category}</div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="font-bold text-emerald-600">$${product.price.toFixed(2)}</div>
                                    <div class="text-xs text-gray-500">Stock: ${product.stock}</div>
                                </div>
                            </div>
                        `;

                        resultItem.addEventListener('click', function() {
                            selectProduct(product);
                        });

                        searchResults.appendChild(resultItem);
                    });
                }

                showSearchDropdown();
            }

            function selectProduct(product) {
                console.log('Selected product from dropdown:', product);

                // Add product to invoice
                addProductToInvoice(product.id, product.name, product.code, product.price, product.stock);

                // Clear search and hide dropdown
                productSearch.value = '';
                hideSearchDropdown();

                // Focus back to search for next product
                setTimeout(() => {
                    productSearch.focus();
                }, 100);
            }

            function showSearchDropdown() {
                searchDropdown.classList.remove('hidden');
            }

            function hideSearchDropdown() {
                searchDropdown.classList.add('hidden');
            }

            // Add a visual indicator that JavaScript is working
            const indicator = document.createElement('div');
            indicator.id = 'js-indicator';
            indicator.className = 'fixed bottom-4 left-4 bg-blue-500 text-white px-3 py-1 rounded text-sm z-50';
            indicator.textContent = '✅ JavaScript Active';
            document.body.appendChild(indicator);

            // Remove indicator after 3 seconds
            setTimeout(() => {
                const ind = document.getElementById('js-indicator');
                if (ind) ind.remove();
            }, 3000);



            // Hold button functionality
            const holdBtn = document.querySelector('button[type="button"]:nth-of-type(1)');
            if (holdBtn) {
                holdBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (selectedItems.length === 0) {
                        showToast('Please add items before holding the invoice', 'error');
                        return;
                    }

                    // Add a hidden field to indicate this is a hold action
                    const holdInput = document.createElement('input');
                    holdInput.type = 'hidden';
                    holdInput.name = 'action';
                    holdInput.value = 'hold';
                    document.getElementById('invoiceForm').appendChild(holdInput);

                    // Submit the form
                    document.getElementById('invoiceForm').submit();
                });
            }

            // Clear button functionality
            const clearBtn = document.querySelector('button[type="button"]:nth-of-type(2)');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (selectedItems.length === 0) {
                        showToast('No items to clear', 'info');
                        return;
                    }

                    if (confirm('Are you sure you want to clear all items?')) {
                        selectedItems = [];
                        selectedItemsBody.innerHTML = '';
                        emptyRow.style.display = '';
                        updateSummary();
                        updateCreateButton();
                        showToast('All items cleared', 'success');
                    }
                });
            }

            // Barcode Scanner Integration
            const invoiceBarcodeSimulatorBtn = document.getElementById('invoiceBarcodeSimulator');
            const invoiceBarcodeScannerBtn = document.getElementById('invoiceBarcodeScanner');

            if (invoiceBarcodeSimulatorBtn) {
                invoiceBarcodeSimulatorBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('🔌 Opening barcode simulator for invoice...');
                    window.open('/barcode-simulator.html', 'barcodeSimulator', 'width=600,height=700,resizable=yes');
                });
            }

            if (invoiceBarcodeScannerBtn) {
                invoiceBarcodeScannerBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('📱 Starting barcode scanner for invoice...');
                    startInvoiceBarcodeScanner();
                });
            }

            // Function to handle barcode input for invoices
            window.addProductToInvoiceFromBarcode = function(product) {
                console.log('🔍 Adding product from barcode:', product);
                if (product && product.id) {
                    addProductToInvoice(
                        product.id,
                        product.name,
                        product.code,
                        product.selling_price || product.price,
                        product.available_quantity || product.stock || product.active_quantity
                    );
                }
            };

            // Barcode scanner for invoices
            async function startInvoiceBarcodeScanner() {
                const query = prompt('Enter barcode or product code:');
                if (!query) return;

                try {
                    const url = `/sales/product-lookup?query=${encodeURIComponent(query)}`;
                    console.log('📡 Fetching product from:', url);

                    const response = await fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        alert('Error fetching product: ' + response.statusText);
                        return;
                    }

                    const data = await response.json();
                    console.log('📡 Response data:', data);

                    if (data.products && data.products.length > 0) {
                        const product = data.products[0];
                        console.log('✅ Product found:', product.name);
                        addProductToInvoiceFromBarcode(product);
                    } else {
                        alert('Barcode not found: ' + query);
                    }
                } catch (error) {
                    console.error('Search error:', error);
                    alert('Search failed. Please try again.');
                }
            }

            // Form submission handler
            const invoiceForm = document.getElementById('invoiceForm');
            if (invoiceForm) {
                invoiceForm.addEventListener('submit', function(e) {
                    console.log('=== Form Submission ===');
                    console.log('Selected items:', selectedItems);
                    console.log('Form data:', new FormData(this));

                    // Log all form fields
                    const formData = new FormData(this);
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`);
                    }
                });
            }

            // Initialize
            updateSummary();
            updateCreateButton();
        });
    </script>
    @endpush

</x-app-layout>

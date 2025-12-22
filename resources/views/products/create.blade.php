<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Product') }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                }
            @endphp
            <a href="{{ route($routePrefix . 'products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Create New Product
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Add a new product to your inventory
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    <form action="{{ route($routePrefix . 'products.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category (Legacy)</label>
                                <input type="text" name="category" id="category" value="{{ old('category') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                @error('category')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Legacy category field - will be replaced by structured categories below
                                </p>
                            </div>

                            <!-- Manufacturer -->
                            <div>
                                <label for="manufacturer" class="block text-sm font-medium text-gray-700">Manufacturer</label>
                                <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="e.g., Bayer Healthcare, Pfizer, Johnson & Johnson" required>
                                @error('manufacturer')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Enter the pharmaceutical company or manufacturer name
                                </p>
                            </div>

                            <!-- Product Code -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700">Product Code</label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category Dropdown -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Product Category</label>
                                <select name="category_id" id="category_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        onchange="updateSubcategories()">
                                    <option value="">Select a category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Subcategory Dropdown -->
                            <div>
                                <label for="subcategory_id" class="block text-sm font-medium text-gray-700">Product Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select a subcategory</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}"
                                                data-category="{{ $subcategory->category_id }}"
                                                {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}
                                                style="display: none;">
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Location Dropdown -->
                            <div>
                                <label for="location_id" class="block text-sm font-medium text-gray-700">Storage Location</label>
                                <select name="location_id" id="location_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select a location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->full_location }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    📍 Physical location where this product is stored
                                </p>
                            </div>

                            <!-- Alert Quantity -->
                            <div>
                                <label for="alert_quantity" class="block text-sm font-medium text-gray-700">Alert Quantity</label>
                                <input type="number" name="alert_quantity" id="alert_quantity" value="{{ old('alert_quantity', 10) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    min="0" required>
                                @error('alert_quantity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Days On Hand -->
                            <div>
                                <label for="days_on_hand" class="block text-sm font-medium text-gray-700">
                                    Days On Hand (DOH)
                                    <span class="text-xs text-gray-500 ml-1">- Optional, will auto-calculate based on sales</span>
                                </label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" name="days_on_hand" id="days_on_hand" value="{{ old('days_on_hand') }}"
                                        class="flex-1 border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500"
                                        min="0" placeholder="Auto-calculated">
                                    <button type="button" id="calculateDOH"
                                        class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-blue-50 text-blue-700 text-sm hover:bg-blue-100">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        Calculate
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Days On Hand indicates how many days current stock will last based on average daily sales.
                                    <span class="font-medium">7-14 days: Low stock</span>,
                                    <span class="font-medium">15-30 days: Good stock</span>,
                                    <span class="font-medium">30+ days: Overstocked</span>
                                </p>
                                @error('days_on_hand')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cost Price -->
                            <div>
                                <label for="cost_price" class="block text-sm font-medium text-gray-700">Cost Price</label>
                                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    step="0.01" min="0" required>
                                @error('cost_price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Original purchase price from supplier
                                </p>
                            </div>

                            <!-- Selling Price -->
                            <div>
                                <label for="selling_price" class="block text-sm font-medium text-gray-700">Selling Price</label>
                                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    step="0.01" min="0" required>
                                @error('selling_price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Price charged to customers (used in invoicing)
                                </p>
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-blue-500">💡</span>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">About Net Price</h3>
                                    <p class="text-sm text-blue-700 mt-1">
                                        The <strong>Net Price</strong> will be automatically calculated based on your stock receiving records,
                                        including any bonus quantities received from suppliers. This gives you the true cost per unit
                                        after considering promotional offers and free units.
                                    </p>
                                    <p class="text-xs text-blue-600 mt-2">
                                        Net Price = Total Cost Paid ÷ Total Quantity Received (including bonus units)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="mt-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Active Product
                                </label>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route($routePrefix . 'products.index') }}"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category dropdown functionality
        function updateSubcategories() {
            const categorySelect = document.getElementById('category_id');
            const subcategorySelect = document.getElementById('subcategory_id');
            const selectedCategoryId = categorySelect.value;

            // Reset subcategory dropdown
            subcategorySelect.value = '';

            // Show/hide subcategory options based on selected category
            const subcategoryOptions = subcategorySelect.querySelectorAll('option[data-category]');
            subcategoryOptions.forEach(option => {
                if (selectedCategoryId === '' || option.dataset.category === selectedCategoryId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        }

        // Initialize subcategories on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSubcategories();
        });

        // DOH Calculation functionality
        document.getElementById('calculateDOH').addEventListener('click', function() {
            const button = this;
            const input = document.getElementById('days_on_hand');
            const productCode = document.getElementById('code').value;

            if (!productCode) {
                alert('Please enter a product code first to calculate DOH.');
                return;
            }

            // Show loading state
            button.disabled = true;
            const originalContent = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Calculating...';

            // For new products, we can't calculate DOH yet, so show a default value
            setTimeout(() => {
                // Since this is a new product, set a default DOH based on alert quantity
                const alertQuantity = parseInt(document.getElementById('alert_quantity').value) || 10;
                const estimatedDOH = Math.max(alertQuantity * 2, 30); // Estimate 2x alert quantity or minimum 30 days

                input.value = estimatedDOH;

                // Show info message
                const infoDiv = document.createElement('div');
                infoDiv.className = 'mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700';
                infoDiv.innerHTML = `
                    <strong>Estimated DOH:</strong> ${estimatedDOH} days<br>
                    <small>This is an estimate for new products. Actual DOH will be calculated based on sales data after the product has sales history.</small>
                `;

                // Remove any existing info div
                const existingInfo = input.parentNode.parentNode.querySelector('.mt-2.p-2.bg-blue-50');
                if (existingInfo) {
                    existingInfo.remove();
                }

                // Add new info div
                input.parentNode.parentNode.appendChild(infoDiv);

                // Reset button
                button.disabled = false;
                button.innerHTML = originalContent;
            }, 1000);
        });

        // Auto-calculate DOH when alert quantity changes
        document.getElementById('alert_quantity').addEventListener('input', function() {
            const alertQuantity = parseInt(this.value) || 10;
            const dohInput = document.getElementById('days_on_hand');

            if (!dohInput.value) {
                // Auto-suggest DOH based on alert quantity
                const suggestedDOH = Math.max(alertQuantity * 2, 30);
                dohInput.placeholder = `Suggested: ${suggestedDOH} days`;
            }
        });

        // Initialize placeholder
        document.addEventListener('DOMContentLoaded', function() {
            const alertQuantity = parseInt(document.getElementById('alert_quantity').value) || 10;
            const suggestedDOH = Math.max(alertQuantity * 2, 30);
            document.getElementById('days_on_hand').placeholder = `Suggested: ${suggestedDOH} days`;
        });
    </script>
</x-app-layout>

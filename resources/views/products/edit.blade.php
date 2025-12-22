<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Product') }}
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
                        Edit Product: {{ $product->name }}
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Update product information
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    <form action="{{ route($routePrefix . 'products.update', $product) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category (Legacy)</label>
                                <input type="text" name="category" id="category" value="{{ old('category', $product->category) }}"
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
                                <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer', $product->manufacturer) }}"
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
                                <input type="text" name="code" id="code" value="{{ old('code', $product->code) }}"
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
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                                {{ old('subcategory_id', $product->subcategory_id) == $subcategory->id ? 'selected' : '' }}
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
                                        <option value="{{ $location->id }}" {{ old('location_id', $product->location_id) == $location->id ? 'selected' : '' }}>
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
                                <input type="number" name="alert_quantity" id="alert_quantity" value="{{ old('alert_quantity', $product->alert_quantity) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    min="0" required>
                                @error('alert_quantity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cost Price -->
                            <div>
                                <label for="cost_price" class="block text-sm font-medium text-gray-700">Cost Price</label>
                                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price', $product->cost_price) }}"
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
                                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price', $product->selling_price) }}"
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

                        <!-- Current Pricing Information -->
                        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <div class="flex-shrink-0">
                                    <span class="text-blue-500">💰</span>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Current Pricing Analysis</h3>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-3 border">
                                    <div class="text-xs text-gray-500 uppercase tracking-wide">Cost Price</div>
                                    <div class="text-lg font-medium text-gray-900">${{ number_format($product->cost_price, 2) }}</div>
                                    <div class="text-xs text-gray-500">Original purchase price</div>
                                </div>

                                <div class="bg-white rounded-lg p-3 border border-blue-200">
                                    <div class="text-xs text-blue-500 uppercase tracking-wide">Net Price</div>
                                    <div class="text-lg font-medium text-blue-600">${{ number_format($product->net_price, 2) }}</div>
                                    <div class="text-xs text-blue-500">After bonus adjustments</div>
                                    @if($product->net_price < $product->cost_price)
                                        <div class="text-xs text-green-600 font-medium mt-1">
                                            💰 Savings: ${{ number_format($product->cost_price - $product->net_price, 2) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="bg-white rounded-lg p-3 border border-green-200">
                                    <div class="text-xs text-green-500 uppercase tracking-wide">Selling Price</div>
                                    <div class="text-lg font-medium text-green-600">${{ number_format($product->selling_price, 2) }}</div>
                                    <div class="text-xs text-green-500">Customer invoice price</div>
                                    <div class="text-xs {{ $product->net_profit_margin >= 20 ? 'text-green-600' : ($product->net_profit_margin >= 10 ? 'text-yellow-600' : 'text-red-600') }} font-medium mt-1">
                                        Net Margin: {{ number_format($product->net_profit_margin, 1) }}%
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 text-xs text-blue-600">
                                <strong>Note:</strong> Net Price is automatically calculated from your stock receiving records and includes bonus quantities.
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="mt-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                    {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
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
                                Update Product
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
    </script>
</x-app-layout>

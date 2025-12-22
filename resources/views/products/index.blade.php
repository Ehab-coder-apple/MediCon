<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center py-4 px-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Products') }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                }
            @endphp
            <div class="flex space-x-6 ml-12">
                <a href="{{ route($routePrefix . 'products.import') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow-sm transition-colors">
                    📥 Import Products
                </a>
                <a href="{{ route($routePrefix . 'products.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-sm transition-colors">
                    ➕ Add New Product
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Product Management
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Manage your product inventory and stock levels
                    </p>
                </div>

                <!-- Filters Section -->
                <div class="p-6 lg:p-8 bg-gray-50 border-b border-gray-200">
                    <form method="GET" action="{{ route($routePrefix . 'products.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                       placeholder="Product name, code, manufacturer..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Category Filter -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                                <select name="category_id" id="category_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onchange="updateSubcategoryFilter()">
                                    <option value="">All Categories</option>
                                    @forelse($categories ?? [] as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @empty
                                        <option disabled>No categories available</option>
                                    @endforelse
                                </select>
                            </div>

                            <!-- Subcategory Filter -->
                            <div>
                                <label for="subcategory_id" class="block text-sm font-medium text-gray-700">Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Subcategories</option>
                                    @forelse($subcategories ?? [] as $subcategory)
                                        <option value="{{ $subcategory->id }}"
                                                data-category="{{ $subcategory->category_id }}"
                                                {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}
                                                style="display: none;">
                                            {{ $subcategory->name }}
                                        </option>
                                    @empty
                                        <option disabled>No subcategories available</option>
                                    @endforelse
                                </select>
                            </div>

                            <!-- Location Filter -->
                            <div>
                                <label for="location_id" class="block text-sm font-medium text-gray-700">Location</label>
                                <select name="location_id" id="location_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Locations</option>
                                    @forelse($locations ?? [] as $location)
                                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->full_location }}
                                        </option>
                                    @empty
                                        <option disabled>No locations available</option>
                                    @endforelse
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <div class="flex space-x-2">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Apply Filters
                                </button>
                                <a href="{{ route($routePrefix . 'products.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Clear Filters
                                </a>
                            </div>
                            <div class="text-sm text-gray-600">
                                Showing {{ $products->count() }} of {{ $products->total() }} products
                            </div>
                        </div>
                    </form>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Manufacturer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Code
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pricing
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        DOH
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            @if($product->description)
                                                <div class="text-sm text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            @if($product->getRelation('category'))
                                                {{ $product->getRelation('category')->name }}
                                            @else
                                                {{ $product->getAttributes()['category'] ?? 'N/A' }}
                                            @endif
                                        </div>
                                        @if($product->getRelation('subcategory'))
                                            <div class="text-xs text-gray-500">{{ $product->getRelation('subcategory')->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($product->getRelation('location'))
                                            <div class="text-sm font-medium text-gray-900">
                                                📍 {{ $product->getRelation('location')->full_location }}
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-400">No location assigned</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $product->manufacturer ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $product->code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-green-600">${{ number_format($product->selling_price, 2) }}</div>
                                        <div class="text-sm font-medium text-blue-600">Net: ${{ number_format($product->net_price, 2) }}</div>
                                        <div class="text-xs text-gray-500">Cost: ${{ number_format($product->cost_price, 2) }}</div>
                                        @if($product->net_price < $product->cost_price)
                                            <div class="text-xs text-green-600 font-medium">
                                                💰 Bonus Savings: ${{ number_format($product->cost_price - $product->net_price, 2) }}
                                            </div>
                                        @endif
                                        <div class="text-xs {{ $product->net_profit_margin >= 20 ? 'text-green-600' : ($product->net_profit_margin >= 10 ? 'text-yellow-600' : 'text-red-600') }}">
                                            Net Margin: {{ number_format($product->net_profit_margin, 1) }}%
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $product->active_quantity }}</div>
                                        @if($product->is_low_stock)
                                            <div class="text-xs text-red-500">Low Stock!</div>
                                        @endif
                                        <div class="text-xs text-gray-500">Alert: {{ $product->alert_quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $doh = $product->days_on_hand ?? $product->calculated_days_on_hand;
                                            $dohStatus = $product->doh_status;
                                        @endphp
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $doh }} days
                                        </div>
                                        <div class="text-xs
                                            @if($dohStatus === 'critical') text-red-600
                                            @elseif($dohStatus === 'warning') text-yellow-600
                                            @elseif($dohStatus === 'good') text-green-600
                                            @else text-blue-600
                                            @endif">
                                            {{ $product->doh_status_text }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Avg: {{ number_format($product->average_daily_sales, 1) }}/day
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($product->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route($routePrefix . 'products.show', $product) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="{{ route($routePrefix . 'products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route($routePrefix . 'products.destroy', $product) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" 
                                                onclick="return confirm('Are you sure you want to delete this product?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No products found. <a href="{{ route($routePrefix . 'products.create') }}" class="text-blue-600 hover:text-blue-900">Create your first product</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category filter dropdown functionality
        function updateSubcategoryFilter() {
            const categorySelect = document.getElementById('category_id');
            const subcategorySelect = document.getElementById('subcategory_id');
            const selectedCategoryId = categorySelect.value;
            const currentSubcategoryValue = subcategorySelect.value;

            // Show/hide subcategory options based on selected category
            const subcategoryOptions = subcategorySelect.querySelectorAll('option[data-category]');
            let hasVisibleOptions = false;

            subcategoryOptions.forEach(option => {
                if (selectedCategoryId === '' || option.dataset.category === selectedCategoryId) {
                    option.style.display = 'block';
                    hasVisibleOptions = true;
                } else {
                    option.style.display = 'none';
                }
            });

            // If the currently selected subcategory is not visible, reset to empty
            if (currentSubcategoryValue !== '') {
                const selectedOption = subcategorySelect.querySelector(`option[value="${currentSubcategoryValue}"]`);
                if (selectedOption && selectedOption.style.display === 'none') {
                    subcategorySelect.value = '';
                }
            }
        }

        // Initialize subcategories on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSubcategoryFilter();
        });
    </script>
</x-app-layout>

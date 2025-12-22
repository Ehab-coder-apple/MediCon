<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-cogs mr-2"></i>Product Display Settings
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <h3 class="font-bold mb-2">Errors:</h3>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Display Strategy Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-list mr-2 text-slate-700"></i>Display Strategy
                    </h3>

                    <form method="POST" action="{{ route('admin.product-display-settings.update-strategy') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Select how products should be displayed on the Point of Sale:
                            </label>

                            <div class="space-y-3">
                                @foreach (['fast_moving' => 'Fast Moving Products', 'high_stock' => 'High Stock Products', 'nearly_expired' => 'Nearly Expired Products', 'custom_selection' => 'Custom Selection'] as $value => $label)
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50" :class="{'bg-blue-50 border-blue-300': '{{ $setting->display_strategy }}' === '{{ $value }}'}">
                                        <input type="radio" name="display_strategy" value="{{ $value }}" {{ $setting->display_strategy === $value ? 'checked' : '' }} class="mr-3">
                                        <span class="font-medium text-gray-900">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="products_limit" class="block text-sm font-medium text-gray-700 mb-2">
                                Number of Products to Display:
                            </label>
                            <input type="number" id="products_limit" name="products_limit" min="5" max="100" value="{{ $setting->products_limit }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Between 5 and 100 products</p>
                        </div>

                        <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>Save Strategy
                        </button>
                    </form>
                </div>
            </div>

            <!-- Featured Products Section (only show if custom_selection is selected) -->
            @if ($setting->isCustomSelection())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-star mr-2 text-yellow-500"></i>Featured Products
                        </h3>

                        <!-- Add Product Form -->
                        <form method="POST" action="{{ route('admin.product-display-settings.add-featured') }}" class="mb-6">
                            @csrf
                            <div class="flex gap-2">
                                <select name="product_id" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                    <option value="">Select a product to add...</option>
                                    @foreach ($allProducts as $product)
                                        @if (!$featuredProducts->pluck('product_id')->contains($product->id))
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code }})</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Add
                                </button>
                            </div>
                        </form>

                        <!-- Featured Products List -->
                        @if ($featuredProducts->count() > 0)
                            <div class="space-y-2">
                                @foreach ($featuredProducts as $featured)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="flex items-center flex-1">
                                            <span class="text-sm font-semibold text-gray-500 mr-3 w-8">{{ $loop->iteration }}</span>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $featured->product->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $featured->product->code }}</p>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.product-display-settings.remove-featured', $featured) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium" onclick="return confirm('Remove this product?')">
                                                <i class="fas fa-trash mr-1"></i>Remove
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-6">No featured products selected yet.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>


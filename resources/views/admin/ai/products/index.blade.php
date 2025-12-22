@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">🔍 Product Information</h1>
            <p class="text-gray-600">Manage pharmaceutical product data</p>
        </div>
        <a href="{{ route('admin.ai.dashboard') }}" class="px-6 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-green-600">
        <h3 class="text-lg font-bold text-gray-900 mb-4">🔍 Search Products</h3>
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" name="search" placeholder="Search product name or code..." class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Category::all() as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" style="background-color: #9333ea; color: white;" class="px-8 py-2 font-semibold rounded-lg hover:shadow-lg transition inline-block">
                    🔍 Search
                </button>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition border-t-4 border-green-600">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $product->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">Code: {{ $product->code }}</p>
                    
                    <div class="space-y-2 mb-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Manufacturer:</span>
                            <span class="font-semibold text-gray-900">{{ $product->manufacturer ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Price:</span>
                            <span class="font-semibold text-green-600">${{ number_format($product->selling_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Stock:</span>
                            <span class="font-semibold text-gray-900">{{ $product->active_quantity ?? 0 }} units</span>
                        </div>
                    </div>

                    @if($product->information)
                        <div class="bg-blue-50 p-3 rounded mb-4 text-sm">
                            <p class="text-gray-600">✓ Information available</p>
                        </div>
                    @else
                        <div class="bg-yellow-50 p-3 rounded mb-4 text-sm">
                            <p class="text-gray-600">⚠️ No information yet</p>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <a href="{{ route('admin.ai.products.show', $product->id) }}" class="flex-1 px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition text-center text-sm">
                            View
                        </a>
                        <a href="{{ route('admin.ai.products.edit', $product->id) }}" class="flex-1 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition text-center text-sm">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-lg text-gray-600">📭 No products found</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $products->links() }}
    </div>
</div>
@endsection


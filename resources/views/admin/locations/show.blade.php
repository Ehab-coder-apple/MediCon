<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center py-4 px-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Location Details') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.locations.edit', $location) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-sm transition-colors">
                    ✏️ Edit Location
                </a>
                <a href="{{ route('admin.locations.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow-sm transition-colors">
                    ← Back to Locations
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-medium text-gray-900">
                                📍 {{ $location->full_location }}
                            </h1>
                            @if($location->description)
                                <p class="mt-2 text-gray-500">{{ $location->description }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($location->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Location Details -->
                <div class="p-6 lg:p-8 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Location Breakdown</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Zone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $location->zone }}</dd>
                        </div>
                        @if($location->cabinet_shelf)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Cabinet/Shelf</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $location->cabinet_shelf }}</dd>
                        </div>
                        @endif
                        @if($location->row_level)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Row/Level</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $location->row_level }}</dd>
                        </div>
                        @endif
                        @if($location->position_side)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Position/Side</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $location->position_side }}</dd>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Products at this Location -->
                <div class="p-6 lg:p-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">
                            Products at this Location ({{ $location->products->count() }})
                        </h2>
                        @if($location->products->count() > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Occupied
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Empty
                            </span>
                        @endif
                    </div>

                    @if($location->products->count() > 0)
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
                                            Code
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock
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
                                    @foreach($location->products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $product->manufacturer }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
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
                                            <div class="text-sm text-gray-900">{{ $product->code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $product->active_quantity ?? 0 }}</div>
                                            @if($product->is_low_stock)
                                                <div class="text-xs text-red-500">Low Stock!</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($product->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-6xl mb-4">📦</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Products Assigned</h3>
                            <p class="text-gray-500">This location is currently empty. Products can be assigned to this location when creating or editing products.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

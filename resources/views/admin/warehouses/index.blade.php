<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center py-4 px-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Warehouse Management') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.warehouses.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-sm transition-colors">
                    ➕ Add New Warehouse
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        🏭 Warehouse Management
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Manage logical stock locations (Main, On Shelf, Expired, Damaged, Returns, etc.)
                    </p>
                </div>

                <!-- Filters Section -->
                <div class="p-6 lg:p-8 bg-gray-50 border-b border-gray-200">
                    <form method="GET" action="{{ route('admin.warehouses.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Name or type..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Type Filter -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="type" id="type" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Types</option>
                                    <option value="main" {{ request('type') == 'main' ? 'selected' : '' }}>Main</option>
                                    <option value="on_shelf" {{ request('type') == 'on_shelf' ? 'selected' : '' }}>On Shelf</option>
                                    <option value="received" {{ request('type') == 'received' ? 'selected' : '' }}>Received</option>
                                    <option value="expired" {{ request('type') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="damaged" {{ request('type') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                    <option value="returns" {{ request('type') == 'returns' ? 'selected' : '' }}>Returns</option>
                                    <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                            </div>

                            <!-- Branch Filter -->
                            <div>
                                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                                <select name="branch_id" id="branch_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sellable Filter -->
                            <div>
                                <label for="is_sellable" class="block text-sm font-medium text-gray-700">Sellable</label>
                                <select name="is_sellable" id="is_sellable" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All</option>
                                    <option value="1" {{ request('is_sellable') === '1' ? 'selected' : '' }}>Sellable Only</option>
                                    <option value="0" {{ request('is_sellable') === '0' ? 'selected' : '' }}>Non-sellable Only</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Showing {{ $warehouses->count() }} of {{ $warehouses->total() }} warehouses
                            </div>
                            <div class="flex space-x-2">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Apply Filters
                                </button>
                                <a href="{{ route('admin.warehouses.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Clear
                                </a>
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
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Branch
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sellable
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($warehouses as $warehouse)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $warehouse->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $warehouse->id }}
                                            @if($warehouse->is_system)
                                                · System
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst(str_replace('_', ' ', $warehouse->type)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $warehouse->branch?->name ?? 'All Branches' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($warehouse->is_sellable)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Sellable
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Not Sellable
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            @unless($warehouse->is_system)
                                                <form method="POST" action="{{ route('admin.warehouses.destroy', $warehouse) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                                            onclick="return confirm('Are you sure you want to delete this warehouse?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No warehouses found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $warehouses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


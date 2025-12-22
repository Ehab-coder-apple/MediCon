<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Branch Management') }}
        </h2>
    </x-slot>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">🏪 Pharmacy Branches</h1>
            <p class="text-gray-600 mt-2">Manage pharmacy locations and geofence settings</p>
        </div>
        <a href="{{ route('admin.branches.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition transform hover:scale-105">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
            </svg>
            Add Branch
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-blue-600">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filter Branches</h3>
        <form method="GET">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>✓ Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>✗ Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Geofencing</label>
                    <select name="geofencing" class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">All</option>
                        <option value="required" {{ request('geofencing') === 'required' ? 'selected' : '' }}>📍 Required</option>
                        <option value="not_required" {{ request('geofencing') === 'not_required' ? 'selected' : '' }}>Optional</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mt-4">
                <button type="submit" style="background-color: #9333ea; color: white;" class="px-8 py-2 font-semibold rounded-lg hover:shadow-lg transition inline-block">
                    🔍 Filter
                </button>
                <a href="{{ route('admin.branches.index') }}" style="background-color: #9ca3af; color: white;" class="px-8 py-2 font-semibold rounded-lg hover:shadow-lg transition inline-block text-center">
                    ↻ Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Branches Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($branches as $branch)
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 p-6 border-t-4 {{ $branch->is_active ? 'border-green-500' : 'border-red-500' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $branch->name }}</h3>
                        <p class="text-sm text-gray-500 font-mono">{{ $branch->code }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}
                    ">
                        {{ $branch->is_active ? '✓ Active' : '✗ Inactive' }}
                    </span>
                </div>

                <div class="space-y-3 mb-6 text-sm text-gray-700 bg-gray-50 p-4 rounded-lg">
                    <p class="flex items-center">
                        <span class="font-semibold text-gray-900 w-24">📍 City:</span>
                        <span>{{ $branch->city }}</span>
                    </p>
                    <p class="flex items-center">
                        <span class="font-semibold text-gray-900 w-24">🗺️ GPS:</span>
                        <span class="font-mono text-xs">{{ number_format($branch->latitude, 4) }}, {{ number_format($branch->longitude, 4) }}</span>
                    </p>
                    <p class="flex items-center">
                        <span class="font-semibold text-gray-900 w-24">📏 Radius:</span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded font-semibold">{{ $branch->geofence_radius }}m</span>
                    </p>
                    <p class="flex items-center">
                        <span class="font-semibold text-gray-900 w-24">🔒 Geofence:</span>
                        <span class="px-2 py-1 rounded text-xs font-bold
                            {{ $branch->requires_geofencing ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}
                        ">
                            {{ $branch->requires_geofencing ? '📍 Required' : 'Optional' }}
                        </span>
                    </p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.branches.show', $branch) }}" class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                        View
                    </a>
                    <a href="{{ route('admin.branches.edit', $branch) }}" class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-yellow-600 text-white text-sm font-semibold rounded-lg hover:bg-yellow-700 shadow-md hover:shadow-lg transition">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Edit
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-gray-50 rounded-lg">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM14 2a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0V6h-1a1 1 0 110-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-gray-600 text-lg font-semibold mb-4">No branches found</p>
                <a href="{{ route('admin.branches.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                    </svg>
                    Create First Branch
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $branches->links() }}
    </div>
</div>
</x-app-layout>


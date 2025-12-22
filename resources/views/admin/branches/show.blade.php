<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Branch Details') }}
        </h2>
    </x-slot>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <a href="{{ route('admin.branches.index') }}" class="text-blue-600 hover:text-blue-900 mb-6 inline-block">
            ← Back to Branches
        </a>

        <!-- Branch Details Card -->
        <div class="bg-white rounded-lg shadow p-8 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $branch->name }}</h1>
                    <p class="text-gray-600 mt-2">{{ $branch->code }}</p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}
                ">
                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <!-- Address -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <p class="mt-1 text-gray-900">{{ $branch->address }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <p class="mt-1 text-gray-900">{{ $branch->city }}, {{ $branch->state }}, {{ $branch->country }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                    <p class="mt-1 text-gray-900">{{ $branch->postal_code }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <p class="mt-1 text-gray-900">{{ $branch->phone ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- GPS & Geofence -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">GPS & Geofence Settings</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Latitude</label>
                        <p class="mt-1 text-gray-900 font-mono">{{ $branch->latitude }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Longitude</label>
                        <p class="mt-1 text-gray-900 font-mono">{{ $branch->longitude }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Geofence Radius</label>
                        <p class="mt-1 text-gray-900">{{ $branch->geofence_radius }} meters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Geofencing Status</label>
                        <p class="mt-1">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $branch->requires_geofencing ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}
                            ">
                                {{ $branch->requires_geofencing ? 'Required' : 'Optional' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-gray-900">{{ $branch->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Manager</label>
                        <p class="mt-1 text-gray-900">{{ $branch->manager_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <a href="{{ route('admin.branches.edit', $branch) }}" class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                ✎ Edit Branch
            </a>
            <a href="{{ route('admin.branches.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                Back
            </a>
        </div>
    </div>
</div>
</x-app-layout>


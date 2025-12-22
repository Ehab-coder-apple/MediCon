<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Basic Information -->
    <div class="md:col-span-2">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-blue-500">📋 Basic Information</h3>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Branch Name *</label>
        <input type="text" name="name" value="{{ old('name', $branch->name ?? '') }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @error('name') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Branch Code *</label>
        <input type="text" name="code" value="{{ old('code', $branch->code ?? '') }}"
            {{ isset($branch) ? 'readonly' : 'required' }}
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition {{ isset($branch) ? 'bg-gray-100 cursor-not-allowed' : '' }}">
        @error('code') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
        <input type="text" name="address" value="{{ old('address', $branch->address ?? '') }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @error('address') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
        <input type="text" name="city" value="{{ old('city', $branch->city ?? '') }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @error('city') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">State *</label>
        <input type="text" name="state" value="{{ old('state', $branch->state ?? '') }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @error('state') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
        <input type="text" name="country" value="{{ old('country', $branch->country ?? '') }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @error('country') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $branch->postal_code ?? '') }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @error('postal_code') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <!-- GPS & Geofence -->
    <div class="md:col-span-2">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-green-500">📍 GPS & Geofence Settings</h3>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Latitude *</label>
        <input type="number" name="latitude" step="0.000001" value="{{ old('latitude', $branch->latitude ?? '') }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
        @error('latitude') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Longitude *</label>
        <input type="number" name="longitude" step="0.000001" value="{{ old('longitude', $branch->longitude ?? '') }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
        @error('longitude') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Geofence Radius (meters) *</label>
        <input type="number" name="geofence_radius" min="50" max="5000" value="{{ old('geofence_radius', $branch->geofence_radius ?? 300) }}" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
        <p class="text-xs text-gray-500 mt-2">💡 Range: 50m - 5000m (default: 300m)</p>
        @error('geofence_radius') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="flex items-center mt-2 p-4 bg-green-50 border border-green-200 rounded-lg cursor-pointer hover:bg-green-100 transition">
            <input type="checkbox" name="requires_geofencing" value="1"
                {{ old('requires_geofencing', $branch->requires_geofencing ?? false) ? 'checked' : '' }}
                class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span class="ml-3 text-sm font-medium text-gray-700">Require Geofencing</span>
        </label>
        <p class="text-xs text-gray-600 mt-2 ml-8">✓ Employees must check-in within geofence radius</p>
    </div>

    <!-- Contact Information -->
    <div class="md:col-span-2">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-purple-500">📞 Contact Information</h3>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $branch->phone ?? '') }}"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
        @error('phone') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input type="email" name="email" value="{{ old('email', $branch->email ?? '') }}"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
        @error('email') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Manager Name</label>
        <input type="text" name="manager_name" value="{{ old('manager_name', $branch->manager_name ?? '') }}"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
        @error('manager_name') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <!-- Status -->
    <div class="md:col-span-2">
        <label class="flex items-center p-4 bg-blue-50 border border-blue-200 rounded-lg cursor-pointer hover:bg-blue-100 transition">
            <input type="checkbox" name="is_active" value="1"
                {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}
                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="ml-3 text-sm font-medium text-gray-700">Active Status</span>
        </label>
        <p class="text-xs text-gray-600 mt-2 ml-8">✓ Enable this branch for employee check-ins</p>
    </div>
</div>


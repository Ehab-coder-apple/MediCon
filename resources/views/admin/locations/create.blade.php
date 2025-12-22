<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center py-4 px-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Location') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.locations.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow-sm transition-colors">
                    ← Back to Locations
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        📍 Create New Location
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Add a new physical location for product storage
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    <form method="POST" action="{{ route('admin.locations.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Zone -->
                            <div>
                                <label for="zone" class="block text-sm font-medium text-gray-700">Zone *</label>
                                <input type="text" name="zone" id="zone" value="{{ old('zone') }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    placeholder="e.g., Front Store, Storage Room, Fridge"
                                    required>
                                @error('zone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Main area or zone (e.g., Front Store, OTC Section, Prescription Zone)
                                </p>
                            </div>

                            <!-- Cabinet/Shelf -->
                            <div>
                                <label for="cabinet_shelf" class="block text-sm font-medium text-gray-700">Cabinet/Shelf</label>
                                <input type="text" name="cabinet_shelf" id="cabinet_shelf" value="{{ old('cabinet_shelf') }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    placeholder="e.g., Shelf A, Rack 4, Drawer 1">
                                @error('cabinet_shelf')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Specific cabinet, shelf, or storage unit
                                </p>
                            </div>

                            <!-- Row/Level -->
                            <div>
                                <label for="row_level" class="block text-sm font-medium text-gray-700">Row/Level</label>
                                <input type="text" name="row_level" id="row_level" value="{{ old('row_level') }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    placeholder="e.g., Row 2, Level 1, Top Shelf">
                                @error('row_level')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Row number or level within the cabinet/shelf
                                </p>
                            </div>

                            <!-- Position/Side -->
                            <div>
                                <label for="position_side" class="block text-sm font-medium text-gray-700">Position/Side</label>
                                <input type="text" name="position_side" id="position_side" value="{{ old('position_side') }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    placeholder="e.g., Position 3, Left, Right">
                                @error('position_side')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Specific position or side within the row/level
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                placeholder="Additional notes about this location...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Sort Order -->
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    min="0">
                                @error('sort_order')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Order for sorting locations (0 = default)
                                </p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                                            {{ old('is_active', true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600">Active</span>
                                    </label>
                                </div>
                                @error('is_active')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Location Preview -->
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Location Preview:</h3>
                            <div id="location-preview" class="text-lg font-medium text-indigo-600">
                                Enter location details above
                            </div>
                        </div>

                        @if($errors->has('location'))
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="text-sm text-red-600">
                                    {{ $errors->first('location') }}
                                </div>
                            </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.locations.index') }}"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update location preview as user types
        function updateLocationPreview() {
            const zone = document.getElementById('zone').value;
            const cabinet = document.getElementById('cabinet_shelf').value;
            const row = document.getElementById('row_level').value;
            const position = document.getElementById('position_side').value;
            
            const parts = [zone, cabinet, row, position].filter(part => part.trim() !== '');
            const preview = parts.length > 0 ? parts.join(' > ') : 'Enter location details above';
            
            document.getElementById('location-preview').textContent = preview;
        }

        // Add event listeners to all location fields
        document.addEventListener('DOMContentLoaded', function() {
            const fields = ['zone', 'cabinet_shelf', 'row_level', 'position_side'];
            fields.forEach(field => {
                document.getElementById(field).addEventListener('input', updateLocationPreview);
            });
            
            // Initial preview update
            updateLocationPreview();
        });
    </script>
</x-app-layout>

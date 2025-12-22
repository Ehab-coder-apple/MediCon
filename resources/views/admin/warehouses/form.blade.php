<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-blue-500">📦 Warehouse Details</h3>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
        <input type="text" name="name" value="{{ old('name', $warehouse->name ?? '') }}" required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        @error('name') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
        <select name="type" required
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            @foreach($types as $value => $label)
                <option value="{{ $value }}" {{ old('type', $warehouse->type ?? 'main') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Branch (optional)</label>
        <select name="branch_id"
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ old('branch_id', $warehouse->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg cursor-pointer hover:bg-green-100 transition">
            <input type="checkbox" name="is_sellable" value="1"
                   {{ old('is_sellable', $warehouse->is_sellable ?? false) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span class="ml-3 text-sm font-medium text-gray-700">Sellable (can be used for POS / On-Shelf stock)</span>
        </label>
        @error('is_sellable') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes / Specifications</label>
        <textarea name="specifications" rows="4"
                  class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">{{ old('specifications', $warehouse->specifications ?? '') }}</textarea>
        @error('specifications') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
    </div>
</div>


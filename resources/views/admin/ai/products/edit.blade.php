@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">✏️ Edit Product Information</h1>
            <p class="text-gray-600">{{ $product->name }}</p>
        </div>
        <a href="{{ route('admin.ai.products.show', $product->id) }}" class="px-6 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition">
            ← Back
        </a>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('admin.ai.products.update', $product->id) }}" class="bg-white rounded-lg shadow-md p-8 border-l-4 border-blue-600">
        @csrf
        @method('PUT')

        <!-- Active Ingredients -->
        <div class="mb-6">
            <label class="block text-gray-900 font-bold mb-2">💊 Active Ingredients (comma-separated)</label>
            <textarea name="active_ingredients" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="e.g., Paracetamol, Caffeine">{{ $product->information?->active_ingredients ? implode(', ', $product->information->active_ingredients) : '' }}</textarea>
        </div>

        <!-- Indications -->
        <div class="mb-6">
            <label class="block text-gray-900 font-bold mb-2">📋 Indications (comma-separated)</label>
            <textarea name="indications" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="e.g., Fever, Headache, Pain relief">{{ $product->information?->indications ? implode(', ', $product->information->indications) : '' }}</textarea>
        </div>

        <!-- Side Effects -->
        <div class="mb-6">
            <label class="block text-gray-900 font-bold mb-2">⚠️ Side Effects (comma-separated)</label>
            <textarea name="side_effects" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="e.g., Nausea, Dizziness, Allergic reaction">{{ $product->information?->side_effects ? implode(', ', $product->information->side_effects) : '' }}</textarea>
        </div>

        <!-- Dosage Information -->
        <div class="mb-6">
            <label class="block text-gray-900 font-bold mb-2">💉 Dosage Information</label>
            <textarea name="dosage_information" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="e.g., Adults: 500mg every 4-6 hours, not exceeding 4g per day">{{ $product->information?->dosage_information ?? '' }}</textarea>
        </div>

        <!-- Contraindications -->
        <div class="mb-6">
            <label class="block text-gray-900 font-bold mb-2">🚫 Contraindications (comma-separated)</label>
            <textarea name="contraindications" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="e.g., Pregnancy, Liver disease, Kidney disease">{{ $product->information?->contraindications ? implode(', ', $product->information->contraindications) : '' }}</textarea>
        </div>

        <!-- Drug Interactions -->
        <div class="mb-6">
            <label class="block text-gray-900 font-bold mb-2">🔗 Drug Interactions (comma-separated)</label>
            <textarea name="drug_interactions" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="e.g., Warfarin, Aspirin, NSAIDs">{{ $product->information?->drug_interactions ? implode(', ', $product->information->drug_interactions) : '' }}</textarea>
        </div>

        <!-- Storage Requirements -->
        <div class="mb-6">
            <label class="block text-gray-900 font-bold mb-2">🧊 Storage Requirements (comma-separated)</label>
            <textarea name="storage_requirements" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="e.g., Store at room temperature, Keep away from moisture, Protect from light">{{ $product->information?->storage_requirements ? implode(', ', $product->information->storage_requirements) : '' }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4">
            <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                ✓ Save Information
            </button>
            <a href="{{ route('admin.ai.products.show', $product->id) }}" class="flex-1 px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition text-center">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection


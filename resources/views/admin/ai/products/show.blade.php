@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">🔍 Product Information</h1>
            <p class="text-gray-600">{{ $product->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.ai.products.edit', $product->id) }}" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                ✏️ Edit
            </a>
            <a href="{{ route('admin.ai.products.index') }}" class="px-6 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition">
                ← Back
            </a>
        </div>
    </div>

    <!-- Product Basic Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-green-600">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600 text-sm font-medium">Product Name</p>
                <p class="text-2xl font-bold text-gray-900">{{ $product->name }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium">Product Code</p>
                <p class="text-2xl font-bold text-gray-900">{{ $product->code }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium">Manufacturer</p>
                <p class="text-2xl font-bold text-gray-900">{{ $product->manufacturer ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium">Price</p>
                <p class="text-2xl font-bold text-green-600">${{ number_format($product->selling_price, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium">Available Stock</p>
                <p class="text-2xl font-bold text-gray-900">{{ $product->active_quantity ?? 0 }} units</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium">Category</p>
                <p class="text-2xl font-bold text-gray-900">{{ $product->category?->name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Pharmaceutical Information from OpenAI -->
    @if($medicalInfo)
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4">
                <h3 class="text-xl font-bold">🤖 Medical Information (from OpenAI)</h3>
            </div>
            <div class="p-6 space-y-6">
                @if(isset($medicalInfo['activeIngredient']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Active Ingredient</h4>
                        <p class="text-gray-600">{{ $medicalInfo['activeIngredient'] }}</p>
                    </div>
                @endif

                @if(isset($medicalInfo['therapeuticClass']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Therapeutic Class</h4>
                        <p class="text-gray-600">{{ $medicalInfo['therapeuticClass'] }}</p>
                    </div>
                @endif

                @if(isset($medicalInfo['mechanism']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Mechanism of Action</h4>
                        <p class="text-gray-600">{{ $medicalInfo['mechanism'] }}</p>
                    </div>
                @endif

                @if(isset($medicalInfo['indications']) && is_array($medicalInfo['indications']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Indications (Medical Uses)</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            @foreach($medicalInfo['indications'] as $indication)
                                <li>{{ $indication }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(isset($medicalInfo['dosage']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Dosage</h4>
                        <p class="text-gray-600">{{ $medicalInfo['dosage'] }}</p>
                    </div>
                @endif

                @if(isset($medicalInfo['administration']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Administration</h4>
                        <p class="text-gray-600">{{ $medicalInfo['administration'] }}</p>
                    </div>
                @endif

                @if(isset($medicalInfo['contraindications']) && is_array($medicalInfo['contraindications']))
                    <div class="bg-red-50 p-4 rounded border-l-4 border-red-600">
                        <h4 class="text-lg font-bold text-red-900 mb-2">⚠️ Contraindications</h4>
                        <ul class="list-disc list-inside space-y-1 text-red-800">
                            @foreach($medicalInfo['contraindications'] as $contra)
                                <li>{{ $contra }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(isset($medicalInfo['sideEffects']) && is_array($medicalInfo['sideEffects']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Side Effects</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            @foreach($medicalInfo['sideEffects'] as $effect)
                                <li>{{ $effect }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(isset($medicalInfo['interactions']) && is_array($medicalInfo['interactions']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Drug Interactions</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            @foreach($medicalInfo['interactions'] as $interaction)
                                <li>{{ $interaction }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(isset($medicalInfo['warnings']) && is_array($medicalInfo['warnings']))
                    <div class="bg-yellow-50 p-4 rounded border-l-4 border-yellow-600">
                        <h4 class="text-lg font-bold text-yellow-900 mb-2">⚠️ Warnings</h4>
                        <ul class="list-disc list-inside space-y-1 text-yellow-800">
                            @foreach($medicalInfo['warnings'] as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(isset($medicalInfo['pharmacokinetics']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Pharmacokinetics</h4>
                        <p class="text-gray-600">{{ $medicalInfo['pharmacokinetics'] }}</p>
                    </div>
                @endif

                @if(isset($medicalInfo['clinicalEfficacy']))
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Clinical Efficacy</h4>
                        <p class="text-gray-600">{{ $medicalInfo['clinicalEfficacy'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Stored Pharmaceutical Information -->
    @if($product->information)
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4">
                <h3 class="text-xl font-bold">💊 Stored Pharmaceutical Information</h3>
            </div>
            <div class="p-6 space-y-6">
                @if($product->information->active_ingredients)
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Active Ingredients</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->information->active_ingredients as $ingredient)
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">{{ $ingredient }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($product->information->indications)
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Indications</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            @foreach($product->information->indications as $indication)
                                <li>{{ $indication }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($product->information->side_effects)
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Side Effects</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            @foreach($product->information->side_effects as $effect)
                                <li>{{ $effect }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($product->information->dosage_information)
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Dosage Information</h4>
                        <p class="text-gray-600">{{ $product->information->dosage_information }}</p>
                    </div>
                @endif

                @if($product->information->contraindications)
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Contraindications</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            @foreach($product->information->contraindications as $contra)
                                <li>{{ $contra }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($product->information->drug_interactions)
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Drug Interactions</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            @foreach($product->information->drug_interactions as $interaction)
                                <li>{{ $interaction }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($product->information->storage_requirements)
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Storage Requirements</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                            @foreach($product->information->storage_requirements as $req)
                                <li>{{ $req }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-600 p-6 mb-8 rounded">
            <p class="text-yellow-800 font-semibold">⚠️ No stored pharmaceutical information available. <a href="{{ route('admin.ai.products.edit', $product->id) }}" class="underline">Add information</a></p>
        </div>
    @endif

    @if(!$medicalInfo && !$product->information)
        <div class="bg-red-50 border-l-4 border-red-600 p-6 mb-8 rounded">
            <p class="text-red-800 font-semibold">❌ Unable to fetch medical information from OpenAI. Please check your API key configuration.</p>
        </div>
    @endif
</div>
@endsection


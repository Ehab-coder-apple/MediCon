@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">💊 Prescription Details</h1>
            <p class="text-gray-600">Patient: {{ $check->patient_name ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('admin.ai.prescriptions.index') }}" class="px-6 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition">
            ← Back to Prescriptions
        </a>
    </div>

    <!-- Prescription Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-purple-600">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-gray-600 text-sm font-medium">Patient Name</p>
                <p class="text-2xl font-bold text-gray-900">{{ $check->patient_name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium">Prescription Date</p>
                <p class="text-2xl font-bold text-gray-900">{{ $check->prescription_date?->format('M d, Y') ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium">Checked At</p>
                <p class="text-2xl font-bold text-gray-900">{{ $check->checked_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Medications -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-4">
            <h3 class="text-xl font-bold">💊 Medications</h3>
        </div>
        <div class="space-y-4 p-6">
            @forelse($check->medications as $med)
                <div class="border rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ $med->medication_name }}</h4>
                            <p class="text-gray-600">Dosage: {{ $med->dosage ?? 'N/A' }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            @if($med->availability_status === 'in_stock')
                                bg-green-100 text-green-800
                            @elseif($med->availability_status === 'low_stock')
                                bg-yellow-100 text-yellow-800
                            @else
                                bg-red-100 text-red-800
                            @endif
                        ">
                            @if($med->availability_status === 'in_stock')
                                ✓ In Stock
                            @elseif($med->availability_status === 'low_stock')
                                ⚠️ Low Stock
                            @else
                                ✗ Out of Stock
                            @endif
                        </span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Prescribed Qty</p>
                            <p class="font-bold text-gray-900">{{ $med->quantity_prescribed }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Available Qty</p>
                            <p class="font-bold text-gray-900">{{ $med->available_quantity }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Product</p>
                            <p class="font-bold text-gray-900">{{ $med->product?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Code</p>
                            <p class="font-bold text-gray-900">{{ $med->product?->code ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Alternatives -->
                    @if($med->availability_status !== 'in_stock' && $med->alternatives->count() > 0)
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-sm font-semibold text-gray-900 mb-2">🔄 Alternative Products:</p>
                            <div class="space-y-2">
                                @foreach($med->alternatives as $alt)
                                    <div class="bg-blue-50 p-3 rounded text-sm">
                                        <p class="font-semibold text-gray-900">{{ $alt->product?->name }}</p>
                                        <p class="text-gray-600">Available: {{ $alt->available_quantity }} | Location: {{ $alt->shelf_location ?? 'N/A' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-center text-gray-600 py-8">No medications found</p>
            @endforelse
        </div>
    </div>
</div>
@endsection


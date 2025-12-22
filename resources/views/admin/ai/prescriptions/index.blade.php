@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">💊 Prescription Checking</h1>
            <p class="text-gray-600">Manage scanned prescriptions and medication availability</p>
        </div>
        <a href="{{ route('admin.ai.dashboard') }}" class="px-6 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-purple-600">
        <h3 class="text-lg font-bold text-gray-900 mb-4">🔍 Filter Prescriptions</h3>
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" name="search" placeholder="Search patient name..." class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
                <button type="submit" style="background-color: #9333ea; color: white;" class="px-8 py-2 font-semibold rounded-lg hover:shadow-lg transition inline-block">
                    🔍 Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Prescriptions Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold">Patient Name</th>
                    <th class="px-6 py-3 text-left font-semibold">Date</th>
                    <th class="px-6 py-3 text-left font-semibold">Medications</th>
                    <th class="px-6 py-3 text-left font-semibold">In Stock</th>
                    <th class="px-6 py-3 text-left font-semibold">Low Stock</th>
                    <th class="px-6 py-3 text-left font-semibold">Out of Stock</th>
                    <th class="px-6 py-3 text-left font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checks as $check)
                    @php
                        $inStock = $check->medications->where('availability_status', 'in_stock')->count();
                        $lowStock = $check->medications->where('availability_status', 'low_stock')->count();
                        $outOfStock = $check->medications->where('availability_status', 'out_of_stock')->count();
                    @endphp
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $check->patient_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $check->prescription_date?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $check->medications->count() }} medications</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">✓ {{ $inStock }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">⚠️ {{ $lowStock }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">✗ {{ $outOfStock }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.ai.prescriptions.show', $check->id) }}" class="text-purple-600 hover:text-purple-800 font-semibold">
                                View Details →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                            <p class="text-lg">📭 No prescriptions found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $checks->links() }}
    </div>
</div>
@endsection


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Statistics') }}
        </h2>
    </x-slot>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Attendance Statistics</h1>
            <p class="text-gray-600 mt-2">Attendance analytics and reporting</p>
        </div>
        <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            ← Back to List
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filters</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 w-full">
                    🔍 Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Records -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Records</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_records'] ?? 0 }}</p>
                </div>
                <div class="text-4xl text-blue-500">📊</div>
            </div>
        </div>

        <!-- Days Present -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Days Present</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['days_present'] ?? 0 }}</p>
                </div>
                <div class="text-4xl text-green-500">✓</div>
            </div>
        </div>

        <!-- Incomplete Days -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Incomplete Days</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['incomplete_days'] ?? 0 }}</p>
                </div>
                <div class="text-4xl text-yellow-500">⚠</div>
            </div>
        </div>

        <!-- Geofence Violations -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Geofence Violations</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['geofence_violations'] ?? 0 }}</p>
                </div>
                <div class="text-4xl text-red-500">✗</div>
            </div>
        </div>
    </div>

    <!-- Average Hours -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Average Hours Per Day</h2>
            <p class="text-4xl font-bold text-blue-600">
                {{ $stats['average_hours'] ?? 0 }}h
            </p>
            <p class="text-gray-600 text-sm mt-2">Based on completed check-ins and check-outs</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Attendance Rate</h2>
            @php
                $total = ($stats['days_present'] ?? 0) + ($stats['incomplete_days'] ?? 0);
                $rate = $total > 0 ? round((($stats['days_present'] ?? 0) / $total) * 100) : 0;
            @endphp
            <p class="text-4xl font-bold text-green-600">
                {{ $rate }}%
            </p>
            <p class="text-gray-600 text-sm mt-2">{{ $stats['days_present'] ?? 0 }} of {{ $total }} days</p>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Status Breakdown</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="border-l-4 border-yellow-500 pl-4">
                <p class="text-gray-600 text-sm">Pending</p>
                <p class="text-2xl font-bold">{{ $stats['status_pending'] ?? 0 }}</p>
            </div>
            <div class="border-l-4 border-blue-500 pl-4">
                <p class="text-gray-600 text-sm">Checked In</p>
                <p class="text-2xl font-bold">{{ $stats['status_checked_in'] ?? 0 }}</p>
            </div>
            <div class="border-l-4 border-green-500 pl-4">
                <p class="text-gray-600 text-sm">Checked Out</p>
                <p class="text-2xl font-bold">{{ $stats['status_checked_out'] ?? 0 }}</p>
            </div>
            <div class="border-l-4 border-red-500 pl-4">
                <p class="text-gray-600 text-sm">Incomplete</p>
                <p class="text-2xl font-bold">{{ $stats['status_incomplete'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Geofence Breakdown -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Geofence Compliance</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border-l-4 border-green-500 pl-4">
                <p class="text-gray-600 text-sm">Within Geofence</p>
                <p class="text-2xl font-bold">{{ $stats['geofence_within'] ?? 0 }}</p>
            </div>
            <div class="border-l-4 border-red-500 pl-4">
                <p class="text-gray-600 text-sm">Outside Geofence</p>
                <p class="text-2xl font-bold">{{ $stats['geofence_outside'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>
</x-app-layout>


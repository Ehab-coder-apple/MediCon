<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Details') }}
        </h2>
    </x-slot>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Attendance Details</h1>
            <p class="text-gray-600 mt-2">{{ $attendance->user?->name ?? 'N/A' }} - {{ $attendance->attendance_date?->format('M d, Y') ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            ← Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2">
            <!-- Employee Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Employee Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="text-lg font-semibold">{{ $attendance->user?->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="text-lg font-semibold">{{ $attendance->user?->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Branch</p>
                        <p class="text-lg font-semibold">{{ $attendance->branch?->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tenant</p>
                        <p class="text-lg font-semibold">{{ $attendance->tenant?->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Check-In Details -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Check-In Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Time</p>
                        <p class="text-lg font-semibold">{{ $attendance->check_in_time?->format('H:i:s') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Latitude</p>
                        <p class="text-lg font-semibold">{{ $attendance->check_in_latitude ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Longitude</p>
                        <p class="text-lg font-semibold">{{ $attendance->check_in_longitude ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Distance (meters)</p>
                        <p class="text-lg font-semibold">{{ $attendance->check_in_distance_meters ? number_format($attendance->check_in_distance_meters, 2) : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Within Geofence</p>
                        <p class="text-lg font-semibold">
                            @if($attendance->check_in_within_geofence)
                                <span class="text-green-600">✓ Yes</span>
                            @else
                                <span class="text-red-600">✗ No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Device Info</p>
                        <p class="text-lg font-semibold">{{ $attendance->check_in_device_info ?? '-' }}</p>
                    </div>
                    @if($attendance->check_in_notes)
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Notes</p>
                            <p class="text-lg font-semibold">{{ $attendance->check_in_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Check-Out Details -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Check-Out Details</h2>
                @if($attendance->check_out_time)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Time</p>
                            <p class="text-lg font-semibold">{{ $attendance->check_out_time?->format('H:i:s') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Latitude</p>
                            <p class="text-lg font-semibold">{{ $attendance->check_out_latitude ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Longitude</p>
                            <p class="text-lg font-semibold">{{ $attendance->check_out_longitude ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Distance (meters)</p>
                            <p class="text-lg font-semibold">{{ $attendance->check_out_distance_meters ? number_format($attendance->check_out_distance_meters, 2) : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Within Geofence</p>
                            <p class="text-lg font-semibold">
                                @if($attendance->check_out_within_geofence)
                                    <span class="text-green-600">✓ Yes</span>
                                @else
                                    <span class="text-red-600">✗ No</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Device Info</p>
                            <p class="text-lg font-semibold">{{ $attendance->check_out_device_info ?? '-' }}</p>
                        </div>
                        @if($attendance->check_out_notes)
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">Notes</p>
                                <p class="text-lg font-semibold">{{ $attendance->check_out_notes }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500">No check-out recorded yet.</p>
                @endif
            </div>

            <!-- Break Details -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Break Information</h2>
                @if($attendance->break_start_time || $attendance->total_break_count > 0)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Total Breaks</p>
                            <p class="text-lg font-semibold">{{ $attendance->total_break_count ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Break Duration</p>
                            <p class="text-lg font-semibold">
                                @if($attendance->break_duration_minutes)
                                    {{ floor($attendance->break_duration_minutes / 60) }}h {{ $attendance->break_duration_minutes % 60 }}m
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @if($attendance->break_start_time)
                            <div>
                                <p class="text-sm text-gray-600">Last Break Start</p>
                                <p class="text-lg font-semibold">{{ $attendance->break_start_time->format('H:i:s') }}</p>
                            </div>
                        @endif
                        @if($attendance->break_end_time)
                            <div>
                                <p class="text-sm text-gray-600">Last Break End</p>
                                <p class="text-lg font-semibold">{{ $attendance->break_end_time->format('H:i:s') }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500">No breaks recorded.</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Summary Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Summary</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Date</p>
                        <p class="text-lg font-semibold">{{ $attendance->attendance_date?->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'checked_in' => 'bg-blue-100 text-blue-800',
                                'checked_out' => 'bg-green-100 text-green-800',
                                'incomplete' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <p class="text-lg font-semibold">
                            <span class="px-3 py-1 {{ $statusColors[$attendance->status] ?? 'bg-gray-100 text-gray-800' }} rounded-full text-sm">
                                {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Hours Worked</p>
                        <p class="text-lg font-semibold">
                            @if($attendance->total_minutes_worked)
                                {{ floor($attendance->total_minutes_worked / 60) }}h {{ $attendance->total_minutes_worked % 60 }}m
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Breaks</p>
                        <p class="text-lg font-semibold">
                            {{ $attendance->total_break_count ?? 0 }}
                            @if($attendance->break_duration_minutes)
                                <span class="text-sm text-gray-500">({{ $attendance->break_duration_minutes }}m)</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Geofence Compliance</p>
                        <p class="text-lg font-semibold">
                            @if($attendance->check_in_within_geofence && $attendance->check_out_within_geofence)
                                <span class="text-green-600">✓ Compliant</span>
                            @elseif(!$attendance->check_in_within_geofence || !$attendance->check_out_within_geofence)
                                <span class="text-red-600">✗ Violation</span>
                            @else
                                <span class="text-gray-600">-</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Timestamps</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Created</p>
                        <p class="font-semibold">{{ $attendance->created_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Updated</p>
                        <p class="font-semibold">{{ $attendance->updated_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>


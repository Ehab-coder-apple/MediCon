<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Management') }}
        </h2>
    </x-slot>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Attendance Management</h1>
            <p class="text-gray-600 mt-2">Track employee attendance with GPS verification</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.attendance.statistics') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                📊 Statistics
            </a>
            <a href="{{ route('admin.attendance.export') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                📥 Export CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filters</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Employee Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Employees</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Branch Filter -->
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

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Incomplete</option>
                </select>
            </div>

            <!-- Geofence Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Geofence</label>
                <select name="geofence" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="within" {{ request('geofence') == 'within' ? 'selected' : '' }}>Within Geofence</option>
                    <option value="outside" {{ request('geofence') == 'outside' ? 'selected' : '' }}>Outside Geofence</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2 items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 w-full">
                    🔍 Filter
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 w-full text-center">
                    ↺ Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Employee</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Branch</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Check-In</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Check-Out</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Duration</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Breaks</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Geofence</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $attendance->user?->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $attendance->branch?->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $attendance->attendance_date?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $attendance->check_in_time?->format('H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $attendance->check_out_time?->format('H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @php
                                // Calculate duration if not already stored
                                $duration = $attendance->total_minutes_worked;
                                if (!$duration && $attendance->check_in_time && $attendance->check_out_time) {
                                    $duration = $attendance->check_in_time->diffInMinutes($attendance->check_out_time);
                                }
                            @endphp
                            @if($duration)
                                <span class="font-semibold text-blue-600">{{ floor($duration / 60) }}h {{ $duration % 60 }}m</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($attendance->break_duration_minutes)
                                <div class="flex items-center gap-1">
                                    <span class="text-orange-600 font-semibold">{{ floor($attendance->break_duration_minutes / 60) }}h {{ $attendance->break_duration_minutes % 60 }}m</span>
                                    @if($attendance->total_break_count)
                                        <span class="text-xs text-gray-500">({{ $attendance->total_break_count }})</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($attendance->check_in_within_geofence && $attendance->check_out_within_geofence)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">✓ Within</span>
                            @elseif(!$attendance->check_in_within_geofence || !$attendance->check_out_within_geofence)
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">✗ Outside</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'checked_in' => 'bg-blue-100 text-blue-800',
                                    'checked_out' => 'bg-green-100 text-green-800',
                                    'incomplete' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 {{ $statusColors[$attendance->status] ?? 'bg-gray-100 text-gray-800' }} rounded-full text-xs font-semibold">
                                {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.attendance.show', $attendance) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                            No attendance records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($attendances->hasPages())
        <div class="mt-6">
            {{ $attendances->links() }}
        </div>
    @endif
</div>
</x-app-layout>


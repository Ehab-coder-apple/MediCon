<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('HQ HR Dashboard') }}
            </h2>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- 1. Top stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Total Employees</p>
                <p class="mt-2 text-2xl font-semibold text-slate-800">{{ $totalEmployees }}</p>
                <p class="mt-1 text-xs text-slate-400"><i class="fa-solid fa-users mr-1"></i>Active across all branches</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Checked In Now</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ $checkedInNow }}</p>
                <p class="mt-1 text-xs text-slate-400"><i class="fa-solid fa-clock mr-1"></i>Currently on shift today</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Pending Leave Requests</p>
                <p class="mt-2 text-2xl font-semibold {{ $pendingLeaves > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ $pendingLeaves }}</p>
                <p class="mt-1 text-xs text-slate-400"><i class="fa-solid fa-calendar-days mr-1"></i>Awaiting your approval</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Active Allocations</p>
                <p class="mt-2 text-2xl font-semibold text-slate-800">{{ $activeAllocations }}</p>
                <p class="mt-1 text-xs text-slate-400"><i class="fa-solid fa-people-arrows mr-1"></i>Temporary assignments today</p>
            </div>
        </div>

        {{-- 6. Quick actions --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4 flex flex-wrap gap-3">
            <a href="{{ route('hq-hr.allocations') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors">
                <i class="fa-solid fa-people-arrows mr-2"></i>New Temporary Allocation
            </a>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium transition-colors">
                <i class="fa-solid fa-user-plus mr-2"></i>Add Employee
            </a>
            <a href="{{ route('admin.leaves.index') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition-colors">
                <i class="fa-solid fa-calendar-check mr-2"></i>Review Leave Requests
            </a>
            <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium transition-colors">
                <i class="fa-solid fa-clock mr-2"></i>View Attendance
            </a>
            <a href="{{ route('admin.activity-logs.index') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium transition-colors">
                <i class="fa-solid fa-clipboard-list mr-2"></i>View Activity Logs
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 3. Pending leave requests panel --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700">Pending Leave Requests</h3>
                    <a href="{{ route('admin.leaves.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View all</a>
                </div>
                <div class="p-2">
                    @if ($pendingLeaveRequests->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach ($pendingLeaveRequests as $leave)
                                <div class="flex items-center justify-between px-3 py-2.5">
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ $leave->user->name ?? '—' }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ $leave->leaveType->name ?? 'Leave' }} ·
                                            {{ $leave->start_date->format('M j') }} - {{ $leave->end_date->format('M j, Y') }}
                                            ({{ $leave->number_of_days }} {{ $leave->number_of_days == 1 ? 'day' : 'days' }})
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('admin.leaves.approve', $leave) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}" onsubmit="return confirm('Reject this leave request?');">
                                            @csrf
                                            <input type="hidden" name="approval_notes" value="Rejected from HR dashboard">
                                            <button type="submit" class="px-2.5 py-1 rounded text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-28 text-slate-400">
                            <i class="fa-solid fa-calendar-check text-2xl mb-2"></i>
                            <p class="text-sm">No pending leave requests</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Today's attendance & geofence snapshot --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700">Today's Attendance &amp; Geofence</h3>
                    <a href="{{ route('admin.attendance.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View all</a>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-2">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>Geofence Violations Today ({{ $geofenceViolationsToday->count() }})
                        </p>
                        @if ($geofenceViolationsToday->isNotEmpty())
                            <div class="divide-y divide-slate-100">
                                @foreach ($geofenceViolationsToday->take(5) as $attendance)
                                    <div class="flex items-center justify-between py-1.5 text-sm">
                                        <span class="text-slate-700">{{ $attendance->user->name ?? '—' }}</span>
                                        <span class="text-slate-400">{{ $attendance->branch->name ?? '—' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-slate-400">No geofence violations today</p>
                        @endif
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                            <i class="fa-solid fa-user-clock mr-1"></i>Not Checked In Yet ({{ $notCheckedInToday->count() }})
                        </p>
                        @if ($notCheckedInToday->isNotEmpty())
                            <div class="divide-y divide-slate-100 max-h-40 overflow-y-auto">
                                @foreach ($notCheckedInToday->take(10) as $employee)
                                    <div class="flex items-center justify-between py-1.5 text-sm">
                                        <span class="text-slate-700">{{ $employee->name }}</span>
                                        <span class="text-slate-400">{{ $employee->branch->name ?? '—' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-slate-400">Everyone has checked in today</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 4. Branch staffing overview --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700">Branch Staffing Overview</h3>
                </div>
                <div class="p-2">
                    @if ($branchStaffing->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                        <th class="px-3 py-2 font-medium">Branch</th>
                                        <th class="px-3 py-2 font-medium text-right">Headcount</th>
                                        <th class="px-3 py-2 font-medium text-right">Outgoing</th>
                                        <th class="px-3 py-2 font-medium text-right">Incoming</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($branchStaffing as $row)
                                        <tr>
                                            <td class="px-3 py-2.5 text-slate-800 font-medium">{{ $row['branch']->name }}</td>
                                            <td class="px-3 py-2.5 text-right text-slate-700">{{ $row['headcount'] }}</td>
                                            <td class="px-3 py-2.5 text-right {{ $row['outgoing'] > 0 ? 'text-amber-600 font-medium' : 'text-slate-400' }}">{{ $row['outgoing'] }}</td>
                                            <td class="px-3 py-2.5 text-right {{ $row['incoming'] > 0 ? 'text-emerald-600 font-medium' : 'text-slate-400' }}">{{ $row['incoming'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-28 text-slate-400">
                            <i class="fa-solid fa-store text-2xl mb-2"></i>
                            <p class="text-sm">No active branches found</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 5. Recent HR-filtered activity feed --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700">Recent HR Activity</h3>
                    <a href="{{ route('admin.activity-logs.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View all</a>
                </div>
                <div class="p-2">
                    @if ($recentHrActivity->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach ($recentHrActivity as $log)
                                <div class="px-3 py-2.5">
                                    <p class="text-sm text-slate-700">{{ $log->description }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $log->user->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-28 text-slate-400">
                            <i class="fa-solid fa-clipboard-list text-2xl mb-2"></i>
                            <p class="text-sm">No recent HR activity</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

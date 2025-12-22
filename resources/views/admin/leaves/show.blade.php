<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Request Details') }}
        </h2>
    </x-slot>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Back Button -->
        <a href="{{ route('admin.leaves.index') }}" class="text-blue-600 hover:text-blue-900 mb-6 inline-block">
            ← Back to Leave Requests
        </a>

        <!-- Leave Details Card -->
        <div class="bg-white rounded-lg shadow p-8 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $leave->user->name }}</h1>
                    <p class="text-gray-600 mt-2">{{ $leave->leaveType->name }}</p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    {{ $leave->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $leave->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $leave->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $leave->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                ">
                    {{ ucfirst($leave->status) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $leave->start_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $leave->end_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Number of Days</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $leave->number_of_days }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Leave Type</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $leave->leaveType->name }}</p>
                </div>
            </div>

            @if($leave->is_half_day)
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-900">
                        <strong>Half Day:</strong> {{ ucfirst($leave->half_day_type) }}
                    </p>
                </div>
            @endif

            @if($leave->reason)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">Reason</label>
                    <p class="mt-2 text-gray-900">{{ $leave->reason }}</p>
                </div>
            @endif

            @if($leave->approvedBy)
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Approval Details</h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Approved By</label>
                            <p class="mt-1 text-gray-900">{{ $leave->approvedBy->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Approved At</label>
                            <p class="mt-1 text-gray-900">{{ $leave->approved_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @if($leave->approval_notes)
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <p class="mt-2 text-gray-900">{{ $leave->approval_notes }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        @if($leave->status === 'pending')
            <div class="grid grid-cols-2 gap-4">
                <!-- Approve Form -->
                <form method="POST" action="{{ route('admin.leaves.approve', $leave) }}" class="bg-green-50 rounded-lg shadow p-6">
                    @csrf
                    <h3 class="text-lg font-semibold text-green-900 mb-4">Approve Leave</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                        <textarea name="approval_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        ✓ Approve
                    </button>
                </form>

                <!-- Reject Form -->
                <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}" class="bg-red-50 rounded-lg shadow p-6">
                    @csrf
                    <h3 class="text-lg font-semibold text-red-900 mb-4">Reject Leave</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Reason (Required)</label>
                        <textarea name="approval_notes" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        ✗ Reject
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
</x-app-layout>


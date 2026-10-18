<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Salary History') }} — {{ $user->name }}
            </h2>
            <a href="{{ route('hq-hr.payroll.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                <i class="fa-solid fa-arrow-left mr-1"></i>Back to Contracts
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Base Pay Change History</h3>
                <span class="text-xs text-slate-400">{{ $increases->count() }} record(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-2 font-medium">Effective Date</th>
                            <th class="px-5 py-2 font-medium">Before</th>
                            <th class="px-5 py-2 font-medium">After</th>
                            <th class="px-5 py-2 font-medium">Change</th>
                            <th class="px-5 py-2 font-medium">Authorized By</th>
                            <th class="px-5 py-2 font-medium">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($increases as $increase)
                            <tr>
                                <td class="px-5 py-2.5 text-slate-700">{{ $increase->effective_date->format('M j, Y') }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ number_format($increase->base_salary_before, 2) }} {{ $increase->currency }}</td>
                                <td class="px-5 py-2.5 text-slate-900 font-medium">{{ number_format($increase->base_salary_after, 2) }} {{ $increase->currency }}</td>
                                <td class="px-5 py-2.5">
                                    @php($change = $increase->change_amount)
                                    <span class="{{ $change >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-medium">
                                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $increase->authorizer->name ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $increase->reason ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No salary change history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

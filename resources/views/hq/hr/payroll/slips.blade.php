<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Payslips') }}
            </h2>
            <a href="{{ route('hq-hr.payroll.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                <i class="fa-solid fa-file-contract mr-1"></i>Manage Contracts
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        @if (session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="p-4 bg-amber-100 border border-amber-400 text-amber-700 rounded-lg">{{ session('warning') }}</div>
        @endif

        {{-- Period selector + Generate All --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
            <div class="flex flex-wrap items-end gap-4">
                <form method="GET" action="{{ route('hq-hr.payroll.slips') }}" class="flex items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Month</label>
                        <select name="month" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == $month)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Year</label>
                        <select name="year" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach (range(now()->year + 1, now()->year - 3) as $y)
                                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="h-10 px-4 rounded-md bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium transition-colors">
                        <i class="fa-solid fa-filter mr-1"></i>View Period
                    </button>
                </form>

                <form method="POST" action="{{ route('hq-hr.payroll.slips.generate-all') }}" onsubmit="return confirm('Generate/recalculate payslips for every employee with an active contract this period?');">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="h-10 px-4 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors">
                        <i class="fa-solid fa-rotate mr-1"></i>Generate All ({{ $employeesWithContracts }} employees)
                    </button>
                </form>
            </div>
        </div>

        {{-- Payslips table --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">
                    {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }} Payslips
                </h3>
                <span class="text-xs text-slate-400">{{ $slips->count() }} generated</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-2 font-medium">Employee</th>
                            <th class="px-5 py-2 font-medium">Pay Type</th>
                            <th class="px-5 py-2 font-medium">Gross Pay</th>
                            <th class="px-5 py-2 font-medium">Deductions</th>
                            <th class="px-5 py-2 font-medium">Net Pay</th>
                            <th class="px-5 py-2 font-medium">Status</th>
                            <th class="px-5 py-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($slips as $slip)
                            @php
                                $statusClasses = [
                                    'draft' => 'bg-slate-100 text-slate-500',
                                    'pending_approval' => 'bg-amber-50 text-amber-700',
                                    'approved' => 'bg-emerald-50 text-emerald-700',
                                    'rejected' => 'bg-red-50 text-red-700',
                                    'paid' => 'bg-blue-50 text-blue-700',
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-2.5 font-medium text-slate-900">{{ $slip->user->name ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ ucfirst($slip->pay_type) }}</td>
                                <td class="px-5 py-2.5 text-slate-700">{{ number_format($slip->gross_pay, 2) }} {{ $slip->currency }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ number_format($slip->deductions_total + $slip->absence_deduction, 2) }}</td>
                                <td class="px-5 py-2.5 text-slate-900 font-semibold">{{ number_format($slip->net_pay, 2) }} {{ $slip->currency }}</td>
                                <td class="px-5 py-2.5">
                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$slip->status] ?? '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $slip->status)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-right">
                                    <a href="{{ route('hq-hr.payroll.slips.show', $slip) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">
                                        View / Approve
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">No payslips generated for this period yet. Click "Generate All" above.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

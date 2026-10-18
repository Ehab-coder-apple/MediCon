<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Payslip') }} — {{ $slip->user->name ?? '—' }}
                <span class="text-sm font-normal text-slate-400">({{ \Carbon\Carbon::create()->month($slip->payroll_month)->format('F') }} {{ $slip->payroll_year }})</span>
            </h2>
            <a href="{{ route('hq-hr.payroll.slips', ['month' => $slip->payroll_month, 'year' => $slip->payroll_year]) }}" class="text-sm text-slate-500 hover:text-slate-700">
                <i class="fa-solid fa-arrow-left mr-1"></i>Back to Payslips
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        @if (session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="p-4 bg-amber-100 border border-amber-400 text-amber-700 rounded-lg">{{ session('warning') }}</div>
        @endif

        {{-- Summary card --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Pay Type</p>
                        <p class="text-lg font-semibold text-slate-800">{{ ucfirst($slip->pay_type) }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Base Pay</p>
                        <p class="text-lg font-semibold text-slate-800">{{ number_format($slip->base_pay, 2) }} {{ $slip->currency }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Overtime</p>
                        <p class="text-lg font-semibold text-slate-800">{{ number_format($slip->overtime_pay, 2) }}
                            <span class="text-xs font-normal text-slate-400">({{ number_format($slip->overtime_hours, 1) }}h)</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Awards</p>
                        <p class="text-lg font-semibold text-emerald-600">+{{ number_format($slip->awards_total, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Deductions</p>
                        <p class="text-lg font-semibold text-red-600">-{{ number_format($slip->deductions_total, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Absence Deduction</p>
                        <p class="text-lg font-semibold text-red-600">-{{ number_format($slip->absence_deduction, 2) }}
                            <span class="text-xs font-normal text-slate-400">({{ $slip->unexcused_absence_days }} day(s))</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Gross Pay</p>
                        <p class="text-lg font-semibold text-slate-800">{{ number_format($slip->gross_pay, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Net Pay</p>
                        <p class="text-2xl font-bold text-slate-900">{{ number_format($slip->net_pay, 2) }} {{ $slip->currency }}</p>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-2">
                    @php
                        $statusClasses = [
                            'draft' => 'bg-slate-100 text-slate-500',
                            'pending_approval' => 'bg-amber-50 text-amber-700',
                            'approved' => 'bg-emerald-50 text-emerald-700',
                            'rejected' => 'bg-red-50 text-red-700',
                            'paid' => 'bg-blue-50 text-blue-700',
                        ];
                    @endphp
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$slip->status] ?? '' }}">
                        {{ ucfirst(str_replace('_', ' ', $slip->status)) }}
                    </span>

                    @if (in_array($slip->status, ['draft', 'pending_approval', 'rejected']))
                        <form method="POST" action="{{ route('hq-hr.payroll.slips.approve', $slip) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-md transition-colors">
                                <i class="fa-solid fa-check mr-1"></i>Approve
                            </button>
                        </form>
                        <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium rounded-md transition-colors">
                            <i class="fa-solid fa-xmark mr-1"></i>Reject
                        </button>
                    @endif

                    <form method="POST" action="{{ route('hq-hr.payroll.slips.generate-one') }}" onsubmit="return confirm('Recalculate this payslip from current attendance/adjustments? Any status will reset to pending approval.');">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $slip->user_id }}">
                        <input type="hidden" name="month" value="{{ $slip->payroll_month }}">
                        <input type="hidden" name="year" value="{{ $slip->payroll_year }}">
                        <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-md transition-colors">
                            <i class="fa-solid fa-rotate mr-1"></i>Recalculate
                        </button>
                    </form>
                </div>
            </div>

            @if ($slip->status === 'rejected' && $slip->notes)
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                    <strong>Rejection reason:</strong> {{ $slip->notes }}
                </div>
            @endif
        </div>

        {{-- Reject modal --}}
        <div id="reject-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-5 w-full max-w-md">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Reject Payslip</h3>
                <form method="POST" action="{{ route('hq-hr.payroll.slips.reject', $slip) }}">
                    @csrf
                    <textarea name="notes" required rows="3" placeholder="Reason for rejection..." class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <div class="flex justify-end gap-2 mt-3">
                        <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">Reject</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Branch attribution --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Branch Attribution</h3>
                <p class="text-xs text-slate-400 mt-0.5">Pay earned while temporarily allocated is attributed to the target branch, not the home branch.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-2 font-medium">Branch</th>
                            <th class="px-5 py-2 font-medium">Regular Hours</th>
                            <th class="px-5 py-2 font-medium">Overtime Hours</th>
                            <th class="px-5 py-2 font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($slip->branchAttributions as $attribution)
                            <tr>
                                <td class="px-5 py-2.5 font-medium text-slate-900">{{ $attribution->branch->name ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ number_format($attribution->regular_hours, 2) }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ number_format($attribution->overtime_hours, 2) }}</td>
                                <td class="px-5 py-2.5 text-slate-700">{{ number_format($attribution->amount, 2) }} {{ $attribution->currency }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-6 text-center text-slate-400">No branch attribution recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Adjustments ledger --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Adjustments Ledger</h3>
                <p class="text-xs text-slate-400 mt-0.5">Awards, overtime, and deductions for this period. System-calculated overtime is regenerated on recalculation.</p>
            </div>

            <form method="POST" action="{{ route('hq-hr.payroll.adjustments.store') }}" class="p-5 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 border-b border-slate-100">
                @csrf
                <input type="hidden" name="user_id" value="{{ $slip->user_id }}">
                <input type="hidden" name="payroll_month" value="{{ $slip->payroll_month }}">
                <input type="hidden" name="payroll_year" value="{{ $slip->payroll_year }}">
                <input type="hidden" name="redirect_slip" value="{{ $slip->id }}">

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                    <select name="type" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="award">Award / Bonus</option>
                        <option value="overtime">Overtime (manual)</option>
                        <option value="deduction">Deduction</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Category</label>
                    <input type="text" name="category" placeholder="e.g. uniform, bonus" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Branch</label>
                    <select name="branch_id" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Employee's home branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Amount</label>
                    <input type="number" step="0.01" min="0" name="amount" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Hours <span class="text-slate-400">(overtime only)</span></label>
                    <input type="number" step="0.01" min="0" name="hours" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full h-9 px-3 rounded-md bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i>Add
                    </button>
                </div>
                <div class="md:col-span-3 lg:col-span-6">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notes <span class="text-slate-400">(optional)</span></label>
                    <input type="text" name="notes" maxlength="1000" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-2 font-medium">Type</th>
                            <th class="px-5 py-2 font-medium">Category</th>
                            <th class="px-5 py-2 font-medium">Branch</th>
                            <th class="px-5 py-2 font-medium">Amount</th>
                            <th class="px-5 py-2 font-medium">Hours</th>
                            <th class="px-5 py-2 font-medium">Source</th>
                            <th class="px-5 py-2 font-medium">Notes</th>
                            <th class="px-5 py-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($adjustments as $adjustment)
                            @php
                                $typeClasses = [
                                    'award' => 'bg-emerald-50 text-emerald-700',
                                    'overtime' => 'bg-blue-50 text-blue-700',
                                    'deduction' => 'bg-red-50 text-red-700',
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-2.5">
                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $typeClasses[$adjustment->type] ?? '' }}">
                                        {{ ucfirst($adjustment->type) }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $adjustment->category ?: '—' }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $adjustment->branch->name ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-slate-700">{{ number_format($adjustment->amount, 2) }} {{ $adjustment->currency }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $adjustment->hours ? number_format($adjustment->hours, 2) : '—' }}</td>
                                <td class="px-5 py-2.5 text-slate-400 text-xs">{{ $adjustment->source === 'system_calculated' ? 'System' : 'Manual' }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $adjustment->notes ?: '—' }}</td>
                                <td class="px-5 py-2.5 text-right">
                                    @if ($adjustment->source === 'manual')
                                        <form method="POST" action="{{ route('hq-hr.payroll.adjustments.destroy', $adjustment) }}" onsubmit="return confirm('Remove this adjustment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs">Remove</button>
                                        </form>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-8 text-center text-slate-400">No adjustments for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<x-report-layout
    title="Shift Reconciliation Ledger"
    :back="route('admin.reports.index')"
>
    {{-- Filter ribbon --}}
    <x-slot name="filters">
        <form method="GET" action="{{ route('admin.reports.shift-reconciliation') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="start_date" class="block text-sm font-medium text-slate-700">From (Shift Start)</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-slate-700">To (Shift Start)</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-900">Apply Filters</button>
            </div>
        </form>
    </x-slot>

    {{-- KPI summary ribbon --}}
    <x-slot name="kpis">
        <x-report.kpi label="Total Shifts" value="{{ number_format($summary['total_shifts']) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></x-slot>
        </x-report.kpi>
        <x-report.kpi label="Open Shifts" value="{{ number_format($summary['open_shifts']) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></x-slot>
        </x-report.kpi>
    </x-slot>

    @if($shifts->count() > 0)
        <x-report.table title="Shift Ledger">
            <x-slot name="head">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Shift ID</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Pharmacist</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Shift Duration</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Expected System Sales</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Cash Entered</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Discrepancy / Variance</th>
                @if($canEdit)
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Action</th>
                @endif
            </x-slot>
            @foreach($shifts as $shift)
                @php
                    $expected = (float) ($expectedByShift[$shift->id] ?? 0);
                    $cashCounted = $shift->ending_cash !== null ? ((float) $shift->ending_cash - (float) $shift->starting_cash) : null;
                    $variance = $cashCounted !== null ? $cashCounted - $expected : null;
                    $isOpen = $shift->status === \App\Models\Shift::STATUS_OPEN;
                @endphp
                <tr>
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-900">#{{ $shift->id }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ $shift->user->name ?? 'Unknown' }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                        <div>{{ $shift->start_time->format('M d, Y H:i') }}</div>
                        <div class="text-xs text-slate-500">
                            @if($shift->end_time)
                                to {{ $shift->end_time->format('M d, Y H:i') }}
                            @else
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">Open</span>
                            @endif
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">${{ number_format($expected, 2) }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                        @if($cashCounted === null)
                            <span class="text-slate-400">&mdash;</span>
                        @else
                            ${{ number_format($cashCounted, 2) }}
                            <div class="text-xs text-slate-500">{{ '$' . number_format($shift->starting_cash, 2) }} &rarr; {{ '$' . number_format($shift->ending_cash, 2) }}</div>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm">
                        @if($variance === null)
                            <span class="text-slate-400">In progress</span>
                        @elseif(abs($variance) < 0.01)
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Balanced</span>
                        @elseif($variance < 0)
                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">Short ${{ number_format(abs($variance), 2) }}</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Over ${{ number_format($variance, 2) }}</span>
                        @endif
                    </td>
                    @if($canEdit)
                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                            <a href="{{ route('admin.shifts.edit', $shift) }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                Edit
                            </a>
                        </td>
                    @endif
                </tr>
            @endforeach
            <x-slot name="foot">
                {{ $shifts->links() }}
            </x-slot>
        </x-report.table>
    @else
        <x-report.empty message="No shifts found for the selected period." />
    @endif
</x-report-layout>

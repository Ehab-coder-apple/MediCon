<x-report-layout
    title="Staff Sales Performance"
    :back="route('admin.reports.index')"
>
    {{-- Filter ribbon --}}
    <x-slot name="filters">
        <form method="GET" action="{{ route('admin.reports.staff-performance') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="start_date" class="block text-sm font-medium text-slate-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-slate-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-900">Apply Filters</button>
            </div>
        </form>
    </x-slot>

    {{-- KPI summary ribbon --}}
    <x-slot name="kpis">
        <x-report.kpi label="Staff Members" value="{{ number_format($summary['staff_count']) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg></x-slot>
        </x-report.kpi>
        <x-report.kpi label="Total Transactions" value="{{ number_format($summary['total_transactions']) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg></x-slot>
        </x-report.kpi>
        <x-report.kpi label="Gross Sales" value="${{ number_format($summary['gross_sales'], 2) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></x-slot>
        </x-report.kpi>
        <x-report.kpi label="Average Order" value="${{ number_format($summary['average_order_value'], 2) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008ZM6.75 4.5h10.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75Z"/></svg></x-slot>
        </x-report.kpi>
    </x-slot>

    @if($rows->count() > 0)
        <x-report.table title="Sales by Staff Member">
            <x-slot name="head">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Staff Name</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Total Transactions</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Gross Sales Amount</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Average Order Value</th>
            </x-slot>
            @foreach($rows as $row)
                <tr>
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-900">{{ $row->staff_name }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ number_format($row->total_transactions) }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">${{ number_format($row->gross_sales, 2) }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">${{ number_format($row->average_order_value, 2) }}</td>
                </tr>
            @endforeach
        </x-report.table>
    @else
        <x-report.empty message="No staff sales found for the selected period." />
    @endif
</x-report-layout>

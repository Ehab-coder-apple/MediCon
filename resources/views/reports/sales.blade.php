@php
    // Backend-free Analytics View toggle: reveals a deeper metrics panel built
    // from the $summary data the controller already provides. No query logic changes.
    $analyticsOn = request()->boolean('analytics');
    $analyticsHref = $analyticsOn
        ? route('admin.reports.sales', request()->except('analytics'))
        : route('admin.reports.sales', array_merge(request()->query(), ['analytics' => 1]));
@endphp

<x-report-layout
    title="Sales Report"
    :back="route('admin.reports.index')"
    :analyticsHref="$analyticsHref"
    :analyticsLabel="$analyticsOn ? 'Hide Analytics' : 'Analytics View'"
    :analyticsActive="$analyticsOn"
>
    {{-- Export Options dropdown menu --}}
    <x-slot name="export">
        <x-dropdown-link href="{{ route('admin.reports.export.sales', request()->query()) }}">Export to CSV</x-dropdown-link>
        <x-dropdown-link href="{{ route('admin.reports.export.sales.excel', request()->query()) }}">Export Standard Excel</x-dropdown-link>
        <x-dropdown-link href="{{ route('admin.reports.export.sales.detailed.excel', request()->query()) }}">Export Detailed Excel</x-dropdown-link>
        <div class="my-1 border-t border-slate-100"></div>
        <span class="flex cursor-not-allowed items-center justify-between px-4 py-2 text-sm text-slate-400">
            Export PDF
            <span class="ml-2 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Soon</span>
        </span>
    </x-slot>

    {{-- Filter ribbon --}}
    <x-slot name="filters">
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-slate-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-slate-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label for="period" class="block text-sm font-medium text-slate-700">Period</label>
                <select name="period" id="period" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-900">
                    Apply Filters
                </button>
            </div>
        </form>
    </x-slot>

    {{-- KPI summary ribbon --}}
    <x-slot name="kpis">
        <x-report.kpi label="Total Sales" value="{{ number_format($summary['total_sales']) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg></x-slot>
        </x-report.kpi>
        <x-report.kpi label="Total Revenue" value="${{ number_format($summary['total_revenue'], 2) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></x-slot>
        </x-report.kpi>
        <x-report.kpi label="Average Order" value="${{ number_format($summary['average_order_value'], 2) }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008ZM6.75 4.5h10.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75Z"/></svg></x-slot>
        </x-report.kpi>
        <x-report.kpi label="Top Products" value="{{ $summary['top_products']->count() }}">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg></x-slot>
        </x-report.kpi>
    </x-slot>

    {{-- Analytics View: deep-dive panel (toggled via the Analytics View button) --}}
    @if($analyticsOn)
        <div class="mb-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-sm font-semibold text-slate-800">Analytics &mdash; Top Products by Revenue</h3>
            </div>
            <div class="p-5">
                @php $maxRev = $summary['top_products']->max('total_revenue') ?: 1; @endphp
                @forelse($summary['top_products'] as $product)
                    <div class="mb-4 last:mb-0">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $product->name }}</span>
                            <span class="text-slate-500">${{ number_format($product->total_revenue, 2) }}</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-slate-700" style="width: {{ max(2, round(($product->total_revenue / $maxRev) * 100)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No product revenue to analyze for this period.</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Top Selling Products --}}
    @if($summary['top_products']->count() > 0)
        <x-report.table title="Top Selling Products">
            <x-slot name="head">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Product</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Code</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Quantity Sold</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Revenue</th>
            </x-slot>
            @foreach($summary['top_products'] as $product)
                <tr>
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-900">{{ $product->name }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ $product->code }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ number_format($product->total_sold) }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">${{ number_format($product->total_revenue, 2) }}</td>
                </tr>
            @endforeach
        </x-report.table>
    @endif

    {{-- Sales Transactions --}}
    @if($sales->count() > 0)
        <x-report.table title="Sales Transactions">
            <x-slot name="head">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Invoice</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Date</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Customer</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Staff</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Items</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Total</th>
            </x-slot>
            @foreach($sales as $sale)
                <tr>
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-900">{{ $sale->invoice_number }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ $sale->sale_date->format('M d, Y') }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ $sale->user->name }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ $sale->saleItems->count() }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-900">${{ number_format($sale->total_price, 2) }}</td>
                </tr>
            @endforeach
            <x-slot name="foot">
                {{ $sales->appends(request()->query())->links() }}
            </x-slot>
        </x-report.table>
    @else
        <x-report.empty message="No sales found for the selected period." />
    @endif
</x-report-layout>

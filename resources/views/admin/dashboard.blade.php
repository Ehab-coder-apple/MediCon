<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Primary business KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Today's Sales --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-600">Today's Sales</p>
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-emerald-50 text-emerald-600">
                        <i class="fa-solid fa-cash-register"></i>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($todaysSales, 2) }}</p>
            </div>

            {{-- Today's Profit --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-600">Today's Profit</p>
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-emerald-50 text-emerald-600">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-bold {{ $todaysProfit < 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $currencySymbol }}{{ number_format($todaysProfit, 2) }}</p>
            </div>

            {{-- Monthly Sales --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-600">Monthly Sales</p>
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 text-slate-700">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($monthlySales, 2) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ now()->format('F Y') }}</p>
            </div>

            {{-- Inventory Value --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-600">Inventory Value</p>
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 text-slate-700">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($inventoryValue, 2) }}</p>
                <p class="mt-1 text-xs text-slate-400">At cost</p>
            </div>
        </div>

        {{-- Inventory & Finance Alerts --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Inventory &amp; Finance Alerts</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
                {{-- Low Stock --}}
                <div class="flex items-center gap-3 p-4">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-amber-50 text-amber-600 shrink-0">
                        <i class="fa-solid fa-arrow-down-short-wide"></i>
                    </span>
                    <div>
                        <p class="text-lg font-bold text-slate-900 leading-none">{{ number_format($lowStockProducts) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Low Stock</p>
                    </div>
                </div>
                {{-- Out of Stock --}}
                <div class="flex items-center gap-3 p-4">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-red-50 text-red-600 shrink-0">
                        <i class="fa-solid fa-ban"></i>
                    </span>
                    <div>
                        <p class="text-lg font-bold text-slate-900 leading-none">{{ number_format($outOfStockProducts) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Out of Stock</p>
                    </div>
                </div>
                {{-- Expiring Soon --}}
                <div class="flex items-center gap-3 p-4">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-amber-50 text-amber-600 shrink-0">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </span>
                    <div>
                        <p class="text-lg font-bold text-slate-900 leading-none">{{ number_format($expiringSoonProducts) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Expiring Soon</p>
                    </div>
                </div>
                {{-- Receivables --}}
                <div class="flex items-center gap-3 p-4">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 text-slate-700 shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </span>
                    <div>
                        <p class="text-lg font-bold text-slate-900 leading-none">{{ $currencySymbol }}{{ number_format($receivables, 2) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Receivables</p>
                    </div>
                </div>
                {{-- Payables --}}
                <div class="flex items-center gap-3 p-4">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 text-slate-700 shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </span>
                    <div>
                        <p class="text-lg font-bold text-slate-900 leading-none">{{ $currencySymbol }}{{ number_format($payables, 2) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Payables</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales Overview + Quick Actions --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Sales Overview --}}
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-3 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700">Sales Overview</h3>
                    <div class="inline-flex rounded-md border border-slate-200 p-0.5 bg-slate-50 self-start" role="group" id="salesRangeTabs">
                        <button type="button" data-range="today" class="sales-range-btn px-3 py-1 text-xs font-medium rounded text-slate-600">Today</button>
                        <button type="button" data-range="week" class="sales-range-btn px-3 py-1 text-xs font-medium rounded text-slate-600">7 Days</button>
                        <button type="button" data-range="month" class="sales-range-btn px-3 py-1 text-xs font-medium rounded text-slate-600">30 Days</button>
                        <button type="button" data-range="year" class="sales-range-btn px-3 py-1 text-xs font-medium rounded text-slate-600">This Year</button>
                    </div>
                </div>
                <div class="p-5">
                    <div class="relative h-72">
                        <canvas id="salesOverviewChart"></canvas>
                        <div id="salesOverviewEmpty" class="absolute inset-0 hidden flex-col items-center justify-center text-center text-slate-400">
                            <i class="fa-solid fa-chart-column text-3xl mb-2"></i>
                            <p class="text-sm">No sales data for this period</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-3 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700">Quick Actions</h3>
                </div>
                <div class="p-5 grid grid-cols-1 gap-2.5">
                    <a href="{{ route('invoices.create') }}" class="flex items-center gap-3 h-10 px-4 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus w-4 text-center"></i> New Sale
                    </a>
                    <a href="{{ route('admin.purchases.create') }}" class="flex items-center gap-3 h-10 px-4 rounded-md bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium transition-colors">
                        <i class="fa-solid fa-cart-plus w-4 text-center"></i> New Purchase
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="flex items-center gap-3 h-10 px-4 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-medium transition-colors">
                        <i class="fa-solid fa-box w-4 text-center text-slate-500"></i> Add Product
                    </a>
                    <a href="{{ route('admin.stock-transfers.create') }}" class="flex items-center gap-3 h-10 px-4 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-medium transition-colors">
                        <i class="fa-solid fa-right-left w-4 text-center text-slate-500"></i> Stock Transfer
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 h-10 px-4 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-medium transition-colors">
                        <i class="fa-solid fa-chart-column w-4 text-center text-slate-500"></i> Reports
                    </a>
                </div>
            </div>
        </div>

        {{-- Top Selling Products --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Top Selling Products</h3>
                <span class="text-xs text-slate-400">{{ now()->format('F Y') }}</span>
            </div>
            <div class="p-2">
                @if ($topProducts->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-3 py-2 font-medium">Product</th>
                                    <th class="px-3 py-2 font-medium text-right">Qty Sold</th>
                                    <th class="px-3 py-2 font-medium text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($topProducts as $product)
                                    <tr>
                                        <td class="px-3 py-2.5">
                                            <p class="font-medium text-slate-900">{{ $product->name }}</p>
                                            @if ($product->code)
                                                <p class="text-xs text-slate-400">{{ $product->code }}</p>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-right text-slate-700">{{ number_format($product->total_sold) }}</td>
                                        <td class="px-3 py-2.5 text-right font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format((float) $product->total_revenue, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-44 text-slate-400">
                        <i class="fa-solid fa-ranking-star text-3xl mb-2"></i>
                        <p class="text-sm">No sales recorded this month</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Sales --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Recent Sales</h3>
                <a href="{{ route('admin.sales.index') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View all</a>
            </div>
            <div class="p-2">
                @if ($recentSales->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-3 py-2 font-medium">Invoice</th>
                                    <th class="px-3 py-2 font-medium">Customer</th>
                                    <th class="px-3 py-2 font-medium">Date</th>
                                    <th class="px-3 py-2 font-medium text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentSales as $sale)
                                    <tr>
                                        <td class="px-3 py-2.5 font-medium text-slate-900">{{ $sale->invoice_number }}</td>
                                        <td class="px-3 py-2.5 text-slate-700">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $sale->sale_date?->format('M j, Y') }}</td>
                                        <td class="px-3 py-2.5 text-right font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format((float) $sale->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-32 text-slate-400">
                        <i class="fa-solid fa-receipt text-3xl mb-2"></i>
                        <p class="text-sm">No recent sales</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const salesSeries = @json($salesChart);
            const currencySymbol = @json($currencySymbol);
            const navy = '#0f172a';
            const green = '#059669';

            const canvas = document.getElementById('salesOverviewChart');
            const emptyState = document.getElementById('salesOverviewEmpty');
            const tabs = document.querySelectorAll('.sales-range-btn');
            let salesChart = null;

            const activeClasses = ['bg-white', 'text-slate-900', 'shadow-sm'];
            const inactiveClasses = ['text-slate-600'];

            function setActiveTab(range) {
                tabs.forEach(function (btn) {
                    if (btn.dataset.range === range) {
                        btn.classList.add(...activeClasses);
                        btn.classList.remove(...inactiveClasses);
                    } else {
                        btn.classList.remove(...activeClasses);
                        btn.classList.add(...inactiveClasses);
                    }
                });
            }

            function formatCurrency(value) {
                return currencySymbol + Number(value).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            function renderChart(range) {
                const series = salesSeries[range] || { labels: [], data: [] };
                const hasData = series.data.some(function (v) { return Number(v) > 0; });

                if (!hasData) {
                    emptyState.classList.remove('hidden');
                    emptyState.classList.add('flex');
                } else {
                    emptyState.classList.add('hidden');
                    emptyState.classList.remove('flex');
                }

                if (salesChart) {
                    salesChart.data.labels = series.labels;
                    salesChart.data.datasets[0].data = series.data;
                    salesChart.update();
                    return;
                }

                const ctx = canvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 288);
                gradient.addColorStop(0, 'rgba(5,150,105,0.18)');
                gradient.addColorStop(1, 'rgba(5,150,105,0.00)');

                salesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: series.labels,
                        datasets: [{
                            label: 'Sales',
                            data: series.data,
                            borderColor: green,
                            backgroundColor: gradient,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            pointHoverBackgroundColor: green,
                            tension: 0.35,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: navy,
                                padding: 10,
                                callbacks: {
                                    label: function (context) {
                                        return ' ' + currencySymbol + Number(context.parsed.y).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { color: '#94a3b8', callback: function (value) { return formatCurrency(value); } }
                            }
                        }
                    }
                });
            }

            tabs.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setActiveTab(btn.dataset.range);
                    renderChart(btn.dataset.range);
                });
            });

            setActiveTab('month');
            renderChart('month');
        })();
    </script>
    @endpush
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Reports') }}
            </h2>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- ERP reporting matrix --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-start">

            {{-- Sales & Financial Analysis --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm flex flex-col">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-200">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-emerald-50 text-emerald-600 shrink-0">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>
                    <h3 class="text-sm font-semibold text-slate-800">Sales &amp; Financial Analysis</h3>
                </div>
                <div class="p-2 space-y-0.5">
                    <a href="{{ route('admin.reports.sales') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-file-invoice-dollar w-4 text-center text-slate-400"></i>
                        <span>Sales Summary</span>
                    </a>
                    <a href="{{ route('admin.reports.sales') }}?period=daily" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-calendar-day w-4 text-center text-slate-400"></i>
                        <span>Daily Sales</span>
                    </a>
                    <a href="{{ route('admin.reports.sales') }}?period=monthly" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-calendar-days w-4 text-center text-slate-400"></i>
                        <span>Monthly Sales</span>
                    </a>
                    <a href="{{ route('admin.reports.financial') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-sack-dollar w-4 text-center text-slate-400"></i>
                        <span>Revenue Report</span>
                    </a>
                    <a href="{{ route('admin.reports.financial') }}?view=profit" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-arrow-trend-up w-4 text-center text-slate-400"></i>
                        <span>Profit Analysis</span>
                    </a>
                    <a href="{{ route('admin.reports.financial') }}?view=receivables" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-hand-holding-dollar w-4 text-center text-slate-400"></i>
                        <span>Accounts Receivable</span>
                    </a>
                    <a href="{{ route('admin.reports.financial') }}?view=payables" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-file-invoice w-4 text-center text-slate-400"></i>
                        <span>Supplier Payments Due</span>
                    </a>
                </div>
            </div>

            {{-- Inventory & Supply Chain --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm flex flex-col">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-200">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 text-slate-800 shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                    <h3 class="text-sm font-semibold text-slate-800">Inventory &amp; Supply Chain</h3>
                </div>
                <div class="p-2 space-y-0.5">
                    <a href="{{ route('admin.reports.inventory') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-layer-group w-4 text-center text-slate-400"></i>
                        <span>Stock Level Report</span>
                    </a>
                    <a href="{{ route('admin.reports.inventory') }}?status=low_stock" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-arrow-down-short-wide w-4 text-center text-slate-400"></i>
                        <span>Low Stock Alerts</span>
                    </a>
                    <a href="{{ route('admin.reports.inventory') }}?status=expired" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-ban w-4 text-center text-red-500"></i>
                        <span>Expired Products</span>
                    </a>
                    <a href="{{ route('admin.reports.inventory') }}?status=nearly_expired" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-hourglass-half w-4 text-center text-amber-500"></i>
                        <span>Nearly Expired (DOH)</span>
                    </a>
                    <a href="{{ route('admin.purchases.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-clipboard-list w-4 text-center text-slate-400"></i>
                        <span>Purchase Order History</span>
                    </a>
                    <a href="{{ route('admin.analytics.suppliers') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-truck-fast w-4 text-center text-slate-400"></i>
                        <span>Supplier Performance</span>
                    </a>
                </div>
            </div>

            {{-- Compliance & Regulatory --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm flex flex-col">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-200">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 text-slate-800 shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <h3 class="text-sm font-semibold text-slate-800">Compliance &amp; Regulatory</h3>
                </div>
                <div class="p-2 space-y-0.5">
                    <div class="flex items-center justify-between gap-3 px-3 py-2 rounded-md text-sm text-slate-400 cursor-default" title="Coming soon">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-prescription-bottle-medical w-4 text-center text-red-400"></i>
                            <span>Controlled Substances / Narcotics Log</span>
                        </span>
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 bg-slate-100 rounded px-1.5 py-0.5">Soon</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3 py-2 rounded-md text-sm text-slate-400 cursor-default" title="Coming soon">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-file-export w-4 text-center text-slate-400"></i>
                            <span>DOH Compliance Export</span>
                        </span>
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 bg-slate-100 rounded px-1.5 py-0.5">Soon</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3 py-2 rounded-md text-sm text-slate-400 cursor-default" title="Coming soon">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-trash-can w-4 text-center text-amber-400"></i>
                            <span>Inventory Wastage &amp; Disposal Log</span>
                        </span>
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 bg-slate-100 rounded px-1.5 py-0.5">Soon</span>
                    </div>
                </div>
            </div>

            {{-- Customers & Insurance --}}
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm flex flex-col">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-200">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-emerald-50 text-emerald-600 shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <h3 class="text-sm font-semibold text-slate-800">Customers &amp; Insurance</h3>
                </div>
                <div class="p-2 space-y-0.5">
                    <a href="{{ route('admin.reports.customers') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-chart-pie w-4 text-center text-slate-400"></i>
                        <span>Customer Analysis</span>
                    </a>
                    <a href="{{ route('admin.reports.customers') }}?sort=top" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-ranking-star w-4 text-center text-slate-400"></i>
                        <span>Top Customers</span>
                    </a>
                    <a href="{{ route('admin.reports.customers') }}?view=purchase-history" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fa-solid fa-clock-rotate-left w-4 text-center text-slate-400"></i>
                        <span>Purchase History</span>
                    </a>
                    <div class="flex items-center justify-between gap-3 px-3 py-2 rounded-md text-sm text-slate-400 cursor-default" title="Coming soon">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-file-medical w-4 text-center text-slate-400"></i>
                            <span>Insurance Claims Settlement</span>
                        </span>
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 bg-slate-100 rounded px-1.5 py-0.5">Soon</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3 py-2 rounded-md text-sm text-slate-400 cursor-default" title="Coming soon">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-file-circle-xmark w-4 text-center text-slate-400"></i>
                            <span>Insurance Rejection Log</span>
                        </span>
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 bg-slate-100 rounded px-1.5 py-0.5">Soon</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

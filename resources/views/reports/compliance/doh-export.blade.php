<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reports.index') }}" class="text-slate-400 hover:text-slate-600" title="Back to Reports">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">DOH Compliance Export</h2>
            </div>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <p class="text-sm text-slate-500">Regulatory export summary compiled from current inventory expiry data.</p>

        {{-- Summary tiles --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-red-50 text-red-600 shrink-0">
                        <i class="fa-solid fa-ban"></i>
                    </span>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Expired Batches</div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($summary['expired_batches']) }}</div>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-amber-50 text-amber-600 shrink-0">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </span>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Nearly Expired (30d)</div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($summary['nearly_expired_batches']) }}</div>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 text-slate-800 shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Active Batches</div>
                        <div class="text-2xl font-bold text-slate-900">{{ number_format($summary['total_active_batches']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Export panel --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">From</label>
                        <input type="date" value="{{ $startDate }}" disabled
                            class="w-full rounded-md border-slate-300 bg-slate-50 text-slate-600 text-sm shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">To</label>
                        <input type="date" value="{{ $endDate }}" disabled
                            class="w-full rounded-md border-slate-300 bg-slate-50 text-slate-600 text-sm shadow-sm" />
                    </div>
                </div>
                <div class="shrink-0">
                    <button type="button" disabled
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-slate-100 text-slate-400 text-sm font-medium cursor-not-allowed"
                        title="Coming soon">
                        <i class="fa-solid fa-file-export"></i>
                        Generate Export
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 bg-slate-200 rounded px-1.5 py-0.5">Soon</span>
                    </button>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-400">The formal DOH export file format will be enabled once the regulatory template is finalized. The summary above reflects live inventory expiry data.</p>
        </div>

    </div>
</x-app-layout>

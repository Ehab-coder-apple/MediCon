@props([
    'title',
    'back' => null,
    'analyticsHref' => null,
    'analyticsLabel' => 'Analytics View',
    'analyticsActive' => false,
])

{{--
    Master layout for detailed report subpages (Sales, Inventory, Expiry,
    Compliance, Finance, ...). It standardizes the shell so every report shares
    the same structure:
      - a left-safe toolbar: title on the left, and a streamlined button array on
        the right (Export Options dropdown, Analytics View, Back to Reports);
      - an optional full-width filter ribbon (pass a "filters" slot);
      - an optional 4-up KPI grid (pass a "kpis" slot);
      - the default slot for full-width data tables.
    New report views should wrap their content in <x-report-layout> so they
    automatically inherit this structure. It does not touch controller logic.
--}}
<x-app-layout>
    <x-slot name="header">
        {{-- px-8 gives the 32px left safety margin so the title never sits against the sidebar --}}
        <div class="flex flex-col gap-3 px-8 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">{{ $title }}</h2>

            <div class="flex flex-wrap items-center gap-2">
                @isset($export)
                    <x-dropdown align="right" width="60">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                <span>Export Options</span>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            {{ $export }}
                        </x-slot>
                    </x-dropdown>
                @endisset

                @if($analyticsHref)
                    <a href="{{ $analyticsHref }}" class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none {{ $analyticsActive ? 'bg-slate-900 ring-2 ring-slate-400' : 'bg-slate-800 hover:bg-slate-900' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        <span>{{ $analyticsLabel }}</span>
                    </a>
                @endif

                <a href="{{ $back ?? route('admin.reports.index') }}" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    <span>Back to Reports</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- px-2 reconciles with the layout's main p-6 (24px) to a 32px offset that lines up with the px-8 header toolbar --}}
    <div class="w-full px-2 pb-8">
        @isset($filters)
            <div class="mb-6 rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-4 sm:p-6">
                    {{ $filters }}
                </div>
            </div>
        @endisset

        @isset($kpis)
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {{ $kpis }}
            </div>
        @endisset

        {{ $slot }}
    </div>
</x-app-layout>

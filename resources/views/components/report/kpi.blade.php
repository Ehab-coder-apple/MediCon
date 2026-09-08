@props(['label', 'value'])

{{-- Uniform KPI summary card: monochromatic line icon on the left, value beside it. --}}
<div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-4">
        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-600">
            @isset($icon){{ $icon }}@endisset
        </span>
        <div class="min-w-0">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</div>
            <div class="mt-0.5 truncate text-2xl font-bold text-slate-900">{{ $value }}</div>
        </div>
    </div>
</div>

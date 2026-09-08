@props(['message' => 'No records found for the selected criteria.'])

{{-- Centered empty state inside a bordered white container. --}}
<div class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-400">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5 12 3 3.75 7.5m16.5 0L12 12m8.25-4.5v9L12 21m0-9L3.75 7.5m0 0v9L12 21"/></svg>
        </span>
        <p class="mt-4 text-sm text-slate-500">{{ $message }}</p>
    </div>
</div>

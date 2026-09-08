@props(['title' => null])

{{-- Standardized full-width data table: subtle gray header, charcoal text. --}}
<div class="mb-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    @if($title)
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
        </div>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @isset($foot)
        <div class="border-t border-slate-200 px-5 py-4">
            {{ $foot }}
        </div>
    @endisset
</div>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reports.index') }}" class="text-slate-400 hover:text-slate-600" title="Back to Reports">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Controlled Substances / Narcotics Log</h2>
            </div>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Compliance notice --}}
        <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
            <i class="fa-solid fa-circle-info mt-0.5 text-amber-500"></i>
            <p class="text-sm text-amber-800">
                This regulatory log is ready for controlled-substance dispensing records. Entries will populate automatically once products can be flagged as controlled and prescriber details are captured at point of sale.
            </p>
        </div>

        {{-- Structured log --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Batch No.</th>
                            <th class="px-4 py-3 text-right">Qty Dispensed</th>
                            <th class="px-4 py-3 text-right">Balance</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Prescriber</th>
                            <th class="px-4 py-3">Dispensed By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($entries as $entry)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700">{{ $entry->date ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-800 font-medium">{{ $entry->product ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $entry->batch_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $entry->quantity ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $entry->balance ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $entry->customer ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $entry->prescriber ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $entry->dispensed_by ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12">
                                    <div class="text-center text-slate-500">
                                        <i class="fa-solid fa-prescription-bottle-medical text-3xl text-slate-300"></i>
                                        <p class="mt-3 text-sm">No controlled-substance dispensing records for this period.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>

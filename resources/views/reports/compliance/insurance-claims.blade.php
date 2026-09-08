<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reports.index') }}" class="text-slate-400 hover:text-slate-600" title="Back to Reports">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Insurance Claims Settlement</h2>
            </div>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <div class="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <i class="fa-solid fa-circle-info mt-0.5 text-slate-400"></i>
            <p class="text-sm text-slate-600">
                This log is ready for insurance claim records. Entries will populate once insurance payer details and claim tracking are captured at point of sale.
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Claim No.</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Insurer</th>
                            <th class="px-4 py-3">Invoice</th>
                            <th class="px-4 py-3 text-right">Claimed</th>
                            <th class="px-4 py-3 text-right">Approved</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($claims as $claim)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700">{{ $claim->claim_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $claim->date ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-800 font-medium">{{ $claim->customer ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $claim->insurer ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $claim->invoice ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $claim->claimed_amount ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $claim->approved_amount ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $claim->status ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12">
                                    <div class="text-center text-slate-500">
                                        <i class="fa-solid fa-file-medical text-3xl text-slate-300"></i>
                                        <p class="mt-3 text-sm">No insurance claims recorded yet.</p>
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

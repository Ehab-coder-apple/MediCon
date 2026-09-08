<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reports.index') }}" class="text-slate-400 hover:text-slate-600" title="Back to Reports">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Inventory Wastage &amp; Disposal Log</h2>
            </div>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <p class="text-sm text-slate-500">Expired stock still on hand, listed as disposal candidates for regulatory wastage records.</p>

        {{-- Summary --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Expired Batches</div>
                <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['batch_count']) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Quantity</div>
                <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['total_quantity']) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Est. Cost Value</div>
                <div class="mt-1 text-2xl font-bold text-red-600">${{ number_format($summary['total_value'], 2) }}</div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Batch No.</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Manufacturer</th>
                            <th class="px-4 py-3">Expiry Date</th>
                            <th class="px-4 py-3 text-right">Quantity</th>
                            <th class="px-4 py-3 text-right">Est. Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700">{{ $batch->batch_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-800 font-medium">{{ optional($batch->product)->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $batch->manufacturer ?? optional($batch->product)->manufacturer ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center text-red-600">
                                        <i class="fa-solid fa-triangle-exclamation mr-1.5 text-xs"></i>
                                        {{ $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('M j, Y') : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ number_format($batch->quantity) }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">${{ number_format(($batch->quantity ?? 0) * ($batch->cost_price ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12">
                                    <div class="text-center text-slate-500">
                                        <i class="fa-solid fa-trash-can text-3xl text-slate-300"></i>
                                        <p class="mt-3 text-sm">No expired stock pending disposal.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($batches instanceof \Illuminate\Contracts\Pagination\Paginator || $batches instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div>{{ $batches->links() }}</div>
        @endif

    </div>
</x-app-layout>

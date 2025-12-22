<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center py-4 px-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Stock Transfer Details') }}
            </h2>
            <a href="{{ route('admin.stock-transfers.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800">
                &larr; Back to list
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8 space-y-6">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">
                            Transfer {{ $stockTransfer->reference }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $stockTransfer->fromWarehouse?->name }} &rarr; {{ $stockTransfer->toWarehouse?->name }}
                        </p>
                        @if($stockTransfer->reason)
                            <p class="mt-1 text-sm text-gray-500">
                                <span class="font-semibold">Reason:</span> {{ $stockTransfer->reason }}
                            </p>
                        @endif
                    </div>
                    <div class="mt-4 md:mt-0 text-sm text-gray-600">
                        <div>
                            <span class="font-semibold">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ ucfirst($stockTransfer->status) }}
                            </span>
                        </div>
                        <div class="mt-1">
                            <span class="font-semibold">Date:</span>
                            {{ optional($stockTransfer->transferred_at)->format('Y-m-d H:i') }}
                        </div>
                        <div class="mt-1">
                            <span class="font-semibold">By:</span>
                            {{ $stockTransfer->user?->name }}
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($stockTransfer->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->product?->name ?? ('#' . $item->product_id) }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            @if($item->batch)
                                                {{ $item->batch->batch_number }} ({{ optional($item->batch->expiry_date)->format('Y-m-d') }})
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->quantity }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No items recorded for this transfer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


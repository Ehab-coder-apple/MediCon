<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Return Request: {{ $salesReturn->reference_number }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                }
            @endphp
            <div class="space-x-2">
                <a href="{{ route($routePrefix . 'sales-returns.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Returns
                </a>
                @if($salesReturn->status === 'pending')
                    <form action="{{ route($routePrefix . 'sales-returns.destroy', $salesReturn) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this sales return?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Return Summary -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Return Request Summary</h3>
                </div>
                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Reference Number</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $salesReturn->reference_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Invoice Number</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $salesReturn->invoice->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Return Date</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $salesReturn->return_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="text-lg font-semibold
                                @if($salesReturn->status === 'pending') text-yellow-600
                                @elseif($salesReturn->status === 'approved') text-blue-600
                                @elseif($salesReturn->status === 'completed') text-green-600
                                @else text-red-600
                                @endif">
                                {{ ucfirst($salesReturn->status) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & User Info -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Customer</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $salesReturn->customer->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Created By</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $salesReturn->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Refund Method</p>
                            <p class="text-lg font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $salesReturn->refund_method)) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Return Items -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Returned Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($salesReturn->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->product->code }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->batch_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($item->total_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                                        {{ ucfirst(str_replace('_', ' ', $item->reason)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item->notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 lg:p-8 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-end">
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Total Items: <span class="font-semibold text-gray-900">{{ $salesReturn->total_items }}</span></p>
                            <p class="text-lg text-gray-600 mt-2">Total Amount: <span class="font-semibold text-red-600">{{ number_format($salesReturn->total_amount, 2) }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($salesReturn->notes)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Additional Notes</h3>
                </div>
                <div class="p-6 lg:p-8">
                    <p class="text-gray-700">{{ $salesReturn->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>


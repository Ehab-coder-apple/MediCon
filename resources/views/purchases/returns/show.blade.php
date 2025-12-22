<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Return Request: {{ $purchaseReturn->reference_number }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                }
            @endphp
            <a href="{{ route($routePrefix . 'purchase-returns.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Returns
            </a>
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
                            <p class="text-lg font-semibold text-gray-900">{{ $purchaseReturn->reference_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Purchase Order</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchaseReturn->purchase->reference_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Return Date</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchaseReturn->return_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="text-lg font-semibold
                                @if($purchaseReturn->status === 'pending') text-yellow-600
                                @elseif($purchaseReturn->status === 'approved') text-blue-600
                                @elseif($purchaseReturn->status === 'completed') text-green-600
                                @else text-red-600
                                @endif">
                                {{ ucfirst($purchaseReturn->status) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier & User Info -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Supplier</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchaseReturn->supplier->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Created By</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchaseReturn->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Cost</p>
                            <p class="text-lg font-semibold text-red-600">{{ number_format($purchaseReturn->total_cost, 2) }}</p>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchaseReturn->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->product->code }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->batch_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($item->total_cost, 2) }}</td>
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
            </div>

            <!-- Notes -->
            @if($purchaseReturn->notes)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Additional Notes</h3>
                </div>
                <div class="p-6 lg:p-8">
                    <p class="text-gray-700">{{ $purchaseReturn->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>


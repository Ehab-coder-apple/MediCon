<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Stock Receipt Details') }} - {{ $stockReceiving->formatted_reference }}
            </h2>
            <a href="{{ route('admin.stock-receiving.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to History
            </a>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Receipt Information -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Receipt Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Reference Number:</span>
                            <p class="text-sm text-gray-900 font-medium">{{ $stockReceiving->formatted_reference }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Received Date:</span>
                            <p class="text-sm text-gray-900">{{ $stockReceiving->received_date->format('M d, Y') }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Supplier:</span>
                            <p class="text-sm text-gray-900">{{ $stockReceiving->supplier->name ?? 'Direct Purchase' }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Received By:</span>
                            <p class="text-sm text-gray-900">{{ $stockReceiving->user->name }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($stockReceiving->status === 'completed') bg-green-100 text-green-800
                                @elseif($stockReceiving->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $stockReceiving->status_text }}
                            </span>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Total Items:</span>
                            <p class="text-sm text-gray-900">{{ $stockReceiving->items->count() }} products</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Total Quantity:</span>
                            <p class="text-sm text-gray-900">{{ $stockReceiving->items->sum('quantity') }} units</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Total Cost:</span>
                            <p class="text-lg font-semibold text-gray-900">
                                ${{ number_format($stockReceiving->items->sum('total_cost'), 2) }}
                            </p>
                        </div>
                        
                        @if($stockReceiving->notes)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Notes:</span>
                                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $stockReceiving->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Items Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">📦 Received Items</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Batch
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Expiry Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Paid Qty
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Bonus Qty
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Qty
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cost Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Cost
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($stockReceiving->items as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $item->product->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $item->product->code }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $item->batch_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $item->expiry_date->format('M d, Y') }}
                                            </div>
                                            @if($item->is_expiring_soon)
                                                <div class="text-xs text-yellow-600">
                                                    ⚠️ Expires in {{ $item->days_until_expiry }} days
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ number_format($item->quantity) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($item->bonus_quantity > 0)
                                                <div class="text-sm text-green-600 font-medium">
                                                    +{{ number_format($item->bonus_quantity) }}
                                                </div>
                                                @if($item->bonus_notes)
                                                    <div class="text-xs text-green-500">
                                                        {{ $item->bonus_notes }}
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-sm text-gray-400">-</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ number_format($item->total_quantity) }}
                                            </div>
                                            @if($item->bonus_quantity > 0)
                                                <div class="text-xs text-green-600">
                                                    ({{ number_format($item->quantity) }} + {{ number_format($item->bonus_quantity) }})
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">${{ number_format($item->cost_price, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                ${{ number_format($item->total_cost, 2) }}
                                            </div>
                                            @if($item->bonus_quantity > 0)
                                                <div class="text-xs text-green-600">
                                                    (Bonus: $0.00)
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                        Totals:
                                    </td>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                        {{ number_format($stockReceiving->total_paid_quantity) }}
                                    </td>
                                    <td class="px-6 py-3 text-sm font-medium text-green-600">
                                        +{{ number_format($stockReceiving->total_bonus_quantity) }}
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                        {{ number_format($stockReceiving->total_quantity) }}
                                    </td>
                                    <td class="px-6 py-3"></td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                        ${{ number_format($stockReceiving->items->sum('total_cost'), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.stock-receiving.create') }}"
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        📦 Receive More Stock
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

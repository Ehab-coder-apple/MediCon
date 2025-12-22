<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Purchase Order: {{ $purchase->reference_number }}
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
                @if($purchase->status === 'pending')
                    <a href="{{ route($routePrefix . 'purchases.edit', $purchase) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Purchase
                    </a>
                    <form action="{{ route($routePrefix . 'purchases.complete', $purchase) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                            onclick="return confirm('Mark this purchase as completed?')">
                            Mark Complete
                        </button>
                    </form>
                @elseif($purchase->status === 'completed')
                    <a href="{{ route($routePrefix . 'purchases.receive-stock', $purchase) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a1 1 0 001 1h12a1 1 0 001-1V6a2 2 0 00-2-2H4zm0 6v4a2 2 0 002 2h8a2 2 0 002-2v-4H4z" clip-rule="evenodd"></path>
                        </svg>
                        Receive Stock
                    </a>
                    <a href="{{ route($routePrefix . 'purchases.return.create', $purchase) }}" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                        <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 110-14H9.828a1 1 0 11 0 2H11a5 5 0 100 10H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Return Items
                    </a>
                @endif
                <a href="{{ route($routePrefix . 'purchases.export-pdf', $purchase) }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" target="_blank">
                    <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                    </svg>
                    Export to PDF
                </a>
                <a href="{{ route($routePrefix . 'purchases.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Purchases
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Purchase Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Purchase Order Details
                    </h1>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Reference Number</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $purchase->reference_number }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Purchase Date</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $purchase->purchase_date->format('M d, Y') }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Cost</h3>
                            <p class="mt-1 text-2xl font-bold text-green-600">${{ number_format($purchase->total_cost, 2) }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($purchase->status === 'completed') bg-green-100 text-green-800
                                    @elseif($purchase->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Supplier</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $purchase->supplier->name }}</p>
                            <p class="text-sm text-gray-500">{{ $purchase->supplier->contact_person }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Contact Info</h3>
                            <p class="mt-1 text-gray-900">{{ $purchase->supplier->phone }}</p>
                            <p class="text-sm text-gray-500">{{ $purchase->supplier->email }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created By</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $purchase->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $purchase->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Items Summary</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $purchase->total_products }} products</p>
                            <p class="text-sm text-gray-500">{{ $purchase->total_quantity }} total quantity</p>
                        </div>
                    </div>
                    
                    @if($purchase->notes)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500">Notes</h3>
                            <p class="mt-1 text-gray-900">{{ $purchase->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Purchase Items -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Purchase Items
                    </h1>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit Cost
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Cost
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Batch Info
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchase->purchaseItems as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->product->code }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${{ number_format($item->unit_cost, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-green-600">${{ number_format($item->total_cost, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->batch_number)
                                            <div class="text-sm text-gray-900">Batch: {{ $item->batch_number }}</div>
                                            @if($item->expiry_date)
                                                <div class="text-sm text-gray-500">Expires: {{ $item->expiry_date->format('M d, Y') }}</div>
                                            @endif
                                        @else
                                            <div class="text-sm text-gray-500">No batch info</div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                        Total:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-green-600">${{ number_format($purchase->total_cost, 2) }}</div>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

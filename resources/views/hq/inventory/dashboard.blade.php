<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('HQ Inventory Dashboard') }}
            </h2>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        @if (session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Central Warehouses --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse ($centralWarehouses as $warehouse)
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-600">{{ $warehouse->name }}</p>
                        <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-slate-100 text-slate-700">
                            <i class="fa-solid fa-warehouse"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($warehouse->stock_quantity ?? 0) }}</p>
                    <p class="mt-1 text-xs text-slate-400">Units on hand</p>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-4 bg-white border border-slate-200 rounded-lg shadow-sm p-5 text-center text-slate-400 text-sm">
                    No Central warehouses found for this organization yet.
                </div>
            @endforelse
        </div>

        {{-- Pending / In-Transit Stock Transfer Orders --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Pending &amp; In-Transit Stock Transfer Orders</h3>
                <span class="text-xs text-slate-400">{{ $pendingOrders->count() }} order(s)</span>
            </div>
            <div class="p-2">
                @if ($pendingOrders->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-3 py-2 font-medium">Order #</th>
                                    <th class="px-3 py-2 font-medium">From</th>
                                    <th class="px-3 py-2 font-medium">To</th>
                                    <th class="px-3 py-2 font-medium">Items</th>
                                    <th class="px-3 py-2 font-medium">Initiated By</th>
                                    <th class="px-3 py-2 font-medium">Status</th>
                                    <th class="px-3 py-2 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($pendingOrders as $order)
                                    <tr>
                                        <td class="px-3 py-2.5 font-medium text-slate-900">#{{ $order->id }}</td>
                                        <td class="px-3 py-2.5 text-slate-700">{{ $order->sourceWarehouse->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-slate-700">{{ $order->destinationWarehouse->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $order->items->count() }} product(s)</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $order->initiator->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5">
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $order->isPending() ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">
                                                {{ $order->isPending() ? 'Pending' : 'In Transit' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                            @if ($order->isPending())
                                                <form method="POST" action="{{ route('stock-transfer-orders.in-transit', $order) }}" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 font-medium mr-3">Dispatch</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('stock-transfer-orders.cancel', $order) }}" style="display:inline;" onsubmit="return confirm('Cancel this stock transfer order?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Cancel</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-32 text-slate-400">
                        <i class="fa-solid fa-truck-ramp-box text-3xl mb-2"></i>
                        <p class="text-sm">No pending stock transfer orders</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recently Completed / Cancelled Orders --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Recent Order History</h3>
            </div>
            <div class="p-2">
                @if ($recentOrders->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-3 py-2 font-medium">Order #</th>
                                    <th class="px-3 py-2 font-medium">From</th>
                                    <th class="px-3 py-2 font-medium">To</th>
                                    <th class="px-3 py-2 font-medium">Received By</th>
                                    <th class="px-3 py-2 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td class="px-3 py-2.5 font-medium text-slate-900">#{{ $order->id }}</td>
                                        <td class="px-3 py-2.5 text-slate-700">{{ $order->sourceWarehouse->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-slate-700">{{ $order->destinationWarehouse->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $order->receiver->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5">
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $order->isReceived() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-32 text-slate-400">
                        <i class="fa-solid fa-clock-rotate-left text-3xl mb-2"></i>
                        <p class="text-sm">No completed orders yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

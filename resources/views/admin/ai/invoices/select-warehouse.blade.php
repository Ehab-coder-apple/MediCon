@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-slate-900 mb-2">🏭 Select Warehouse</h1>
            <p class="text-slate-600">Invoice #{{ $invoice->invoice_number ?? 'N/A' }} • {{ $invoice->items->count() }} items to transfer</p>
        </div>
        <a href="{{ route('admin.ai.invoices.show', $invoice->id) }}" class="px-6 py-2 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-800 transition duration-200">
            ← Back to Invoice
        </a>
    </div>

    <!-- Items Summary -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-t-4 border-slate-700">
        <h3 class="text-lg font-bold text-slate-900 mb-4">📦 Items to Transfer</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Product Name</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Quantity</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Unit Price</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-900">{{ $item->product_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-slate-900">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-slate-900">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 text-slate-900 font-semibold">${{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-slate-600">No items found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Warehouse Selection -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-t-4 border-teal-600">
        <h3 class="text-lg font-bold text-slate-900 mb-4">🏭 Select Destination Warehouse</h3>

        @if($warehouses->isEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <p class="text-amber-800 font-semibold">⚠️ No Warehouses Available</p>
                <p class="text-amber-700 text-sm mt-2">Please create a warehouse first before transferring items.</p>
            </div>
        @else
            <form method="POST" action="{{ route('admin.ai.invoices.approve-warehouse-transfer', $invoice->id) }}">
                @csrf
                <div class="mb-6">
                    <label for="warehouse_id" class="block text-sm font-semibold text-slate-700 mb-3">Choose Warehouse</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($warehouses as $warehouse)
                            <label class="flex items-center p-4 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-teal-600 hover:bg-teal-50 transition duration-200">
                                <input type="radio" name="warehouse_id" value="{{ $warehouse->id }}" required class="w-4 h-4 text-teal-600">
                                <div class="ml-4">
                                    <p class="font-semibold text-slate-900">{{ $warehouse->name }}</p>
                                    <p class="text-sm text-slate-600">{{ ucfirst(str_replace('_', ' ', $warehouse->type)) }}</p>
                                    @if($warehouse->specifications)
                                        <p class="text-xs text-slate-500 mt-1">{{ $warehouse->specifications }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('warehouse_id')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition duration-200">
                    ✓ Confirm Transfer to Warehouse
                </button>
            </form>
        @endif
    </div>

    <!-- Transfer Information -->
    <div class="bg-teal-50 border border-teal-200 rounded-lg p-6">
        <h3 class="text-lg font-bold text-teal-900 mb-3">ℹ️ Transfer Information</h3>
        <ul class="text-teal-800 text-sm space-y-2">
            <li>✓ All {{ $invoice->items->count() }} items will be transferred to the selected warehouse</li>
            <li>✓ Inventory will be updated automatically</li>
            <li>✓ Batch records will be created for tracking</li>
            <li>✓ Transfer will be logged for audit purposes</li>
        </ul>
    </div>
</div>
@endsection


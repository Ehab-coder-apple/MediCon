@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">📋 Invoice Processing</h1>
            <p class="text-gray-600">Manage processed purchase order invoices</p>
        </div>
        <a href="{{ route('admin.ai.dashboard') }}" class="px-6 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-blue-600">
        <h3 class="text-lg font-bold text-gray-900 mb-4">🔍 Filter Invoices</h3>
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" name="search" placeholder="Search invoice number..." class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <select name="workflow_stage" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">All Workflow Stages</option>
                    <option value="uploaded">📤 Uploaded</option>
                    <option value="approved_for_processing">✓ Approved for Processing</option>
                    <option value="processing">⚙️ Processing</option>
                    <option value="processed">📊 Processed</option>
                    <option value="approved_for_inventory">✓ Approved for Inventory</option>
                    <option value="completed">✅ Completed</option>
                </select>
                <button type="submit" style="background-color: #9333ea; color: white;" class="px-8 py-2 font-semibold rounded-lg hover:shadow-lg transition inline-block">
                    🔍 Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold">Invoice #</th>
                    <th class="px-6 py-3 text-left font-semibold">Date</th>
                    <th class="px-6 py-3 text-left font-semibold">Supplier</th>
                    <th class="px-6 py-3 text-left font-semibold">Amount</th>
                    <th class="px-6 py-3 text-left font-semibold">Workflow Stage</th>
                    <th class="px-6 py-3 text-left font-semibold">Items</th>
                    <th class="px-6 py-3 text-left font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $invoice->invoice_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $invoice->invoice_date?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $invoice->supplier_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($invoice->workflow_stage === 'uploaded')
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">📤 Uploaded</span>
                            @elseif($invoice->workflow_stage === 'approved_for_processing')
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">✓ Approved for Processing</span>
                            @elseif($invoice->workflow_stage === 'processing')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">⚙️ Processing</span>
                            @elseif($invoice->workflow_stage === 'processed')
                                <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-semibold">📊 Processed</span>
                            @elseif($invoice->workflow_stage === 'approved_for_inventory')
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">✓ Approved for Inventory</span>
                            @else
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">✅ Completed</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $invoice->items->count() }} items</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.ai.invoices.show', $invoice->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                View Details →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                            <p class="text-lg">📭 No invoices found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $invoices->links() }}
    </div>
</div>
@endsection


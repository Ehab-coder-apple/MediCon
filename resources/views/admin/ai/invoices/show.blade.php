@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-slate-900 mb-2">📋 Invoice Details</h1>
            <p class="text-slate-600">Invoice #{{ $invoice->invoice_number ?? 'N/A' }} • Workflow: <span class="font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', $invoice->workflow_stage)) }}</span></p>
        </div>
        <a href="{{ route('admin.ai.invoices.index') }}" class="px-6 py-2 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-800 transition duration-200">
            ← Back to Invoices
        </a>
    </div>

    <!-- Workflow Progress -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-t-4 border-slate-700">
        <h3 class="text-lg font-bold text-slate-900 mb-4">📊 Workflow Progress</h3>
        <div class="flex items-center justify-between">
            <!-- Stage 1: Uploaded -->
            <div class="flex flex-col items-center flex-1">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white {{ $invoice->workflow_stage === 'uploaded' || in_array($invoice->workflow_stage, ['approved_for_processing', 'processing', 'processed', 'approved_for_inventory', 'completed']) ? 'bg-slate-700' : 'bg-slate-300' }}">
                    1
                </div>
                <p class="text-sm font-semibold text-slate-900 mt-2">Uploaded</p>
            </div>
            <div class="flex-1 h-1 {{ in_array($invoice->workflow_stage, ['approved_for_processing', 'processing', 'processed', 'approved_for_inventory', 'completed']) ? 'bg-slate-700' : 'bg-slate-300' }}"></div>

            <!-- Stage 2: Approved for Processing -->
            <div class="flex flex-col items-center flex-1">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white {{ in_array($invoice->workflow_stage, ['approved_for_processing', 'processing', 'processed', 'approved_for_inventory', 'completed']) ? 'bg-slate-700' : 'bg-slate-300' }}">
                    2
                </div>
                <p class="text-sm font-semibold text-slate-900 mt-2">Approve OCR</p>
            </div>
            <div class="flex-1 h-1 {{ in_array($invoice->workflow_stage, ['processing', 'processed', 'approved_for_inventory', 'completed']) ? 'bg-slate-700' : 'bg-slate-300' }}"></div>

            <!-- Stage 3: Processing -->
            <div class="flex flex-col items-center flex-1">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white {{ in_array($invoice->workflow_stage, ['processing', 'processed', 'approved_for_inventory', 'completed']) ? 'bg-slate-700' : 'bg-slate-300' }}">
                    3
                </div>
                <p class="text-sm font-semibold text-slate-900 mt-2">Processing</p>
            </div>
            <div class="flex-1 h-1 {{ in_array($invoice->workflow_stage, ['processed', 'approved_for_inventory', 'completed']) ? 'bg-slate-700' : 'bg-slate-300' }}"></div>

            <!-- Stage 4: Processed -->
            <div class="flex flex-col items-center flex-1">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white {{ in_array($invoice->workflow_stage, ['processed', 'approved_for_inventory', 'completed']) ? 'bg-slate-700' : 'bg-slate-300' }}">
                    4
                </div>
                <p class="text-sm font-semibold text-slate-900 mt-2">Approve Inventory</p>
            </div>
            <div class="flex-1 h-1 {{ in_array($invoice->workflow_stage, ['approved_for_inventory', 'completed']) ? 'bg-slate-700' : 'bg-slate-300' }}"></div>

            <!-- Stage 5: Completed -->
            <div class="flex flex-col items-center flex-1">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white {{ $invoice->workflow_stage === 'completed' ? 'bg-emerald-600' : 'bg-slate-300' }}">
                    5
                </div>
                <p class="text-sm font-semibold text-slate-900 mt-2">Completed</p>
            </div>
        </div>
    </div>

    <!-- PDF Preview (Stage 1: Uploaded) -->
    @if($invoice->workflow_stage === 'uploaded' && $invoice->aiDocument)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-cyan-600">
            <h3 class="text-lg font-bold text-slate-900 mb-4">📄 Original Invoice Document</h3>
            <div class="bg-slate-50 rounded-lg p-2 mb-4 border border-slate-200" style="height: 500px; overflow: hidden;">
                <iframe
                    src="{{ route('admin.ai.invoices.view-document', $invoice->id) }}"
                    class="w-full h-full rounded-md border-none"
                    style="min-height: 480px;">
                </iframe>
            </div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-slate-600">
                    📄 {{ $invoice->aiDocument->file_name }}
                    <span class="text-slate-500">({{ number_format($invoice->aiDocument->file_size / 1024, 2) }} KB)</span>
                </p>
                <a
                    href="{{ route('admin.ai.invoices.view-document', $invoice->id) }}"
                    target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-slate-700 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition duration-200"
                >
                    🔍 Open in New Tab
                </a>
            </div>
            <p class="text-sm text-slate-600 mb-4">Review the original invoice document above. If it looks correct, click "Approve for Processing" to start OCR extraction.</p>
        </div>
    @endif

    <!-- Invoice Info Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-slate-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-slate-600 text-sm font-medium">Invoice Number</p>
                <p class="text-2xl font-bold text-slate-900">{{ $invoice->invoice_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-slate-600 text-sm font-medium">Invoice Date</p>
                <p class="text-2xl font-bold text-slate-900">{{ $invoice->invoice_date?->format('M d, Y') ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-slate-600 text-sm font-medium">Supplier</p>
                <p class="text-2xl font-bold text-slate-900">{{ $invoice->supplier_name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-slate-600 text-sm font-medium">Total Amount</p>
                <p class="text-2xl font-bold text-emerald-600">${{ number_format($invoice->total_amount, 2) }}</p>
            </div>
            <div>
                <p class="text-slate-600 text-sm font-medium">Workflow Stage</p>
                <p class="text-lg font-bold">
                    @if($invoice->workflow_stage === 'uploaded')
                        <span class="px-3 py-1 bg-slate-100 text-slate-800 rounded-full text-sm font-semibold">📤 Uploaded</span>
                    @elseif($invoice->workflow_stage === 'approved_for_processing')
                        <span class="px-3 py-1 bg-cyan-100 text-cyan-800 rounded-full text-sm font-semibold">✓ Approved for Processing</span>
                    @elseif($invoice->workflow_stage === 'processing')
                        <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-sm font-semibold">⚙️ Processing</span>
                    @elseif($invoice->workflow_stage === 'processed')
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-semibold">📊 Processed</span>
                    @elseif($invoice->workflow_stage === 'approved_for_inventory')
                        <span class="px-3 py-1 bg-teal-100 text-teal-800 rounded-full text-sm font-semibold">✓ Approved for Inventory</span>
                    @else
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-sm font-semibold">✅ Completed</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-slate-600 text-sm font-medium">Items in Inventory</p>
                <p class="text-lg font-bold text-slate-900">{{ $invoice->items_added_to_inventory ?? 0 }} items</p>
            </div>
        </div>
    </div>

    <!-- Invoice Items (Only show after processing) -->
    @if(in_array($invoice->workflow_stage, ['processing', 'processed', 'approved_for_inventory', 'completed']))
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-slate-700 to-slate-800 text-white px-6 py-4">
                <h3 class="text-xl font-bold">📦 Extracted Invoice Items</h3>
            </div>
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Product Name</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Quantity</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Unit Price</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Total Price</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Confidence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->product_name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-slate-600">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">${{ number_format($item->total_price, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-full bg-slate-200 rounded-full h-2 mr-2">
                                        <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $item->confidence_score }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-900">{{ $item->confidence_score }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-600">
                                <p>No items found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- PDF Upload Section -->
    @if($invoice->workflow_stage === 'uploaded' && !$invoice->pdf_file_path)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-cyan-600">
            <h3 class="text-lg font-bold text-slate-900 mb-4">📤 Upload Invoice PDF</h3>
            <form method="POST" action="{{ route('admin.ai.invoices.upload-pdf', $invoice->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="pdf_file" class="block text-sm font-semibold text-slate-700 mb-2">Select PDF File</label>
                    <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-700 focus:border-transparent">
                    @error('pdf_file')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full px-6 py-3 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-800 transition duration-200">
                    📤 Upload PDF
                </button>
            </form>
            <p class="text-sm text-slate-600 mt-4">Upload the invoice PDF file. Maximum file size: 10MB</p>
        </div>
    @endif

    <!-- PDF Uploaded - Convert to Items -->
    @if($invoice->pdf_file_path && $invoice->extraction_status === 'pending')
        <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-emerald-600">
            <h3 class="text-lg font-bold text-slate-900 mb-4">🔄 Convert PDF to Items</h3>
            <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <p class="text-emerald-800 font-semibold">✓ PDF Uploaded Successfully</p>
                <p class="text-emerald-700 text-sm mt-2">File: <strong>{{ $invoice->pdf_file_name }}</strong> ({{ number_format($invoice->pdf_file_size / 1024, 2) }} KB)</p>
            </div>
            <form method="POST" action="{{ route('admin.ai.invoices.convert-to-items', $invoice->id) }}">
                @csrf
                <button type="submit" class="w-full px-6 py-3 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition duration-200">
                    🔄 Convert to Items
                </button>
            </form>
            <p class="text-sm text-slate-600 mt-4">Click the button above to extract items from the PDF. The system will parse the document and create item records.</p>
        </div>
    @endif

    <!-- Items Extracted - Ready for Warehouse Transfer -->
    @if($invoice->extraction_status === 'completed' && $invoice->items && $invoice->items->count() > 0 && !$invoice->warehouse_id)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-teal-600">
            <h3 class="text-lg font-bold text-slate-900 mb-4">🏭 Transfer to Warehouse</h3>
            <div class="mb-4 bg-teal-50 border border-teal-200 rounded-lg p-4">
                <p class="text-teal-800 font-semibold">✓ Items Ready for Transfer</p>
                <p class="text-teal-700 text-sm mt-2">{{ $invoice->items->count() }} items extracted and ready to be transferred to a warehouse.</p>
            </div>
            <a href="{{ route('admin.ai.invoices.select-warehouse', $invoice->id) }}" class="w-full block text-center px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition duration-200">
                🏭 Select Warehouse & Transfer
            </a>
            <p class="text-sm text-slate-600 mt-4">Choose a warehouse to transfer the extracted items to your inventory system.</p>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-t-4 border-slate-700">
        <h3 class="text-lg font-bold text-slate-900 mb-4">⚡ Actions</h3>

        <!-- Stage 1: Uploaded - Approve for Processing -->
        @if($invoice->workflow_stage === 'uploaded')
            <div class="flex gap-4">
                <form method="POST" action="{{ route('admin.ai.invoices.approve-for-processing', $invoice->id) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-800 transition duration-200">
                        ✓ Approve for OCR Processing
                    </button>
                </form>
            </div>
            <p class="text-sm text-slate-600 mt-4">Click the button above to approve this invoice for OCR extraction. The system will extract data from the PDF document.</p>
        @endif

        <!-- Stage 2-3: Processing - Show status -->
        @if(in_array($invoice->workflow_stage, ['approved_for_processing', 'processing']))
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <p class="text-amber-800 font-semibold">⚙️ Processing in Progress</p>
                <p class="text-amber-700 text-sm mt-2">The system is extracting data from the invoice. This may take a few moments. Please refresh the page to check the status.</p>
            </div>
        @endif

        <!-- Stage 4: Processed - Approve for Inventory -->
        @if($invoice->workflow_stage === 'processed')
            <div class="mb-4 bg-cyan-50 border border-cyan-200 rounded-lg p-4">
                <p class="text-cyan-800 font-semibold">✓ OCR Processing Complete</p>
                <p class="text-cyan-700 text-sm mt-2">The invoice has been successfully processed. Review the extracted items above and click "Approve for Inventory" to add them to your inventory system.</p>
            </div>
            <form method="POST" action="{{ route('admin.ai.invoices.approve-for-inventory', $invoice->id) }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full px-6 py-3 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition duration-200">
                    ✓ Approve for Inventory Upload
                </button>
            </form>
        @endif

        <!-- Stage 5: Completed -->
        @if($invoice->workflow_stage === 'completed')
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <p class="text-emerald-800 font-semibold">✅ Invoice Processing Complete</p>
                <p class="text-emerald-700 text-sm mt-2">{{ $invoice->items_added_to_inventory }} items have been successfully added to your inventory.</p>
            </div>
        @endif
    </div>

    <!-- Approval History -->
    <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-slate-700">
        <h3 class="text-lg font-bold text-slate-900 mb-4">📋 Approval History</h3>
        <div class="space-y-3">
            @if($invoice->approved_for_processing_at)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <div>
                        <p class="font-semibold text-slate-900">✓ Approved for Processing</p>
                        <p class="text-sm text-slate-600">by {{ $invoice->approvedForProcessingBy->name ?? 'System' }}</p>
                    </div>
                    <p class="text-sm text-slate-600">{{ $invoice->approved_for_processing_at->format('M d, Y H:i') }}</p>
                </div>
            @endif

            @if($invoice->processing_started_at)
                <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg border border-amber-200">
                    <div>
                        <p class="font-semibold text-slate-900">⚙️ Processing Started</p>
                    </div>
                    <p class="text-sm text-slate-600">{{ $invoice->processing_started_at->format('M d, Y H:i') }}</p>
                </div>
            @endif

            @if($invoice->processing_completed_at)
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200">
                    <div>
                        <p class="font-semibold text-slate-900">📊 Processing Completed</p>
                    </div>
                    <p class="text-sm text-slate-600">{{ $invoice->processing_completed_at->format('M d, Y H:i') }}</p>
                </div>
            @endif

            @if($invoice->approved_for_inventory_at)
                <div class="flex items-center justify-between p-3 bg-teal-50 rounded-lg border border-teal-200">
                    <div>
                        <p class="font-semibold text-slate-900">✓ Approved for Inventory</p>
                        <p class="text-sm text-slate-600">by {{ $invoice->approvedForInventoryBy->name ?? 'System' }}</p>
                    </div>
                    <p class="text-sm text-slate-600">{{ $invoice->approved_for_inventory_at->format('M d, Y H:i') }}</p>
                </div>
            @endif

            @if($invoice->inventory_uploaded_at)
                <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                    <div>
                        <p class="font-semibold text-slate-900">✅ Inventory Upload Complete</p>
                    </div>
                    <p class="text-sm text-slate-600">{{ $invoice->inventory_uploaded_at->format('M d, Y H:i') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


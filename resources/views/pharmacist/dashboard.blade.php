<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pharmacist Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showRequisitionPanel: false, showReplenishPanel: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="mt-8 text-2xl font-medium text-gray-900">
                        Welcome to MediCon Pharmacist Dashboard!
                    </h1>

                    <p class="mt-6 text-gray-500 leading-relaxed">
                        Manage inventory, sales, and monitor medicine stock levels efficiently.
                    </p>
                </div>

                <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 p-6 lg:p-8">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $salesToday ?? 0 }}</div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">Sales Today</div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-red-600">{{ $inventoryAlerts }}</div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">Low Stock Alerts</div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-green-600">{{ $totalMedicines }}</div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">Total Medicines</div>
                    </div>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <a href="{{ route('invoices.index') }}" class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                                    Manage Sales & Invoices
                                </a>
                                <a href="{{ route('pharmacist.inventory') }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                                    Check Inventory
                                </a>
                                <button class="w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                    Generate Reports
                                </button>
                                <button type="button" @click="showReplenishPanel = true" class="w-full bg-teal-600 hover:bg-teal-800 text-white font-bold py-2 px-4 rounded">
                                    Replenish Shelf from Backroom
                                </button>
                                <button type="button" @click="showRequisitionPanel = true" class="w-full bg-amber-500 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded">
                                    Request Stock from HQ
                                </button>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">My Stock Requisitions</h3>
                            <div class="space-y-3">
                                @forelse ($myRequisitions as $requisition)
                                    <div class="flex justify-between items-center p-3 bg-white rounded">
                                        <div>
                                            <span class="text-sm text-gray-700 font-medium">{{ $requisition->display_name }}</span>
                                            <span class="text-xs text-gray-400 block">Qty: {{ number_format($requisition->requested_quantity) }} &middot; {{ $requisition->created_at->diffForHumans() }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                            @if($requisition->isApproved()) bg-emerald-50 text-emerald-700
                                            @elseif($requisition->isRejected()) bg-red-50 text-red-700
                                            @else bg-amber-50 text-amber-700
                                            @endif">
                                            {{ ucfirst($requisition->status) }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-400 p-3">No requisitions submitted yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Slide-over: Request Stock from HQ --}}
        <div x-show="showRequisitionPanel" x-cloak class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            <div class="absolute inset-0 bg-black bg-opacity-40" @click="showRequisitionPanel = false"></div>
            <div class="absolute inset-y-0 right-0 max-w-full flex" x-show="showRequisitionPanel" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                <div class="w-screen max-w-md bg-white shadow-xl flex flex-col h-full">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Request Stock from HQ</h3>
                        <button type="button" @click="showRequisitionPanel = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('branch-requisitions.store') }}" class="flex-1 flex flex-col overflow-y-auto">
                        @csrf
                        <div class="p-6 space-y-4 flex-1">
                            <p class="text-sm text-gray-500">
                                Requesting on behalf of: <span class="font-medium text-gray-700">{{ $activeBranch->name ?? 'No branch assigned' }}</span>
                            </p>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product (existing catalog item)</label>
                                <select name="product_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">— Not in catalog yet (type name below) —</option>
                                    @foreach ($requisitionProducts as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Or type a new drug name</label>
                                <input type="text" name="custom_product_name" placeholder="e.g. Amoxicillin 500mg" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <p class="text-xs text-gray-400 mt-1">Use this only if the drug isn't already in the product catalog.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Requested</label>
                                <input type="number" name="requested_quantity" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                                <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Any context for HQ..."></textarea>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                            <button type="button" @click="showRequisitionPanel = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Slide-over: Replenish Shelf from Backroom --}}
        <div x-show="showReplenishPanel" x-cloak class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            <div class="absolute inset-0 bg-black bg-opacity-40" @click="showReplenishPanel = false"></div>
            <div class="absolute inset-y-0 right-0 max-w-full flex" x-show="showReplenishPanel" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                <div class="w-screen max-w-md bg-white shadow-xl flex flex-col h-full">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Replenish Shelf from Backroom</h3>
                        <button type="button" @click="showReplenishPanel = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('local-inventory.replenish') }}" class="flex-1 flex flex-col overflow-y-auto">
                        @csrf
                        <div class="p-6 space-y-4 flex-1">
                            <p class="text-sm text-gray-500">
                                Moving stock within: <span class="font-medium text-gray-700">{{ $activeBranch->name ?? 'No branch assigned' }}</span>
                                (Backroom &rarr; Dispensing Shelf)
                            </p>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                <select name="product_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <option value="">Select a product...</option>
                                    @foreach ($requisitionProducts as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Move</label>
                                <input type="number" name="quantity" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                            <button type="button" @click="showReplenishPanel = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-md hover:bg-teal-700">Move Stock</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

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

        {{-- Slide-over: Request Stock from HQ (Odoo-style batch requisition grid) --}}
        <div x-show="showRequisitionPanel" x-cloak class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            <div class="absolute inset-0 bg-black bg-opacity-40" @click="showRequisitionPanel = false"></div>
            <div class="absolute inset-y-0 right-0 max-w-full flex" x-show="showRequisitionPanel" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                <div class="w-screen max-w-5xl bg-white shadow-xl flex flex-col h-full"
                     x-data="requisitionWorkspace(@js($requisitionProducts->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'code' => $p->code])))">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Request Stock from HQ</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Requesting on behalf of: <span class="font-medium text-gray-700">{{ $activeBranch->name ?? 'No branch assigned' }}</span></p>
                        </div>
                        <button type="button" @click="showRequisitionPanel = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <div class="flex-1 grid grid-cols-1 md:grid-cols-5 overflow-hidden">
                        {{-- Left column: Product Grid --}}
                        <div class="md:col-span-2 border-r border-gray-200 flex flex-col overflow-hidden bg-gray-50">
                            <div class="p-3 border-b border-gray-200">
                                <input type="text" x-model="search" placeholder="Search catalog..."
                                       class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div class="flex-1 overflow-y-auto p-3 grid grid-cols-2 gap-2 content-start">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <button type="button" @click="addProduct(product)"
                                            class="text-left p-3 bg-white border rounded-lg shadow-sm hover:border-amber-400 hover:shadow transition"
                                            :class="cart[product.id] ? 'border-amber-500 ring-1 ring-amber-400' : 'border-gray-200'">
                                        <div class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></div>
                                        <div class="text-xs text-gray-400" x-text="product.code"></div>
                                    </button>
                                </template>
                                <template x-if="filteredProducts.length === 0">
                                    <div class="col-span-2 text-sm text-gray-400 text-center py-6">No matching products.</div>
                                </template>
                            </div>
                        </div>

                        {{-- Right column: The Requisition Cart --}}
                        <div class="md:col-span-3 flex flex-col overflow-hidden">
                            <div class="flex-1 overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Qty Requested</th>
                                            <th class="px-4 py-2 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="item in cartItems" :key="item.id">
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <div class="text-sm font-medium text-gray-900" x-text="item.name"></div>
                                                    <div class="text-xs text-gray-400" x-text="item.code"></div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" min="1" :id="'qty-' + item.id"
                                                           x-model.number="cart[item.id].quantity"
                                                           @focus="$el.select()"
                                                           class="w-24 text-lg font-semibold text-center rounded-md border-gray-300 focus:border-amber-500 focus:ring-amber-500">
                                                </td>
                                                <td class="px-4 py-2 text-right">
                                                    <button type="button" @click="removeProduct(item.id)" class="text-gray-400 hover:text-red-600">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="cartItems.length === 0">
                                            <tr><td colspan="3" class="px-4 py-10 text-center text-sm text-gray-400">Click a product on the left to add it here.</td></tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="border-t border-gray-200 p-4 space-y-3">
                                <input type="text" x-model="notes" placeholder="Optional note for HQ (applies to this batch)..."
                                       class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <template x-if="error">
                                    <p class="text-sm text-red-600" x-text="error"></p>
                                </template>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500"><span x-text="cartCount"></span> item(s) selected</span>
                                    <div class="space-x-3">
                                        <button type="button" @click="showRequisitionPanel = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                                        <button type="button" @click="submit()" :disabled="cartCount === 0 || submitting"
                                                class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span x-show="!submitting">Submit Bulk Request</span>
                                            <span x-show="submitting">Submitting...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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

    @push('scripts')
    <script>
        // Odoo-style batch requisition workspace for the "Request Stock from
        // HQ" panel: click a product on the left to drop it into the cart
        // table on the right, type its quantity, then submit the whole
        // basket to BranchRequisitionController@store in one AJAX request.
        function requisitionWorkspace(products) {
            return {
                products: products,
                search: '',
                cart: {},
                notes: '',
                submitting: false,
                error: null,

                get filteredProducts() {
                    if (!this.search) {
                        return this.products;
                    }
                    const q = this.search.toLowerCase();
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(q) || (p.code || '').toLowerCase().includes(q)
                    );
                },

                get cartItems() {
                    return Object.values(this.cart);
                },

                get cartCount() {
                    return this.cartItems.length;
                },

                addProduct(product) {
                    if (!this.cart[product.id]) {
                        this.cart[product.id] = { id: product.id, name: product.name, code: product.code, quantity: 1 };
                    }
                    this.$nextTick(() => {
                        const input = document.getElementById('qty-' + product.id);
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    });
                },

                removeProduct(id) {
                    delete this.cart[id];
                },

                async submit() {
                    if (this.cartCount === 0 || this.submitting) {
                        return;
                    }

                    this.submitting = true;
                    this.error = null;

                    try {
                        const response = await fetch('{{ route('branch-requisitions.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                items: this.cartItems.map(item => ({
                                    product_id: item.id,
                                    requested_quantity: item.quantity,
                                })),
                                notes: this.notes,
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            this.error = data.message || 'Failed to submit the requisition.';
                            this.submitting = false;
                            return;
                        }

                        window.location.reload();
                    } catch (e) {
                        this.error = 'Network error while submitting the requisition. Please try again.';
                        this.submitting = false;
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>

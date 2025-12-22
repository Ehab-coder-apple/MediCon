<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="relative p-6 lg:p-8 border-b border-slate-200 overflow-hidden" style="background-image: linear-gradient(to right, rgba(15,23,42,0.95), rgba(15,23,42,0.85)), url('{{ asset('images/dashboard-welcome-bg.jpg') }}'); background-size: cover; background-position: center;">
                    <div class="relative">
                        <h1 class="mt-8 text-3xl font-semibold text-white">
                            Welcome to MediCon Admin Dashboard!
                        </h1>

                        <p class="mt-6 text-slate-100 leading-relaxed max-w-2xl">
                            As an administrator, you have full access to manage the system, users, and view comprehensive reports.
                        </p>
                    </div>
                </div>

                <div class="bg-slate-50 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 p-6 lg:p-8">
                    <!-- Total Users -->
                    <div class="p-6 rounded-lg shadow-md text-white" style="background-color: #718EA3;">
                        <div class="flex items-center">
                            <div class="text-3xl font-extrabold tracking-tight">{{ $totalUsers }}</div>
                        </div>
                        <div class="mt-2 text-sm md:text-base text-white font-semibold">Total Users</div>
                    </div>

                    <!-- Admin Users -->
                    <div class="p-6 rounded-lg shadow-md text-white" style="background-color: #717F84;">
                        <div class="flex items-center">
                            <div class="text-3xl font-extrabold tracking-tight">{{ $adminUsers }}</div>
                        </div>
                        <div class="mt-2 text-sm md:text-base text-white font-semibold">Admin Users</div>
                    </div>

                    <!-- Pharmacists -->
                    <div class="p-6 rounded-lg shadow-md text-white" style="background-color: #18608E;">
                        <div class="flex items-center">
                            <div class="text-3xl font-extrabold tracking-tight">{{ $pharmacistUsers }}</div>
                        </div>
                        <div class="mt-2 text-sm md:text-base text-white font-semibold">Pharmacists</div>
                    </div>

                    <!-- Sales Staff -->
                    <div class="p-6 rounded-lg shadow-md text-white" style="background-color: #0B4875;">
                        <div class="flex items-center">
                            <div class="text-3xl font-extrabold tracking-tight">{{ $salesStaffUsers }}</div>
                        </div>
                        <div class="mt-2 text-sm md:text-base text-white font-semibold">Sales Staff</div>
                    </div>
                </div>

                <!-- Inventory Metrics Section -->
                <div class="bg-slate-50 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 p-6 lg:p-8 border-t border-slate-200">
                    <!-- Total Expired Products -->
                    <div class="p-6 rounded-lg shadow-md border-l-4 border-blue-600 bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-3xl font-extrabold tracking-tight text-red-600">{{ $totalExpiredProducts }}</div>
                                <div class="mt-2 text-sm md:text-base text-slate-600 font-semibold">Expired Products</div>
                            </div>
                            <div class="text-4xl">⚠️</div>
                        </div>
                    </div>

                    <!-- Total Nearly Expired Products -->
                    <div class="p-6 rounded-lg shadow-md border-l-4 border-blue-600 bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-3xl font-extrabold tracking-tight text-amber-600">{{ $totalNearlyExpiredProducts }}</div>
                                <div class="mt-2 text-sm md:text-base text-slate-600 font-semibold">Nearly Expired</div>
                            </div>
                            <div class="text-4xl">⏰</div>
                        </div>
                    </div>

                    <!-- Low Stock Products -->
                    <div class="p-6 rounded-lg shadow-md border-l-4 border-blue-600 bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-3xl font-extrabold tracking-tight text-orange-600">{{ $lowStockProducts }}</div>
                                <div class="mt-2 text-sm md:text-base text-slate-600 font-semibold">Low Stock</div>
                            </div>
                            <div class="text-4xl">📉</div>
                        </div>
                    </div>

                    <!-- Out of Stock Products -->
                    <div class="p-6 rounded-lg shadow-md border-l-4 border-blue-600 bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-3xl font-extrabold tracking-tight text-red-700">{{ $outOfStockProducts }}</div>
                                <div class="mt-2 text-sm md:text-base text-slate-600 font-semibold">Out of Stock</div>
                            </div>
                            <div class="text-4xl">🚫</div>
                        </div>
                    </div>
                </div>

                <div class="p-6 lg:p-8">


                    <!-- WhatsApp Messaging Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">💬 WhatsApp Messaging</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <a href="{{ route('whatsapp.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-5 px-6 rounded-xl text-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 hover:scale-105 transition-all duration-200">
                                <i class="fab fa-whatsapp text-2xl mb-2 block"></i>
                                <div class="text-base font-semibold">WhatsApp Dashboard</div>
                                <div class="text-sm text-emerald-50 mt-1">Manage messages & templates</div>
                            </a>
                            <a href="{{ route('whatsapp.create') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white font-semibold py-5 px-6 rounded-xl text-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 hover:scale-105 transition-all duration-200">
                                <i class="fas fa-paper-plane text-2xl mb-2 block"></i>
                                <div class="text-base font-semibold">Send Message</div>
                                <div class="text-sm text-cyan-50 mt-1">Individual customer message</div>
                            </a>
                            <a href="{{ route('whatsapp.bulk') }}" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold py-5 px-6 rounded-xl text-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 hover:scale-105 transition-all duration-200">
                                <i class="fas fa-broadcast-tower text-2xl mb-2 block"></i>
                                <div class="text-base font-semibold">Bulk Message</div>
                                <div class="text-sm text-teal-50 mt-1">Send to multiple customers</div>
                            </a>
                        </div>
                    </div>

                    <!-- Invoicing & Sales Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">💰 Invoicing & Sales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <a href="{{ route('invoices.create') }}" class="bg-slate-700 hover:bg-slate-800 text-white font-semibold py-5 px-6 rounded-xl text-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 hover:scale-105 transition-all duration-200">
                                <i class="fas fa-cash-register text-2xl mb-2 block"></i>
                                <div class="text-base font-semibold">Point of Sale</div>
                                <div class="text-sm text-slate-100 mt-1">Create new invoice/sale</div>
                            </a>
                            <a href="{{ route('invoices.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-5 px-6 rounded-xl text-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 hover:scale-105 transition-all duration-200">
                                <i class="fas fa-file-invoice text-2xl mb-2 block"></i>
                                <div class="text-base font-semibold">Invoice Management</div>
                                <div class="text-sm text-emerald-50 mt-1">View & manage invoices</div>
                            </a>
                            <a href="{{ route('admin.sales.index') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white font-semibold py-5 px-6 rounded-xl text-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 hover:scale-105 transition-all duration-200">
                                <i class="fas fa-chart-line text-2xl mb-2 block"></i>
                                <div class="text-base font-semibold">Sales Reports</div>
                                <div class="text-sm text-cyan-50 mt-1">View sales analytics</div>
                            </a>
                        </div>
                    </div>

                    <!-- Admin Management Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">System Management</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <a href="{{ route('admin.users') }}" class="bg-slate-700 hover:bg-slate-800 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">Manage Users</div>
                            </a>
                            <a href="{{ route('admin.categories.index') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">Manage Categories</div>
                            </a>
                            <a href="{{ route('admin.subcategories.index') }}" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">Manage Subcategories</div>
                            </a>
                            <a href="{{ route('admin.locations.index') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">📍 Manage Locations</div>
                            </a>
                            <a href="{{ route('admin.warehouses.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">🏭 Manage Warehouses</div>
                            </a>
                            <a href="{{ route('admin.stock-transfers.index') }}" class="bg-slate-600 hover:bg-slate-700 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">🔀 Stock Transfers</div>
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">View Reports</div>
                            </a>
                            <a href="{{ route('admin.settings.index') }}" class="bg-slate-700 hover:bg-slate-800 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">System Settings</div>
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-5 px-6 rounded-xl text-center shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 transition-all duration-200">
                                <div class="text-lg md:text-xl font-extrabold">Manage Products</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

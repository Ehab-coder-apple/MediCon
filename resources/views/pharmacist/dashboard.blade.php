<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pharmacist Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-white rounded">
                                    <span class="text-sm text-gray-600">Invoice #001 created</span>
                                    <span class="text-xs text-gray-400">2 hours ago</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-white rounded">
                                    <span class="text-sm text-gray-600">Inventory updated for Aspirin</span>
                                    <span class="text-xs text-gray-400">4 hours ago</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-white rounded">
                                    <span class="text-sm text-gray-600">Low stock alert: Paracetamol</span>
                                    <span class="text-xs text-gray-400">6 hours ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

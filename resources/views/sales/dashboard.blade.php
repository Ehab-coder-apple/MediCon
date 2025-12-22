<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sales Staff Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="mt-8 text-2xl font-medium text-gray-900">
                        Welcome to MediCon Sales Dashboard!
                    </h1>

                    <p class="mt-6 text-gray-500 leading-relaxed">
                        Manage sales transactions, process orders, and track your daily performance.
                    </p>
                </div>

                <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 p-6 lg:p-8">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-green-600">${{ number_format($salesToday, 2) }}</div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">Sales Today</div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $ordersToday }}</div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">Orders Today</div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $pendingOrders }}</div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">Pending Orders</div>
                    </div>
                </div>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <button class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    New Sale
                                </button>
                                <a href="{{ route('sales.orders') }}" class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                                    View Orders
                                </a>
                                <a href="{{ route('sales.sales') }}" class="block w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                                    Sales History
                                </a>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Transactions</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-white rounded">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Sale #001</span>
                                        <div class="text-xs text-gray-500">Paracetamol, Aspirin</div>
                                    </div>
                                    <span class="text-sm font-bold text-green-600">$25.50</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-white rounded">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Sale #002</span>
                                        <div class="text-xs text-gray-500">Cough Syrup</div>
                                    </div>
                                    <span class="text-sm font-bold text-green-600">$12.00</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-white rounded">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Sale #003</span>
                                        <div class="text-xs text-gray-500">Vitamins</div>
                                    </div>
                                    <span class="text-sm font-bold text-green-600">$35.75</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

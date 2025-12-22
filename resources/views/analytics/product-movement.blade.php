<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Product Movement Analytics') }}
            </h2>
            <div class="space-x-2">
                <select id="periodSelect" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="180">Last 6 months</option>
                </select>
                <a href="{{ route('admin.analytics.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <p class="mt-2 text-gray-600">Loading analytics data...</p>
            </div>

            <!-- Analytics Content -->
            <div id="analyticsContent" class="hidden">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Fast-Moving Products</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="fastMovingCount">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Slow-Moving Products</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="slowMovingCount">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">No Movement</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="noMovementCount">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Fast-Moving Products Chart -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Fast-Moving Products</h2>
                            <p class="mt-2 text-gray-500">Top 10 products by sales volume</p>
                        </div>
                        <div class="p-6 lg:p-8">
                            <canvas id="fastMovingChart" width="400" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Slow-Moving Products Chart -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Slow-Moving Products</h2>
                            <p class="mt-2 text-gray-500">Bottom 10 products by sales volume</p>
                        </div>
                        <div class="p-6 lg:p-8">
                            <canvas id="slowMovingChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Fast-Moving Products Table -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Fast-Moving Products</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sold</th>
                                    </tr>
                                </thead>
                                <tbody id="fastMovingTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Slow-Moving Products Table -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Slow-Moving Products</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sold</th>
                                    </tr>
                                </thead>
                                <tbody id="slowMovingTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- No Movement Products Table -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">No Movement</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                    </tr>
                                </thead>
                                <tbody id="noMovementTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        let fastMovingChart, slowMovingChart;

        document.addEventListener('DOMContentLoaded', function() {
            loadProductMovementData();
            
            document.getElementById('periodSelect').addEventListener('change', function() {
                loadProductMovementData();
            });
        });

        async function loadProductMovementData() {
            const days = document.getElementById('periodSelect').value;
            
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('analyticsContent').classList.add('hidden');
            
            try {
                const response = await fetch(`/admin/analytics/product-movement/data?days=${days}`);
                const data = await response.json();
                
                updateSummaryCards(data);
                updateCharts(data);
                updateTables(data);
                
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('analyticsContent').classList.remove('hidden');
            } catch (error) {
                console.error('Error loading product movement data:', error);
                document.getElementById('loadingIndicator').innerHTML = '<p class="text-red-600">Error loading data. Please try again.</p>';
            }
        }

        function updateSummaryCards(data) {
            document.getElementById('fastMovingCount').textContent = data.fast_moving.length;
            document.getElementById('slowMovingCount').textContent = data.slow_moving.length;
            document.getElementById('noMovementCount').textContent = data.no_movement.length;
        }

        function updateCharts(data) {
            // Destroy existing charts
            if (fastMovingChart) fastMovingChart.destroy();
            if (slowMovingChart) slowMovingChart.destroy();
            
            // Fast-moving products chart
            const fastCtx = document.getElementById('fastMovingChart').getContext('2d');
            fastMovingChart = new Chart(fastCtx, {
                type: 'bar',
                data: {
                    labels: data.fast_moving.map(p => p.name.length > 15 ? p.name.substring(0, 15) + '...' : p.name),
                    datasets: [{
                        label: 'Units Sold',
                        data: data.fast_moving.map(p => p.total_sold),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Slow-moving products chart
            const slowCtx = document.getElementById('slowMovingChart').getContext('2d');
            slowMovingChart = new Chart(slowCtx, {
                type: 'bar',
                data: {
                    labels: data.slow_moving.map(p => p.name.length > 15 ? p.name.substring(0, 15) + '...' : p.name),
                    datasets: [{
                        label: 'Units Sold',
                        data: data.slow_moving.map(p => p.total_sold),
                        backgroundColor: 'rgba(251, 191, 36, 0.8)',
                        borderColor: 'rgba(251, 191, 36, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function updateTables(data) {
            // Fast-moving products table
            const fastTable = document.getElementById('fastMovingTable');
            fastTable.innerHTML = data.fast_moving.map(product => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${product.name}</div>
                        <div class="text-sm text-gray-500">${product.code}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            ${product.total_sold} units
                        </span>
                    </td>
                </tr>
            `).join('');
            
            // Slow-moving products table
            const slowTable = document.getElementById('slowMovingTable');
            slowTable.innerHTML = data.slow_moving.map(product => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${product.name}</div>
                        <div class="text-sm text-gray-500">${product.code}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            ${product.total_sold} units
                        </span>
                    </td>
                </tr>
            `).join('');
            
            // No movement products table
            const noMovementTable = document.getElementById('noMovementTable');
            noMovementTable.innerHTML = data.no_movement.map(product => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${product.name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">${product.code}</div>
                    </td>
                </tr>
            `).join('');
        }
    </script>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Supplier Analytics') }}
            </h2>
            <div class="space-x-2">
                <select id="periodSelect" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="30">Last 30 days</option>
                    <option value="90" selected>Last 90 days</option>
                    <option value="180">Last 6 months</option>
                    <option value="365">Last year</option>
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
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                <p class="mt-2 text-gray-600">Loading supplier analytics...</p>
            </div>

            <!-- Analytics Content -->
            <div id="analyticsContent" class="hidden">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Active Suppliers</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="activeSuppliers">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Purchase Value</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="totalPurchaseValue">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="totalOrders">-</dd>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Avg Order Value</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="avgOrderValue">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Top Suppliers by Volume Chart -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Top Suppliers by Volume</h2>
                            <p class="mt-2 text-gray-500">Purchase volume ranking</p>
                        </div>
                        <div class="p-6 lg:p-8">
                            <canvas id="topSuppliersChart" width="400" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Supplier Performance Chart -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Supplier Performance</h2>
                            <p class="mt-2 text-gray-500">Order completion rates</p>
                        </div>
                        <div class="p-6 lg:p-8">
                            <canvas id="performanceChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Suppliers Table -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Top Suppliers by Volume</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                    </tr>
                                </thead>
                                <tbody id="topSuppliersTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Supplier Performance Table -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Supplier Performance</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Order</th>
                                    </tr>
                                </thead>
                                <tbody id="performanceTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Purchase Trends Chart -->
                <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <h2 class="text-xl font-medium text-gray-900">Purchase Trends by Supplier</h2>
                        <p class="mt-2 text-gray-500">Monthly purchase trends for top suppliers</p>
                    </div>
                    <div class="p-6 lg:p-8">
                        <canvas id="trendsChart" width="800" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        let topSuppliersChart, performanceChart, trendsChart;

        document.addEventListener('DOMContentLoaded', function() {
            loadSupplierAnalytics();
            
            document.getElementById('periodSelect').addEventListener('change', function() {
                loadSupplierAnalytics();
            });
        });

        async function loadSupplierAnalytics() {
            const days = document.getElementById('periodSelect').value;
            
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('analyticsContent').classList.add('hidden');
            
            try {
                const response = await fetch(`/admin/analytics/suppliers/data?days=${days}`);
                const data = await response.json();
                
                updateSummaryCards(data);
                updateCharts(data);
                updateTables(data);
                
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('analyticsContent').classList.remove('hidden');
            } catch (error) {
                console.error('Error loading supplier analytics:', error);
                document.getElementById('loadingIndicator').innerHTML = '<p class="text-red-600">Error loading analytics. Please try again.</p>';
            }
        }

        function updateSummaryCards(data) {
            const totalValue = data.top_suppliers.reduce((sum, supplier) => sum + parseFloat(supplier.total_volume), 0);
            const totalOrders = data.top_suppliers.reduce((sum, supplier) => sum + parseInt(supplier.purchase_count), 0);
            const avgOrderValue = totalOrders > 0 ? totalValue / totalOrders : 0;
            
            document.getElementById('activeSuppliers').textContent = data.top_suppliers.length;
            document.getElementById('totalPurchaseValue').textContent = '$' + totalValue.toLocaleString();
            document.getElementById('totalOrders').textContent = totalOrders.toLocaleString();
            document.getElementById('avgOrderValue').textContent = '$' + avgOrderValue.toLocaleString();
        }

        function updateCharts(data) {
            // Destroy existing charts
            if (topSuppliersChart) topSuppliersChart.destroy();
            if (performanceChart) performanceChart.destroy();
            if (trendsChart) trendsChart.destroy();
            
            // Top suppliers chart
            const topCtx = document.getElementById('topSuppliersChart').getContext('2d');
            topSuppliersChart = new Chart(topCtx, {
                type: 'bar',
                data: {
                    labels: data.top_suppliers.map(s => s.name.length > 15 ? s.name.substring(0, 15) + '...' : s.name),
                    datasets: [{
                        label: 'Purchase Volume',
                        data: data.top_suppliers.map(s => parseFloat(s.total_volume)),
                        backgroundColor: 'rgba(147, 51, 234, 0.8)',
                        borderColor: 'rgba(147, 51, 234, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Performance chart
            const perfCtx = document.getElementById('performanceChart').getContext('2d');
            performanceChart = new Chart(perfCtx, {
                type: 'bar',
                data: {
                    labels: data.supplier_performance.map(s => s.name.length > 15 ? s.name.substring(0, 15) + '...' : s.name),
                    datasets: [{
                        label: 'Completion Rate (%)',
                        data: data.supplier_performance.map(s => parseFloat(s.completion_rate)),
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
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Purchase trends chart
            const trendsCtx = document.getElementById('trendsChart').getContext('2d');
            const suppliers = Object.keys(data.purchase_trends);
            const colors = [
                'rgba(147, 51, 234, 1)',
                'rgba(34, 197, 94, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(251, 191, 36, 1)',
                'rgba(239, 68, 68, 1)'
            ];
            
            const datasets = suppliers.slice(0, 5).map((supplier, index) => {
                const supplierData = data.purchase_trends[supplier];
                return {
                    label: supplier,
                    data: supplierData.map(d => parseFloat(d.monthly_total)),
                    borderColor: colors[index],
                    backgroundColor: colors[index].replace('1)', '0.1)'),
                    tension: 0.4,
                    fill: false
                };
            });
            
            // Get all unique months for labels
            const allMonths = new Set();
            suppliers.forEach(supplier => {
                data.purchase_trends[supplier].forEach(d => {
                    allMonths.add(`${d.year}-${String(d.month).padStart(2, '0')}`);
                });
            });
            const sortedMonths = Array.from(allMonths).sort();
            
            trendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: sortedMonths.map(month => {
                        const [year, monthNum] = month.split('-');
                        return new Date(year, monthNum - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    }),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function updateTables(data) {
            // Top suppliers table
            const topTable = document.getElementById('topSuppliersTable');
            topTable.innerHTML = data.top_suppliers.map(supplier => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${supplier.name}</div>
                        <div class="text-sm text-gray-500">${supplier.contact_person || 'No contact'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-green-600">$${parseFloat(supplier.total_volume).toLocaleString()}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            ${supplier.purchase_count} orders
                        </span>
                    </td>
                </tr>
            `).join('');
            
            // Performance table
            const perfTable = document.getElementById('performanceTable');
            perfTable.innerHTML = data.supplier_performance.map(supplier => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${supplier.name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: ${supplier.completion_rate}%"></div>
                            </div>
                            <span class="text-sm text-gray-900">${parseFloat(supplier.completion_rate).toFixed(1)}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        $${parseFloat(supplier.avg_order_value).toLocaleString()}
                    </td>
                </tr>
            `).join('');
        }
    </script>
</x-app-layout>

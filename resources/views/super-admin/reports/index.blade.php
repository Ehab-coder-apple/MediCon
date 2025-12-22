<x-superadmin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Business Reports & Analytics') }}
            </h2>
            <div class="flex space-x-2">
                <button onclick="exportReports()" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"/>
                    </svg>
                    Export Reports
                </button>
                <button onclick="refreshData()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                    Refresh Data
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Company Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Revenue -->
                <div class="metric-card bg-gradient-to-r from-green-400 to-green-600 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-white">Total Revenue</div>
                                <div class="text-2xl font-bold text-white">${{ number_format($companyMetrics['total_revenue'], 2) }}</div>
                                <div class="text-sm text-white opacity-90">
                                    @if($companyMetrics['revenue_growth'] > 0)
                                        ↗ +{{ number_format($companyMetrics['revenue_growth'], 1) }}% this month
                                    @elseif($companyMetrics['revenue_growth'] < 0)
                                        ↘ {{ number_format($companyMetrics['revenue_growth'], 1) }}% this month
                                    @else
                                        → No change this month
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Tenants -->
                <div class="metric-card bg-gradient-to-r from-blue-400 to-blue-600 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-white">Total Tenants</div>
                                <div class="text-2xl font-bold text-white">{{ number_format($companyMetrics['total_tenants']) }}</div>
                                <div class="text-sm text-white opacity-90">{{ $companyMetrics['active_tenants'] }} active</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="metric-card bg-gradient-to-r from-purple-400 to-purple-600 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM6 9.5a.5.5 0 01.5-.5h7a.5.5 0 010 1h-7a.5.5 0 01-.5-.5zm.5 2.5a.5.5 0 000 1h7a.5.5 0 000-1h-7z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-white">Total Sales</div>
                                <div class="text-2xl font-bold text-white">${{ number_format($tenantMetrics['tenant_sales_summary']['total_sales_value'], 2) }}</div>
                                <div class="text-sm text-white opacity-90">{{ number_format($tenantMetrics['tenant_sales_summary']['total_sales_count']) }} transactions</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platform Users -->
                <div class="metric-card bg-gradient-to-r from-yellow-400 to-yellow-600 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-white">Platform Users</div>
                                <div class="text-2xl font-bold text-white">{{ number_format($companyMetrics['total_users']) }}</div>
                                <div class="text-sm text-white opacity-90">{{ $companyMetrics['active_users'] }} active</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Trends Chart -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue Trends</h3>
                        <div class="h-64">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Category Performance Chart -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales by Category</h3>
                        <div class="h-64">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performing Tenants -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Top Performing Tenants</h3>
                        <button onclick="viewAllTenants()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View All →
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Sale</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($tenantMetrics['top_performing_tenants'] as $tenant)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $tenant->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $tenant->pharmacy_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($tenant->total_sales) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($tenant->total_revenue, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($tenant->avg_sale_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewTenantDetails({{ $tenant->id }})" class="text-blue-600 hover:text-blue-900">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Most Sold Products -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Most Sold Products Across Platform</h3>
                    <div class="overflow-x-auto">
                        <table class="data-table min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(array_slice($tenantMetrics['most_sold_products'], 0, 10) as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $product->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->tenant_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($product->total_sold) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($product->total_revenue, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Platform Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Inventory Overview -->
                <div class="stats-card bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Inventory</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">Total Products</span>
                                <span class="stat-value text-sm font-medium">{{ number_format($tenantMetrics['inventory_insights']['total_products']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">Low Stock Items</span>
                                <span class="text-sm font-medium text-yellow-600">{{ number_format($tenantMetrics['inventory_insights']['low_stock_products']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">Out of Stock</span>
                                <span class="text-sm font-medium text-red-600">{{ number_format($tenantMetrics['inventory_insights']['out_of_stock_products']) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-3">
                                <span class="stat-value text-sm font-medium">Total Value</span>
                                <span class="stat-value text-sm font-bold">${{ number_format($tenantMetrics['inventory_insights']['total_inventory_value'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Metrics -->
                <div class="stats-card bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Metrics</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">Total Customers</span>
                                <span class="stat-value text-sm font-medium">{{ number_format($tenantMetrics['total_customers_across_tenants']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">New This Month</span>
                                <span class="text-sm font-medium text-green-600">{{ number_format($tenantMetrics['customer_growth']['new_customers_this_month']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">Growth Rate</span>
                                <span class="text-sm font-medium {{ $tenantMetrics['customer_growth']['customer_growth_rate'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($tenantMetrics['customer_growth']['customer_growth_rate'], 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platform Health -->
                <div class="stats-card bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Health</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">Utilization Rate</span>
                                <span class="stat-value text-sm font-medium">{{ number_format($companyMetrics['platform_utilization'], 1) }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">New Tenants (Month)</span>
                                <span class="text-sm font-medium text-blue-600">{{ $companyMetrics['new_tenants_this_month'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="stat-label text-sm">New Users (Month)</span>
                                <span class="text-sm font-medium text-purple-600">{{ $companyMetrics['new_users_this_month'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenant Details Modal -->
    <div id="tenantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Tenant Details</h3>
                    <button onclick="closeTenantModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Ensure all text has proper contrast */
        .metric-card {
            background: linear-gradient(135deg, var(--tw-gradient-from), var(--tw-gradient-to));
            color: white !important;
        }

        .metric-card * {
            color: white !important;
        }

        .data-table th {
            background-color: #f9fafb !important;
            color: #374151 !important;
            font-weight: 600;
        }

        .data-table td {
            color: #1f2937 !important;
        }

        .stats-card {
            background-color: white;
            border: 1px solid #e5e7eb;
        }

        .stats-card .stat-label {
            color: #6b7280 !important;
        }

        .stats-card .stat-value {
            color: #111827 !important;
            font-weight: 600;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        function initializeCharts() {
            // Revenue Trends Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const monthlyTrends = @json($tenantMetrics['monthly_trends']);
            
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: monthlyTrends.map(item => item.month),
                    datasets: [{
                        label: 'Revenue',
                        data: monthlyTrends.map(item => item.total_revenue),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4
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
                    }
                }
            });

            // Category Performance Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryData = @json($tenantMetrics['category_performance']);
            
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(item => item.category),
                    datasets: [{
                        data: categoryData.map(item => item.total_revenue),
                        backgroundColor: [
                            '#3B82F6', '#EF4444', '#10B981', '#F59E0B',
                            '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function viewTenantDetails(tenantId) {
            // Show modal and load tenant details
            document.getElementById('tenantModal').classList.remove('hidden');
            document.getElementById('modalContent').innerHTML = '<div class="text-center py-4">Loading...</div>';
            
            fetch(`/super-admin/reports/tenant-details?tenant_id=${tenantId}`)
                .then(response => response.json())
                .then(data => {
                    displayTenantDetails(data);
                })
                .catch(error => {
                    document.getElementById('modalContent').innerHTML = '<div class="text-red-600">Error loading data</div>';
                });
        }

        function displayTenantDetails(data) {
            const content = `
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900">${data.tenant_info.name}</h4>
                        <p class="text-sm text-gray-600">${data.tenant_info.pharmacy_name}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-3 rounded">
                            <div class="text-sm text-blue-600">Total Sales</div>
                            <div class="text-lg font-bold text-blue-900">$${data.sales_metrics.total_sales.toLocaleString()}</div>
                        </div>
                        <div class="bg-green-50 p-3 rounded">
                            <div class="text-sm text-green-600">Products</div>
                            <div class="text-lg font-bold text-green-900">${data.product_metrics.total_products}</div>
                        </div>
                        <div class="bg-purple-50 p-3 rounded">
                            <div class="text-sm text-purple-600">Customers</div>
                            <div class="text-lg font-bold text-purple-900">${data.customer_metrics.total_customers}</div>
                        </div>
                        <div class="bg-yellow-50 p-3 rounded">
                            <div class="text-sm text-yellow-600">Avg Sale</div>
                            <div class="text-lg font-bold text-yellow-900">$${data.sales_metrics.average_sale.toFixed(2)}</div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('modalContent').innerHTML = content;
        }

        function closeTenantModal() {
            document.getElementById('tenantModal').classList.add('hidden');
        }

        function exportReports() {
            // Implement export functionality
            alert('Export functionality will be implemented');
        }

        function refreshData() {
            location.reload();
        }

        function viewAllTenants() {
            window.location.href = '/super-admin/tenants';
        }
    </script>
</x-superadmin-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sales Analytics') }}
            </h2>
            <div class="space-x-2">
                <select id="periodSelect" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="day">Daily (Last 7 days)</option>
                    <option value="week">Weekly (Last 12 weeks)</option>
                    <option value="month" selected>Monthly (Last 12 months)</option>
                </select>
                @php
                    $routePrefix = auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isPharmacist() ? 'pharmacist' : 'sales-staff');
                @endphp
                <a href="{{ route($routePrefix . '.analytics.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-600">Loading sales analytics...</p>
            </div>

            <!-- Analytics Content -->
            <div id="analyticsContent" class="hidden">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="totalRevenue">-</dd>
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
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Sales</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="totalSales">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Average Sale</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="averageSale">-</dd>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Period</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="periodInfo">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Sales Trend Chart -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Sales Trend</h2>
                            <p class="mt-2 text-gray-500" id="trendDescription">Revenue over time</p>
                        </div>
                        <div class="p-6 lg:p-8">
                            <canvas id="salesTrendChart" width="400" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Sales Volume Chart -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Sales Volume</h2>
                            <p class="mt-2 text-gray-500" id="volumeDescription">Number of transactions</p>
                        </div>
                        <div class="p-6 lg:p-8">
                            <canvas id="salesVolumeChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Analytics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Daily Sales -->
                    <div id="dailySection" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Daily Sales</h2>
                        </div>
                        <div class="overflow-x-auto max-h-96">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                    </tr>
                                </thead>
                                <tbody id="dailyTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Weekly Sales -->
                    <div id="weeklySection" class="bg-white overflow-hidden shadow-xl sm:rounded-lg hidden">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Weekly Sales</h2>
                        </div>
                        <div class="overflow-x-auto max-h-96">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Week</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                    </tr>
                                </thead>
                                <tbody id="weeklyTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Monthly Sales -->
                    <div id="monthlySection" class="bg-white overflow-hidden shadow-xl sm:rounded-lg hidden">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Monthly Sales</h2>
                        </div>
                        <div class="overflow-x-auto max-h-96">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                    </tr>
                                </thead>
                                <tbody id="monthlyTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Performance Insights -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-medium text-gray-900">Performance Insights</h2>
                        </div>
                        <div class="p-6 lg:p-8">
                            <div id="insights" class="space-y-4">
                                <!-- Insights will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        let trendChart, volumeChart;
        let currentData = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadSalesAnalytics();
            
            document.getElementById('periodSelect').addEventListener('change', function() {
                loadSalesAnalytics();
            });
        });

        async function loadSalesAnalytics() {
            const period = document.getElementById('periodSelect').value;
            
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('analyticsContent').classList.add('hidden');
            
            try {
                const routePrefix = '{{ auth()->user()->isAdmin() ? "admin" : (auth()->user()->isPharmacist() ? "pharmacist" : "sales-staff") }}';
                const response = await fetch(`/${routePrefix}/analytics/sales/data?period=${period}`);
                const data = await response.json();
                
                currentData = data;
                updateSummaryCards(data);
                updateCharts(data, period);
                updateTables(data, period);
                updateInsights(data, period);
                
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('analyticsContent').classList.remove('hidden');
            } catch (error) {
                console.error('Error loading sales analytics:', error);
                document.getElementById('loadingIndicator').innerHTML = '<p class="text-red-600">Error loading analytics. Please try again.</p>';
            }
        }

        function updateSummaryCards(data) {
            document.getElementById('totalRevenue').textContent = '$' + data.current_period.total.toLocaleString();
            document.getElementById('totalSales').textContent = data.current_period.count.toLocaleString();
            document.getElementById('averageSale').textContent = '$' + data.current_period.average.toLocaleString();
            document.getElementById('periodInfo').textContent = `${data.current_period.days} days`;
        }

        function updateCharts(data, period) {
            // Destroy existing charts
            if (trendChart) trendChart.destroy();
            if (volumeChart) volumeChart.destroy();
            
            let chartData, labels;
            
            switch(period) {
                case 'day':
                    chartData = data.daily_sales;
                    labels = chartData.map(d => new Date(d.date).toLocaleDateString());
                    document.getElementById('trendDescription').textContent = 'Daily revenue trend';
                    document.getElementById('volumeDescription').textContent = 'Daily transaction count';
                    break;
                case 'week':
                    chartData = data.weekly_sales;
                    labels = chartData.map(d => d.week_start);
                    document.getElementById('trendDescription').textContent = 'Weekly revenue trend';
                    document.getElementById('volumeDescription').textContent = 'Weekly transaction count';
                    break;
                case 'month':
                    chartData = data.monthly_sales;
                    labels = chartData.map(d => d.month_name);
                    document.getElementById('trendDescription').textContent = 'Monthly revenue trend';
                    document.getElementById('volumeDescription').textContent = 'Monthly transaction count';
                    break;
            }
            
            // Sales trend chart
            const trendCtx = document.getElementById('salesTrendChart').getContext('2d');
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: chartData.map(d => parseFloat(d.total)),
                        borderColor: 'rgba(34, 197, 94, 1)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
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
            
            // Sales volume chart
            const volumeCtx = document.getElementById('salesVolumeChart').getContext('2d');
            volumeChart = new Chart(volumeCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Sales Count',
                        data: chartData.map(d => parseInt(d.count)),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
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
                                stepSize: 1
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
        }

        function updateTables(data, period) {
            // Hide all sections first
            document.getElementById('dailySection').classList.add('hidden');
            document.getElementById('weeklySection').classList.add('hidden');
            document.getElementById('monthlySection').classList.add('hidden');
            
            switch(period) {
                case 'day':
                    document.getElementById('dailySection').classList.remove('hidden');
                    updateTable('dailyTable', data.daily_sales, (item) => new Date(item.date).toLocaleDateString());
                    break;
                case 'week':
                    document.getElementById('weeklySection').classList.remove('hidden');
                    updateTable('weeklyTable', data.weekly_sales, (item) => item.week_start);
                    break;
                case 'month':
                    document.getElementById('monthlySection').classList.remove('hidden');
                    updateTable('monthlyTable', data.monthly_sales, (item) => item.month_name);
                    break;
            }
        }

        function updateTable(tableId, data, labelFormatter) {
            const table = document.getElementById(tableId);
            
            if (data.length === 0) {
                table.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                            No sales data available for this period.
                        </td>
                    </tr>
                `;
                return;
            }
            
            table.innerHTML = data.map(item => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${labelFormatter(item)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        $${parseFloat(item.total).toLocaleString()}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${parseInt(item.count)} sales
                    </td>
                </tr>
            `).join('');
        }

        function updateInsights(data, period) {
            const insights = document.getElementById('insights');
            const currentPeriod = data.current_period;
            
            let insightsList = [];
            
            // Revenue insights
            if (currentPeriod.total > 0) {
                insightsList.push({
                    icon: '💰',
                    title: 'Revenue Performance',
                    description: `Generated $${currentPeriod.total.toLocaleString()} in ${currentPeriod.days} days`
                });
            }
            
            // Average sale insights
            if (currentPeriod.average > 0) {
                insightsList.push({
                    icon: '📊',
                    title: 'Average Sale Value',
                    description: `$${currentPeriod.average.toLocaleString()} per transaction`
                });
            }
            
            // Volume insights
            if (currentPeriod.count > 0) {
                const dailyAverage = (currentPeriod.count / currentPeriod.days).toFixed(1);
                insightsList.push({
                    icon: '📈',
                    title: 'Sales Volume',
                    description: `${currentPeriod.count} total sales (${dailyAverage} per day)`
                });
            }
            
            // Period-specific insights
            switch(period) {
                case 'day':
                    insightsList.push({
                        icon: '🗓️',
                        title: 'Daily Trend',
                        description: 'Showing last 7 days of sales activity'
                    });
                    break;
                case 'week':
                    insightsList.push({
                        icon: '📅',
                        title: 'Weekly Trend',
                        description: 'Showing last 12 weeks of sales performance'
                    });
                    break;
                case 'month':
                    insightsList.push({
                        icon: '📆',
                        title: 'Monthly Trend',
                        description: 'Showing last 12 months of sales history'
                    });
                    break;
            }
            
            insights.innerHTML = insightsList.map(insight => `
                <div class="flex items-start space-x-3">
                    <div class="text-2xl">${insight.icon}</div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">${insight.title}</h4>
                        <p class="text-sm text-gray-500">${insight.description}</p>
                    </div>
                </div>
            `).join('');
        }
    </script>
</x-app-layout>

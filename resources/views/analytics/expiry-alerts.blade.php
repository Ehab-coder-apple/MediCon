<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Expiry Alerts') }}
            </h2>
            <div class="space-x-2">
                <select id="alertPeriod" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="7">Next 7 days</option>
                    <option value="30" selected>Next 30 days</option>
                    <option value="60">Next 60 days</option>
                    <option value="90">Next 90 days</option>
                </select>
                <a href="{{ auth()->user()->isAdmin() ? route('admin.analytics.dashboard') : route('pharmacist.analytics.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-600"></div>
                <p class="mt-2 text-gray-600">Loading expiry alerts...</p>
            </div>

            <!-- Alerts Content -->
            <div id="alertsContent" class="hidden">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Critical (≤7 days)</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="criticalCount">-</dd>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Warning (8-30 days)</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="warningCount">-</dd>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Notice (31+ days)</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="noticeCount">-</dd>
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
                                        <dt class="text-sm font-medium text-gray-500 truncate">Value at Risk</dt>
                                        <dd class="text-lg font-medium text-gray-900" id="valueAtRisk">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expiry Distribution Chart -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <h2 class="text-xl font-medium text-gray-900">Expiry Distribution</h2>
                        <p class="mt-2 text-gray-500">Products expiring by urgency level</p>
                    </div>
                    <div class="p-6 lg:p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <canvas id="expiryDistributionChart" width="400" height="300"></canvas>
                            </div>
                            <div>
                                <canvas id="expiryTimelineChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Alerts Tables -->
                <div class="space-y-6">
                    <!-- Critical Alerts -->
                    <div id="criticalSection" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-red-50 border-b border-red-200">
                            <h2 class="text-xl font-medium text-red-900">Critical Alerts (≤7 days)</h2>
                            <p class="mt-2 text-red-700">Products expiring within 7 days - immediate action required</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Left</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    </tr>
                                </thead>
                                <tbody id="criticalTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Warning Alerts -->
                    <div id="warningSection" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-yellow-50 border-b border-yellow-200">
                            <h2 class="text-xl font-medium text-yellow-900">Warning Alerts (8-30 days)</h2>
                            <p class="mt-2 text-yellow-700">Products expiring within 30 days - plan for clearance</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Left</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    </tr>
                                </thead>
                                <tbody id="warningTable" class="bg-white divide-y divide-gray-200">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notice Alerts -->
                    <div id="noticeSection" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 lg:p-8 bg-blue-50 border-b border-blue-200">
                            <h2 class="text-xl font-medium text-blue-900">Notice Alerts (31+ days)</h2>
                            <p class="mt-2 text-blue-700">Products expiring in extended period - monitor closely</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Left</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    </tr>
                                </thead>
                                <tbody id="noticeTable" class="bg-white divide-y divide-gray-200">
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
        let distributionChart, timelineChart;

        document.addEventListener('DOMContentLoaded', function() {
            loadExpiryAlerts();
            
            document.getElementById('alertPeriod').addEventListener('change', function() {
                loadExpiryAlerts();
            });
        });

        async function loadExpiryAlerts() {
            const days = document.getElementById('alertPeriod').value;
            
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('alertsContent').classList.add('hidden');
            
            try {
                const routePrefix = '{{ auth()->user()->isAdmin() ? "admin" : "pharmacist" }}';
                const response = await fetch(`/${routePrefix}/analytics/expiry-alerts/data?days=${days}`);
                const data = await response.json();
                
                updateSummaryCards(data);
                updateCharts(data);
                updateTables(data);
                
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('alertsContent').classList.remove('hidden');
            } catch (error) {
                console.error('Error loading expiry alerts:', error);
                document.getElementById('loadingIndicator').innerHTML = '<p class="text-red-600">Error loading alerts. Please try again.</p>';
            }
        }

        function updateSummaryCards(data) {
            document.getElementById('criticalCount').textContent = data.critical.length;
            document.getElementById('warningCount').textContent = data.warning.length;
            document.getElementById('noticeCount').textContent = data.notice.length;
            document.getElementById('valueAtRisk').textContent = '$' + data.total_value_at_risk.toLocaleString();
        }

        function updateCharts(data) {
            // Destroy existing charts
            if (distributionChart) distributionChart.destroy();
            if (timelineChart) timelineChart.destroy();
            
            // Distribution pie chart
            const distCtx = document.getElementById('expiryDistributionChart').getContext('2d');
            distributionChart = new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Critical (≤7 days)', 'Warning (8-30 days)', 'Notice (31+ days)'],
                    datasets: [{
                        data: [data.critical.length, data.warning.length, data.notice.length],
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(59, 130, 246, 0.8)'
                        ],
                        borderColor: [
                            'rgba(239, 68, 68, 1)',
                            'rgba(251, 191, 36, 1)',
                            'rgba(59, 130, 246, 1)'
                        ],
                        borderWidth: 2
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
            
            // Timeline chart showing expiry dates
            const allBatches = [...data.critical, ...data.warning, ...data.notice];
            const timelineData = allBatches.reduce((acc, batch) => {
                const date = batch.expiry_date;
                acc[date] = (acc[date] || 0) + 1;
                return acc;
            }, {});
            
            const timelineCtx = document.getElementById('expiryTimelineChart').getContext('2d');
            timelineChart = new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(timelineData).sort(),
                    datasets: [{
                        label: 'Batches Expiring',
                        data: Object.keys(timelineData).sort().map(date => timelineData[date]),
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
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
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        function updateTables(data) {
            updateTable('criticalTable', data.critical, 'critical');
            updateTable('warningTable', data.warning, 'warning');
            updateTable('noticeTable', data.notice, 'notice');
        }

        function updateTable(tableId, batches, urgency) {
            const table = document.getElementById(tableId);
            const urgencyColors = {
                critical: 'bg-red-100 text-red-800',
                warning: 'bg-yellow-100 text-yellow-800',
                notice: 'bg-blue-100 text-blue-800'
            };
            
            if (batches.length === 0) {
                table.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No ${urgency} alerts found.
                        </td>
                    </tr>
                `;
                return;
            }
            
            table.innerHTML = batches.map(batch => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${batch.product_name}</div>
                        <div class="text-sm text-gray-500">${batch.product_code}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${batch.batch_number}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${batch.expiry_date_formatted}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${urgencyColors[urgency]}">
                            ${batch.days_to_expiry} days
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${batch.quantity} units
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        $${batch.cost_value.toLocaleString()}
                    </td>
                </tr>
            `).join('');
        }
    </script>
</x-app-layout>

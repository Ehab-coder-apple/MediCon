<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('System Settings') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('admin.settings.logs') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    View Logs
                </a>
                <form method="POST" action="{{ route('admin.settings.backup') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create Backup
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- System Information (Super Admin Only) -->
            @if(auth()->user()->is_super_admin)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-500">PHP Version</div>
                            <div class="text-lg font-bold text-gray-900">{{ $systemInfo['php_version'] }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-500">Laravel Version</div>
                            <div class="text-lg font-bold text-gray-900">{{ $systemInfo['laravel_version'] }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-500">Environment</div>
                            <div class="text-lg font-bold text-gray-900">{{ ucfirst($systemInfo['app_env']) }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-500">Database</div>
                            <div class="text-lg font-bold text-gray-900">{{ ucfirst($systemInfo['database_type']) }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-500">Cache Driver</div>
                            <div class="text-lg font-bold text-gray-900">{{ ucfirst($systemInfo['cache_driver']) }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-500">Debug Mode</div>
                            <div class="text-lg font-bold text-gray-900">{{ $systemInfo['app_debug'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Information (Super Admin Only) -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Storage Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-blue-600">Total Space</div>
                            <div class="text-lg font-bold text-blue-900">{{ $storageInfo['total_space'] }}</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-green-600">Free Space</div>
                            <div class="text-lg font-bold text-green-900">{{ $storageInfo['free_space'] }}</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-yellow-600">Used Space</div>
                            <div class="text-lg font-bold text-yellow-900">{{ $storageInfo['used_space'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- System Actions -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">System Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <form method="POST" action="{{ route('admin.settings.clear-cache') }}">
                            @csrf
                            <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                                <div class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                    </svg>
                                    Clear Cache
                                </div>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.settings.optimize') }}">
                            @csrf
                            <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                                <div class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                    </svg>
                                    Optimize
                                </div>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.settings.test-email') }}">
                            @csrf
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                                <div class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                    </svg>
                                    Test Email
                                </div>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.settings.clear-logs') }}">
                            @csrf
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors" onclick="return confirm('Are you sure you want to clear all logs?')">
                                <div class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l1.5 1.5a1 1 0 01-1.414 1.414L10 10.414V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    Clear Logs
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                
                <!-- General Settings -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">General Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="app_name" class="block text-sm font-medium text-gray-700">Application Name</label>
                                <input type="text" name="settings[app_name]" id="app_name" value="{{ $settings['general']['app_name'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="pharmacy_name" class="block text-sm font-medium text-gray-700">Pharmacy Name</label>
                                <input type="text" name="settings[pharmacy_name]" id="pharmacy_name" value="{{ $settings['general']['pharmacy_name'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="pharmacy_phone" class="block text-sm font-medium text-gray-700">Pharmacy Phone</label>
                                <input type="text" name="settings[pharmacy_phone]" id="pharmacy_phone" value="{{ $settings['general']['pharmacy_phone'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="pharmacy_email" class="block text-sm font-medium text-gray-700">Pharmacy Email</label>
                                <input type="email" name="settings[pharmacy_email]" id="pharmacy_email" value="{{ $settings['general']['pharmacy_email'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label for="pharmacy_address" class="block text-sm font-medium text-gray-700">Pharmacy Address</label>
                                <textarea name="settings[pharmacy_address]" id="pharmacy_address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ $settings['general']['pharmacy_address'] }}</textarea>
                            </div>
                            <div>
                                <label for="currency_symbol" class="block text-sm font-medium text-gray-700">Currency Symbol</label>
                                <select name="settings[currency_symbol]" id="currency_symbol" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($currencies as $symbol => $label)
                                        <option value="{{ $symbol }}" {{ $settings['general']['currency_symbol'] === $symbol ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="tax_rate" class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                                <input type="number" step="0.01" name="settings[tax_rate]" id="tax_rate" value="{{ $settings['general']['tax_rate'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Settings -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="expiry_alert_days" class="block text-sm font-medium text-gray-700">Expiry Alert Days</label>
                                <input type="number" name="settings[expiry_alert_days]" id="expiry_alert_days" value="{{ $settings['inventory']['expiry_alert_days'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="default_alert_quantity" class="block text-sm font-medium text-gray-700">Default Alert Quantity</label>
                                <input type="number" name="settings[default_alert_quantity]" id="default_alert_quantity" value="{{ $settings['inventory']['default_alert_quantity'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="settings[low_stock_alert_enabled]" id="low_stock_alert_enabled" value="true" {{ $settings['inventory']['low_stock_alert_enabled'] === 'true' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="low_stock_alert_enabled" class="ml-2 block text-sm text-gray-900">Enable Low Stock Alerts</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="settings[batch_tracking_enabled]" id="batch_tracking_enabled" value="true" {{ $settings['inventory']['batch_tracking_enabled'] === 'true' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="batch_tracking_enabled" class="ml-2 block text-sm text-gray-900">Enable Batch Tracking</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Settings -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="invoice_prefix" class="block text-sm font-medium text-gray-700">Invoice Prefix</label>
                                <input type="text" name="settings[invoice_prefix]" id="invoice_prefix" value="{{ $settings['sales']['invoice_prefix'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="max_discount_percentage" class="block text-sm font-medium text-gray-700">Max Discount (%)</label>
                                <input type="number" name="settings[max_discount_percentage]" id="max_discount_percentage" value="{{ $settings['sales']['max_discount_percentage'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="settings[allow_negative_stock]" id="allow_negative_stock" value="true" {{ $settings['sales']['allow_negative_stock'] === 'true' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="allow_negative_stock" class="ml-2 block text-sm text-gray-900">Allow Negative Stock</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="settings[auto_print_receipt]" id="auto_print_receipt" value="true" {{ $settings['sales']['auto_print_receipt'] === 'true' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="auto_print_receipt" class="ml-2 block text-sm text-gray-900">Auto Print Receipt</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Security Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="session_timeout" class="block text-sm font-medium text-gray-700">Session Timeout (minutes)</label>
                                <input type="number" name="settings[session_timeout]" id="session_timeout" value="{{ $settings['security']['session_timeout'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="max_login_attempts" class="block text-sm font-medium text-gray-700">Max Login Attempts</label>
                                <input type="number" name="settings[max_login_attempts]" id="max_login_attempts" value="{{ $settings['security']['max_login_attempts'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

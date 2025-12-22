<x-superadmin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-2xl font-medium text-gray-900">System Settings</h1>
                            <p class="mt-2 text-sm text-gray-600">Manage platform-wide settings and configurations</p>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Platform Configuration -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- General Settings -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
                            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="section" value="general">
                                
                                <div>
                                    <label for="platform_name" class="block text-sm font-medium text-gray-700">Platform Name</label>
                                    <input type="text" name="platform_name" id="platform_name" value="MediCon Platform"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="support_email" class="block text-sm font-medium text-gray-700">Support Email</label>
                                    <input type="email" name="support_email" id="support_email" value="support@medicon.com"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="max_tenants" class="block text-sm font-medium text-gray-700">Maximum Tenants</label>
                                    <input type="number" name="max_tenants" id="max_tenants" value="1000" min="1"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="maintenance_mode" class="flex items-center">
                                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1"
                                               class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Enable Maintenance Mode</span>
                                    </label>
                                </div>

                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    Update General Settings
                                </button>
                            </form>
                        </div>

                        <!-- Security Settings -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Security Settings</h3>
                            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="section" value="security">

                                <div>
                                    <label for="session_timeout" class="block text-sm font-medium text-gray-700">Session Timeout (minutes)</label>
                                    <input type="number" name="session_timeout" id="session_timeout" value="120" min="5" max="1440"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="max_login_attempts" class="block text-sm font-medium text-gray-700">Max Login Attempts</label>
                                    <input type="number" name="max_login_attempts" id="max_login_attempts" value="5" min="1" max="20"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="require_2fa" class="flex items-center">
                                        <input type="checkbox" name="require_2fa" id="require_2fa" value="1"
                                               class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Require 2FA for Super Admins</span>
                                    </label>
                                </div>

                                <div>
                                    <label for="password_expiry_days" class="block text-sm font-medium text-gray-700">Password Expiry (days)</label>
                                    <input type="number" name="password_expiry_days" id="password_expiry_days" value="90" min="0" max="365"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    <p class="mt-1 text-sm text-gray-500">Set to 0 to disable password expiry</p>
                                </div>

                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    Update Security Settings
                                </button>
                            </form>
                        </div>

                        <!-- Email Settings -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Email Settings</h3>
                            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="section" value="email">

                                <div>
                                    <label for="smtp_host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                                    <input type="text" name="smtp_host" id="smtp_host" value="smtp.gmail.com"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="smtp_port" class="block text-sm font-medium text-gray-700">SMTP Port</label>
                                    <input type="number" name="smtp_port" id="smtp_port" value="587" min="1" max="65535"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="smtp_username" class="block text-sm font-medium text-gray-700">SMTP Username</label>
                                    <input type="text" name="smtp_username" id="smtp_username" value=""
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <div>
                                    <label for="smtp_encryption" class="block text-sm font-medium text-gray-700">SMTP Encryption</label>
                                    <select name="smtp_encryption" id="smtp_encryption"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="">None</option>
                                    </select>
                                </div>

                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    Update Email Settings
                                </button>
                            </form>
                        </div>

                        <!-- Backup & Maintenance -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Backup & Maintenance</h3>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Database Backup</h4>
                                    <p class="text-sm text-gray-600 mb-3">Create a backup of the entire platform database</p>
                                    <form method="POST" action="{{ route('super-admin.settings.update') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="backup">
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                            Create Backup
                                        </button>
                                    </form>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Clear System Cache</h4>
                                    <p class="text-sm text-gray-600 mb-3">Clear all cached data to improve performance</p>
                                    <form method="POST" action="{{ route('super-admin.settings.update') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="clear_cache">
                                        <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                            Clear Cache
                                        </button>
                                    </form>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Optimize Database</h4>
                                    <p class="text-sm text-gray-600 mb-3">Optimize database tables for better performance</p>
                                    <form method="POST" action="{{ route('super-admin.settings.update') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="optimize">
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                            Optimize Database
                                        </button>
                                    </form>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">System Logs</h4>
                                    <p class="text-sm text-gray-600 mb-3">View and manage system logs</p>
                                    <div class="flex space-x-2">
                                        <a href="#" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                            View Logs
                                        </a>
                                        <form method="POST" action="{{ route('super-admin.settings.update') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="clear_logs">
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors" onclick="return confirm('Are you sure you want to clear all logs?')">
                                                Clear Logs
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">System Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Platform Version</dt>
                                <dd class="text-sm text-gray-900">MediCon v1.0.0</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Laravel Version</dt>
                                <dd class="text-sm text-gray-900">{{ app()->version() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">PHP Version</dt>
                                <dd class="text-sm text-gray-900">{{ PHP_VERSION }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Database</dt>
                                <dd class="text-sm text-gray-900">{{ config('database.default') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Environment</dt>
                                <dd class="text-sm text-gray-900">{{ app()->environment() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Debug Mode</dt>
                                <dd class="text-sm text-gray-900">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-superadmin-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('System Logs') }}
            </h2>
            <div class="space-x-2">
                <form method="POST" action="{{ route('admin.settings.clear-logs') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to clear all logs?')">
                        Clear Logs
                    </button>
                </form>
                <a href="{{ route('admin.settings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Settings
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent System Logs</h3>
                    
                    @if(count($logs) > 0)
                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm max-h-96 overflow-y-auto">
                            @foreach($logs as $log)
                                @if(trim($log))
                                    <div class="mb-2 border-b border-gray-700 pb-2">
                                        <span class="text-gray-500">{{ $loop->iteration }}.</span>
                                        <span class="ml-2">{{ $log }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        
                        <div class="mt-4 text-sm text-gray-600">
                            <p><strong>Note:</strong> Showing the last {{ count($logs) }} log entries. Logs are automatically rotated to prevent excessive disk usage.</p>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No logs found</h3>
                            <p class="mt-1 text-sm text-gray-500">The system log file is empty or doesn't exist.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Log Information -->
            <div class="mt-6 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Log Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-medium text-blue-900 mb-2">Log Levels</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li><span class="font-mono bg-red-100 px-2 py-1 rounded">ERROR</span> - System errors and exceptions</li>
                                <li><span class="font-mono bg-yellow-100 px-2 py-1 rounded">WARNING</span> - Warning messages</li>
                                <li><span class="font-mono bg-blue-100 px-2 py-1 rounded">INFO</span> - General information</li>
                                <li><span class="font-mono bg-gray-100 px-2 py-1 rounded">DEBUG</span> - Debug information</li>
                            </ul>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-medium text-green-900 mb-2">Log Management</h4>
                            <ul class="text-sm text-green-800 space-y-1">
                                <li>• Logs are stored in <code class="bg-gray-200 px-1 rounded">storage/logs/</code></li>
                                <li>• Log files are rotated daily by default</li>
                                <li>• Old log files are automatically cleaned up</li>
                                <li>• Use "Clear Logs" to manually clear current logs</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Important Notice</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>System logs may contain sensitive information. Only authorized administrators should have access to this page. Regularly monitor logs for security issues and system errors.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

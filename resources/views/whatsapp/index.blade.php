<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="mr-4 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fab fa-whatsapp text-green-500 mr-2"></i>
                    {{ __('WhatsApp Messaging') }}
                </h2>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('whatsapp.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                    <i class="fas fa-plus mr-1"></i>
                    Send Message
                </a>
                <a href="{{ route('whatsapp.bulk') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                    <i class="fas fa-broadcast-tower mr-1"></i>
                    Bulk Message
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Test Mode Notice -->
            @if(config('whatsapp.test_mode', true))
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-flask text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                🧪 Test Mode Active
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>WhatsApp messages are being simulated for testing. No actual messages will be sent to customers. Check the logs to see simulated message details.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-paper-plane text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Messages</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_messages']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Sent Today</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['sent_today']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-check-double text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Delivered Today</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['delivered_today']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Failed Today</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['failed_today']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('whatsapp.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors group">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 group-hover:bg-green-200">
                                <i class="fas fa-user text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-medium text-gray-900">Send to Customer</h4>
                                <p class="text-sm text-gray-500">Send message to individual customer</p>
                            </div>
                        </a>

                        <a href="{{ route('whatsapp.bulk') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors group">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-200">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-medium text-gray-900">Bulk Message</h4>
                                <p class="text-sm text-gray-500">Send to multiple customers</p>
                            </div>
                        </a>

                        <a href="{{ route('whatsapp.history') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-colors group">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 group-hover:bg-purple-200">
                                <i class="fas fa-history text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-medium text-gray-900">Message History</h4>
                                <p class="text-sm text-gray-500">View sent messages and status</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Recent Messages</h3>
                        <a href="{{ route('whatsapp.history') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View All
                        </a>
                    </div>
                </div>
                <div class="overflow-hidden">
                    @if($recentMessages->count() > 0)
                        <div class="divide-y divide-gray-200">
                            @foreach($recentMessages as $message)
                                <div class="p-6 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @if($message->status === 'delivered' || $message->status === 'read')
                                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-check text-green-600 text-sm"></i>
                                                    </div>
                                                @elseif($message->status === 'sent')
                                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-paper-plane text-blue-600 text-sm"></i>
                                                    </div>
                                                @elseif($message->status === 'failed')
                                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                                    </div>
                                                @else
                                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-clock text-gray-600 text-sm"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="flex items-center">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $message->customer ? $message->customer->name : 'Unknown Customer' }}
                                                    </p>
                                                    @if($message->is_bulk_message)
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            Bulk
                                                        </span>
                                                    @endif
                                                    @if($message->template)
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                            Template
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-500 mt-1">{{ Str::limit($message->message_content, 100) }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">{{ $message->created_at->diffForHumans() }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ ucfirst($message->status) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <div class="w-12 h-12 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fab fa-whatsapp text-gray-400 text-xl"></i>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">No messages yet</h3>
                            <p class="text-sm text-gray-500 mb-4">Start sending WhatsApp messages to your customers</p>
                            <a href="{{ route('whatsapp.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                Send First Message
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('whatsapp.index') }}" class="mr-4 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-history text-purple-500 mr-2"></i>
                    {{ __('WhatsApp Message History') }}
                </h2>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('whatsapp.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                    <i class="fas fa-plus mr-1"></i>
                    New Message
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
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Filters -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Filter by Status:</label>
                            <select class="rounded-md border-gray-300 text-sm" onchange="filterMessages(this.value)">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="sent">Sent</option>
                                <option value="delivered">Delivered</option>
                                <option value="read">Read</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Message Type:</label>
                            <select class="rounded-md border-gray-300 text-sm" onchange="filterMessageType(this.value)">
                                <option value="">All Types</option>
                                <option value="text">Text</option>
                                <option value="template">Template</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Bulk Messages:</label>
                            <select class="rounded-md border-gray-300 text-sm" onchange="filterBulkMessages(this.value)">
                                <option value="">All Messages</option>
                                <option value="individual">Individual Only</option>
                                <option value="bulk">Bulk Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Messages Table -->
                <div class="overflow-x-auto">
                    @if($messages->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Message
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sent By
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($messages as $message)
                                    <tr class="hover:bg-gray-50" data-status="{{ $message->status }}" data-type="{{ $message->message_type }}" data-bulk="{{ $message->is_bulk_message ? 'bulk' : 'individual' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($message->status === 'delivered' || $message->status === 'read')
                                                    <div class="flex-shrink-0 w-2.5 h-2.5 bg-green-400 rounded-full"></div>
                                                    <span class="ml-2 text-sm font-medium text-green-800">
                                                        {{ $message->status === 'read' ? 'Read' : 'Delivered' }}
                                                    </span>
                                                @elseif($message->status === 'sent')
                                                    <div class="flex-shrink-0 w-2.5 h-2.5 bg-blue-400 rounded-full"></div>
                                                    <span class="ml-2 text-sm font-medium text-blue-800">Sent</span>
                                                @elseif($message->status === 'failed')
                                                    <div class="flex-shrink-0 w-2.5 h-2.5 bg-red-400 rounded-full"></div>
                                                    <span class="ml-2 text-sm font-medium text-red-800">Failed</span>
                                                @else
                                                    <div class="flex-shrink-0 w-2.5 h-2.5 bg-gray-400 rounded-full"></div>
                                                    <span class="ml-2 text-sm font-medium text-gray-800">Pending</span>
                                                @endif
                                            </div>
                                            @if($message->status === 'read' && $message->read_at)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Read: {{ $message->read_at->format('M j, g:i A') }}
                                                </div>
                                            @elseif($message->status === 'delivered' && $message->delivered_at)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Delivered: {{ $message->delivered_at->format('M j, g:i A') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $message->customer ? $message->customer->name : 'Unknown Customer' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $message->recipient_phone }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 max-w-xs">
                                                {{ Str::limit($message->message_content, 100) }}
                                            </div>
                                            @if($message->template)
                                                <div class="text-xs text-purple-600 mt-1">
                                                    Template: {{ $message->template->display_name }}
                                                </div>
                                            @endif
                                            @if($message->error_message)
                                                <div class="text-xs text-red-600 mt-1">
                                                    Error: {{ Str::limit($message->error_message, 50) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                @if($message->message_type === 'template')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        Template
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Text
                                                    </span>
                                                @endif
                                                
                                                @if($message->is_bulk_message)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        Bulk
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $message->user ? $message->user->name : 'System' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ $message->created_at->format('M j, Y') }}</div>
                                            <div class="text-xs">{{ $message->created_at->format('g:i A') }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $messages->links() }}
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <div class="w-12 h-12 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-history text-gray-400 text-xl"></i>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">No messages found</h3>
                            <p class="text-sm text-gray-500 mb-4">You haven't sent any WhatsApp messages yet</p>
                            <a href="{{ route('whatsapp.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                Send First Message
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function filterMessages(status) {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (status === '' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function filterMessageType(type) {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (type === '' || row.dataset.type === type) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function filterBulkMessages(bulkType) {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (bulkType === '' || row.dataset.bulk === bulkType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    @endpush
</x-app-layout>

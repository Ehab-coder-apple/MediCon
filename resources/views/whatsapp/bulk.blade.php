<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('whatsapp.index') }}" class="mr-4 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-broadcast-tower text-blue-500 mr-2"></i>
                    {{ __('Bulk WhatsApp Message') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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
                                <p>Bulk messages will be simulated for testing. No actual WhatsApp messages will be sent to customers.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Warning Notice -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Important: Bulk Messaging Guidelines
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Only send relevant messages to avoid spam complaints</li>
                                <li>Respect customer preferences and opt-out requests</li>
                                <li>Use approved templates for promotional content</li>
                                <li>Messages are sent with delays to respect rate limits</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <form method="POST" action="{{ route('whatsapp.bulk.send') }}" id="bulkMessageForm">
                        @csrf

                        <!-- Recipient Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Recipients
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="recipient_filter" value="all" 
                                           class="text-blue-600 focus:ring-blue-500" 
                                           {{ old('recipient_filter', 'all') === 'all' ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">All Customers</div>
                                        <div class="text-sm text-gray-500">Send to all customers with phone numbers ({{ number_format($customerCount) }} customers)</div>
                                    </div>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="recipient_filter" value="active" 
                                           class="text-blue-600 focus:ring-blue-500"
                                           {{ old('recipient_filter') === 'active' ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Active Customers</div>
                                        <div class="text-sm text-gray-500">Customers with recent purchases or prescriptions (last 30 days)</div>
                                    </div>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="recipient_filter" value="recent" 
                                           class="text-blue-600 focus:ring-blue-500"
                                           {{ old('recipient_filter') === 'recent' ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">New Customers</div>
                                        <div class="text-sm text-gray-500">Customers registered in the last 30 days</div>
                                    </div>
                                </label>
                            </div>
                            @error('recipient_filter')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message Type Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Message Type
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="message_type" value="text" id="bulkMessageTypeText"
                                           class="sr-only peer" {{ old('message_type', 'text') === 'text' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                        <div class="flex items-center">
                                            <i class="fas fa-comment text-blue-500 text-xl mr-3"></i>
                                            <div>
                                                <h3 class="font-medium text-gray-900">Text Message</h3>
                                                <p class="text-sm text-gray-500">Send a custom text message</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="message_type" value="template" id="bulkMessageTypeTemplate"
                                           class="sr-only peer" {{ old('message_type') === 'template' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-alt text-purple-500 text-xl mr-3"></i>
                                            <div>
                                                <h3 class="font-medium text-gray-900">Template Message</h3>
                                                <p class="text-sm text-gray-500">Use a pre-approved template (recommended)</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('message_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Text Message Content -->
                        <div id="textMessageSection" class="mb-6" style="{{ old('message_type', 'text') === 'text' ? '' : 'display: none;' }}">
                            <label for="message_content" class="block text-sm font-medium text-gray-700 mb-2">
                                Message Content
                            </label>
                            <textarea name="message_content" id="message_content" rows="6" 
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                      placeholder="Type your bulk message here..."
                                      maxlength="4096">{{ old('message_content') }}</textarea>
                            <div class="mt-1 flex justify-between">
                                <span class="text-sm text-gray-500">Maximum 4,096 characters</span>
                                <span id="charCount" class="text-sm text-gray-500">0 / 4096</span>
                            </div>
                            @error('message_content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Template Message Section -->
                        <div id="templateMessageSection" class="mb-6" style="{{ old('message_type') === 'template' ? '' : 'display: none;' }}">
                            <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Select Template
                            </label>
                            <select name="template_id" id="template_id" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Choose a template...</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->display_name }} ({{ ucfirst($template->category) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <!-- Template Preview -->
                            <div id="templatePreview" class="mt-4 p-4 bg-gray-50 rounded-lg" style="display: none;">
                                <h4 class="font-medium text-gray-900 mb-2">Template Preview:</h4>
                                <div id="templateContent" class="text-sm text-gray-700 whitespace-pre-wrap"></div>
                            </div>

                            <!-- Template Parameters -->
                            <div id="templateParameters" class="mt-4" style="display: none;">
                                <h4 class="font-medium text-gray-900 mb-2">Template Parameters:</h4>
                                <p class="text-sm text-gray-600 mb-3">These values will be used for all recipients</p>
                                <div id="parameterInputs"></div>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center">
                                <input type="checkbox" id="confirmSend" required 
                                       class="text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="confirmSend" class="ml-2 text-sm text-blue-900">
                                    I confirm that I want to send this message to multiple customers and understand that this action cannot be undone.
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('whatsapp.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors flex items-center">
                                <i class="fas fa-broadcast-tower mr-2"></i>
                                Send Bulk Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textRadio = document.getElementById('bulkMessageTypeText');
            const templateRadio = document.getElementById('bulkMessageTypeTemplate');
            const textSection = document.getElementById('textMessageSection');
            const templateSection = document.getElementById('templateMessageSection');
            const messageContent = document.getElementById('message_content');
            const charCount = document.getElementById('charCount');
            const templateSelect = document.getElementById('template_id');
            const templatePreview = document.getElementById('templatePreview');
            const templateContent = document.getElementById('templateContent');
            const templateParameters = document.getElementById('templateParameters');
            const parameterInputs = document.getElementById('parameterInputs');

            // Function to toggle sections
            function toggleSections() {
                if (textRadio.checked) {
                    textSection.style.display = 'block';
                    templateSection.style.display = 'none';
                    console.log('Bulk text section shown');
                } else if (templateRadio.checked) {
                    textSection.style.display = 'none';
                    templateSection.style.display = 'block';
                    console.log('Bulk template section shown');
                }
            }

            // Handle message type change
            textRadio.addEventListener('change', toggleSections);
            templateRadio.addEventListener('change', toggleSections);

            // Initial toggle
            toggleSections();

            // Character count for text message
            if (messageContent) {
                messageContent.addEventListener('input', function() {
                    const count = this.value.length;
                    charCount.textContent = `${count} / 4096`;
                    
                    if (count > 4000) {
                        charCount.classList.add('text-red-500');
                    } else {
                        charCount.classList.remove('text-red-500');
                    }
                });

                // Initial count
                const initialCount = messageContent.value.length;
                charCount.textContent = `${initialCount} / 4096`;
            }

            // Handle template selection (same as individual message)
            if (templateSelect) {
                templateSelect.addEventListener('change', function() {
                    const templateId = this.value;
                    
                    if (templateId) {
                        fetch(`/whatsapp/template/${templateId}`)
                            .then(response => response.json())
                            .then(data => {
                                templateContent.textContent = data.body_text;
                                templatePreview.style.display = 'block';
                                
                                if (data.parameters && data.parameters.length > 0) {
                                    let parametersHtml = '';
                                    data.parameters.forEach((param, index) => {
                                        parametersHtml += `
                                            <div class="mb-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    ${param.charAt(0).toUpperCase() + param.slice(1)}
                                                </label>
                                                <input type="text" 
                                                       name="template_parameters[${param}]" 
                                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                       placeholder="Enter ${param}">
                                            </div>
                                        `;
                                    });
                                    parameterInputs.innerHTML = parametersHtml;
                                    templateParameters.style.display = 'block';
                                } else {
                                    templateParameters.style.display = 'none';
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching template:', error);
                                templatePreview.style.display = 'none';
                                templateParameters.style.display = 'none';
                            });
                    } else {
                        templatePreview.style.display = 'none';
                        templateParameters.style.display = 'none';
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

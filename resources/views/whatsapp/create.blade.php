<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('whatsapp.index') }}" class="mr-4 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fab fa-whatsapp text-green-500 mr-2"></i>
                    {{ __('Send WhatsApp Message') }}
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
                                <p>This message will be simulated for testing. No actual WhatsApp message will be sent.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <form method="POST" action="{{ route('whatsapp.send') }}" id="messageForm">
                        @csrf

                        <!-- Customer Selection -->
                        <div class="mb-6">
                            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Select Customer
                            </label>
                            <select name="customer_id" id="customer_id" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" 
                                    required>
                                <option value="">Choose a customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} - {{ $customer->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
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
                                    <input type="radio" name="message_type" value="text" id="messageTypeText"
                                           class="sr-only peer" {{ old('message_type', 'text') === 'text' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-300 transition-colors">
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
                                    <input type="radio" name="message_type" value="template" id="messageTypeTemplate"
                                           class="sr-only peer" {{ old('message_type') === 'template' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-300 transition-colors">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-alt text-purple-500 text-xl mr-3"></i>
                                            <div>
                                                <h3 class="font-medium text-gray-900">Template Message</h3>
                                                <p class="text-sm text-gray-500">Use a pre-approved template</p>
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
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" 
                                      placeholder="Type your message here..."
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
                            @if(isset($templates) && count($templates) > 0)
                                <select name="template_id" id="template_id"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="">Choose a template...</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-sm text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        No templates available. Please contact your administrator to create templates.
                                    </p>
                                </div>
                            @endif
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
                                <div id="parameterInputs"></div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('whatsapp.index') }}"
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                Cancel
                            </a>
                            <button type="button" id="previewBtn"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors flex items-center">
                                <i class="fas fa-eye mr-2"></i>
                                Preview Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fab fa-whatsapp text-green-500 mr-2"></i>
                    Confirm Message
                </h3>
                <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Message Preview -->
            <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="mb-3">
                    <p class="text-sm text-gray-600">
                        <strong>To:</strong> <span id="previewCustomerName"></span>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>Phone:</strong> <span id="previewCustomerPhone"></span>
                    </p>
                </div>
                <div class="border-t pt-3">
                    <p class="text-sm text-gray-600 mb-2"><strong>Message:</strong></p>
                    <div id="previewMessageContent" class="text-sm text-gray-800 whitespace-pre-wrap bg-white p-3 rounded border border-gray-200"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3">
                <button type="button" id="cancelBtn"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirmBtn"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors flex items-center">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Send Message
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textRadio = document.getElementById('messageTypeText');
            const templateRadio = document.getElementById('messageTypeTemplate');
            const textSection = document.getElementById('textMessageSection');
            const templateSection = document.getElementById('templateMessageSection');
            const messageContent = document.getElementById('message_content');
            const charCount = document.getElementById('charCount');
            const templateSelect = document.getElementById('template_id');
            const templatePreview = document.getElementById('templatePreview');
            const templateContent = document.getElementById('templateContent');
            const templateParameters = document.getElementById('templateParameters');
            const parameterInputs = document.getElementById('parameterInputs');
            const messageForm = document.getElementById('messageForm');
            const previewBtn = document.getElementById('previewBtn');
            const confirmationModal = document.getElementById('confirmationModal');
            const closeModal = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmBtn = document.getElementById('confirmBtn');

            // Function to toggle sections
            function toggleSections() {
                if (textRadio.checked) {
                    textSection.style.display = 'block';
                    templateSection.style.display = 'none';
                    console.log('Text section shown');
                } else if (templateRadio.checked) {
                    textSection.style.display = 'none';
                    templateSection.style.display = 'block';
                    console.log('Template section shown');
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

            // Handle template selection
            if (templateSelect) {
                templateSelect.addEventListener('change', function() {
                    const templateId = this.value;

                    if (templateId) {
                        fetch(`/whatsapp/template/${templateId}`)
                            .then(response => response.json())
                            .then(data => {
                                // Show template preview
                                templateContent.textContent = data.body_text;
                                templatePreview.style.display = 'block';

                                // Show parameter inputs if needed
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
                                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
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

            // Preview button handler
            if (previewBtn) {
                previewBtn.addEventListener('click', async function(e) {
                    e.preventDefault();

                    // Validate form
                    const customerId = document.getElementById('customer_id').value;

                    if (!customerId) {
                        alert('Please select a customer');
                        return;
                    }

                    // Build form data manually to avoid sending null values
                    const formData = new FormData();
                    const messageType = document.querySelector('input[name="message_type"]:checked').value;

                    formData.append('customer_id', customerId);
                    formData.append('message_type', messageType);
                    formData.append('_token', document.querySelector('input[name="_token"]').value);

                    if (messageType === 'text') {
                        const messageContent = document.getElementById('message_content').value;
                        if (messageContent) {
                            formData.append('message_content', messageContent);
                        }
                    } else {
                        const templateId = document.getElementById('template_id').value;
                        if (templateId) {
                            formData.append('template_id', templateId);
                        }

                        // Add template parameters
                        const paramInputs = document.querySelectorAll('input[name^="template_parameters"]');
                        paramInputs.forEach(input => {
                            formData.append(input.name, input.value);
                        });
                    }

                    try {
                        const response = await fetch('{{ route("whatsapp.preview") }}', {
                            method: 'POST',
                            body: formData,
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Populate modal with preview data
                            document.getElementById('previewCustomerName').textContent = data.preview.customer_name;
                            document.getElementById('previewCustomerPhone').textContent = data.preview.customer_phone;
                            document.getElementById('previewMessageContent').textContent = data.preview.message_content;

                            // Show modal
                            confirmationModal.classList.remove('hidden');
                        } else {
                            alert('Error: ' + (data.error || 'Failed to preview message'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while previewing the message');
                    }
                });
            }

            // Close modal handlers
            closeModal.addEventListener('click', function() {
                confirmationModal.classList.add('hidden');
            });

            cancelBtn.addEventListener('click', function() {
                confirmationModal.classList.add('hidden');
            });

            // Confirm and send
            confirmBtn.addEventListener('click', function() {
                messageForm.submit();
            });

            // Close modal when clicking outside
            confirmationModal.addEventListener('click', function(e) {
                if (e.target === confirmationModal) {
                    confirmationModal.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Import Products') }}
            </h2>
            @php
                $routePrefix = '';
                if (auth()->user()->hasRole('admin')) {
                    $routePrefix = 'admin.';
                } elseif (auth()->user()->hasRole('pharmacist')) {
                    $routePrefix = 'pharmacist.';
                }
            @endphp
            <div class="flex space-x-3">
                <a href="{{ route($routePrefix . 'products.template') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Download CSV Template
                </a>
                <a href="{{ route($routePrefix . 'products.template.excel') }}" class="bg-emerald-500 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded">
                    Download Excel Template
                </a>
                <a href="{{ route($routePrefix . 'products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Products
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-medium text-gray-900">
                        Import Products from File
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Upload a CSV or Excel file to bulk import products into your inventory
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium text-blue-900 mb-2">📋 Import Instructions</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• <strong>Supported formats:</strong> CSV (.csv), Excel (.xlsx), Text (.txt) with UTF-8 encoding</li>
                            <li>• <strong>Maximum file size:</strong> 2MB</li>
                            <li>• <strong>Separators:</strong> Comma (,), Semicolon (;), Tab, or Pipe (|) - auto-detected</li>
                            <li>• <strong>Required columns:</strong> name, category, manufacturer, code, batch_number, expiry_date, initial_quantity, cost_price, selling_price</li>
                            <li>• <strong>Optional columns:</strong> alert_quantity, days_on_hand, description, is_active</li>
                            <li>• <strong>Date format:</strong> YYYY-MM-DD (e.g., 2024-12-31) or DD/MM/YYYY</li>
                            <li>• <strong>Numbers:</strong> Use decimal point (.) for prices, no currency symbols</li>
                            <li>• <strong>Download template:</strong> Use the template above for correct format</li>
                            <li>• <strong>Batch tracking:</strong> Each product must include batch information for inventory tracking</li>
                            <li>• <strong>Manufacturer info:</strong> Company name is required for both product and batch records</li>
                        </ul>
                    </div>

                    <!-- Troubleshooting -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium text-yellow-900 mb-2">🔧 Troubleshooting Common Issues</h3>
                        <ul class="text-sm text-yellow-800 space-y-1">
                            <li>• <strong>"Separation symbol not found":</strong> Ensure your CSV uses comma, semicolon, tab, or pipe separators</li>
                            <li>• <strong>"Unexpected data found":</strong> Check for extra commas, quotes, or line breaks in your data</li>
                            <li>• <strong>"Missing required field":</strong> Verify all required columns are present and not empty</li>
                            <li>• <strong>"Invalid date format":</strong> Use YYYY-MM-DD format (e.g., 2024-12-31)</li>
                            <li>• <strong>File encoding issues:</strong> Save your CSV with UTF-8 encoding</li>
                            <li>• <strong>Special characters:</strong> Avoid special characters in product codes and names</li>
                        </ul>
                    </div>

                    <!-- File Upload Form -->
                    <form action="{{ route($routePrefix . 'products.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <!-- File Upload -->
                        <div class="mb-6">
                            <label for="import_file" class="block text-sm font-medium text-gray-700 mb-2">
                                Select File to Import
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="import_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="import_file" name="import_file" type="file" class="sr-only" accept=".csv,.txt,.xlsx" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">CSV, Excel, TXT up to 2MB</p>
                                </div>
                            </div>
                            @error('import_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- File Preview -->
                            <div id="filePreview" class="mt-3 hidden">
                                <div class="flex items-center p-3 bg-gray-50 rounded-md">
                                    <svg class="w-8 h-8 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900" id="fileName"></p>
                                        <p class="text-xs text-gray-500" id="fileSize"></p>
                                    </div>
                                    <button type="button" id="removeFile" class="text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Import Options -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Has Headers -->
                            <div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="has_headers" id="has_headers" value="1" 
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                        checked>
                                    <label for="has_headers" class="ml-2 block text-sm text-gray-900">
                                        File has header row
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Check if your file's first row contains column names
                                </p>
                            </div>

                            <!-- Update Existing -->
                            <div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="update_existing" id="update_existing" value="1" 
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="update_existing" class="ml-2 block text-sm text-gray-900">
                                        Update existing products
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Update products with matching codes instead of skipping them
                                </p>
                            </div>
                        </div>

                        <!-- Column Mapping Info -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">📊 Expected Column Format</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs text-gray-600 mb-3">
                                <div class="font-medium text-red-600 col-span-full">Required Fields:</div>
                                <div><strong>name:</strong> Product name</div>
                                <div><strong>category:</strong> Product category</div>
                                <div><strong>manufacturer:</strong> Company name</div>
                                <div><strong>code:</strong> Unique product code</div>
                                <div><strong>batch_number:</strong> Batch identifier</div>
                                <div><strong>expiry_date:</strong> YYYY-MM-DD format</div>
                                <div><strong>initial_quantity:</strong> Starting quantity</div>
                                <div><strong>cost_price:</strong> Cost price (number)</div>
                                <div><strong>selling_price:</strong> Selling price (number)</div>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs text-gray-600">
                                <div class="font-medium text-blue-600 col-span-full">Optional Fields:</div>
                                <div><strong>alert_quantity:</strong> Alert quantity (default: 10)</div>
                                <div><strong>days_on_hand:</strong> Days on hand (auto-calculated)</div>
                                <div><strong>description:</strong> Product description</div>
                                <div><strong>is_active:</strong> 1/true/yes for active</div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route($routePrefix . 'products.index') }}"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" id="submitBtn"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="submitText">Import Products</span>
                                <svg id="loadingSpinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File upload handling
        const fileInput = document.getElementById('import_file');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const removeFileBtn = document.getElementById('removeFile');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const form = document.getElementById('importForm');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                showFilePreview(file);
            }
        });

        removeFileBtn.addEventListener('click', function() {
            fileInput.value = '';
            filePreview.classList.add('hidden');
        });

        function showFilePreview(file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.classList.remove('hidden');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form submission handling
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitText.textContent = 'Importing...';
            loadingSpinner.classList.remove('hidden');
        });

        // Drag and drop functionality
        const dropZone = document.querySelector('.border-dashed');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                showFilePreview(files[0]);
            }
        }
    </script>
</x-app-layout>

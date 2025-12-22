<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Code Generator') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-2xl font-medium text-gray-900">Code Generator</h1>
                            <p class="mt-2 text-sm text-gray-600">Generate unique codes for products, customers, and more</p>
                        </div>
                    </div>

                    <!-- Code Generation Forms -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Single Code Generator -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Single Code</h3>
                            <form id="singleCodeForm" class="space-y-4">
                                <div>
                                    <label for="codeType" class="block text-sm font-medium text-gray-700">Code Type</label>
                                    <select name="type" id="codeType" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">Select Code Type</option>
                                        <option value="product">Product Code</option>
                                        <option value="customer">Customer Code</option>
                                        <option value="batch">Batch Number</option>
                                        <option value="barcode">Barcode (EAN-13)</option>
                                        <option value="access">Access Code</option>
                                        <option value="prescription">Prescription Number</option>
                                        <option value="transaction">Transaction ID</option>
                                        <option value="reference">Reference Code</option>
                                    </select>
                                </div>

                                <!-- Category field (for products) -->
                                <div id="categoryField" class="hidden">
                                    <label for="category" class="block text-sm font-medium text-gray-700">Product Category</label>
                                    <select name="category" id="category"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">Select Category</option>
                                        <option value="Pain Relief">Pain Relief</option>
                                        <option value="Antibiotics">Antibiotics</option>
                                        <option value="Vitamins">Vitamins</option>
                                        <option value="Cold & Flu">Cold & Flu</option>
                                        <option value="Digestive">Digestive</option>
                                        <option value="Allergy">Allergy</option>
                                        <option value="Topical">Topical</option>
                                        <option value="Prescription">Prescription</option>
                                        <option value="OTC">Over-the-Counter</option>
                                    </select>
                                </div>

                                <!-- Product code field (for batches) -->
                                <div id="productCodeField" class="hidden">
                                    <label for="productCode" class="block text-sm font-medium text-gray-700">Product Code</label>
                                    <input type="text" name="product_code" id="productCode" placeholder="e.g., PR250001"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <!-- Prefix field (for reference codes) -->
                                <div id="prefixField" class="hidden">
                                    <label for="prefix" class="block text-sm font-medium text-gray-700">Prefix</label>
                                    <input type="text" name="prefix" id="prefix" placeholder="e.g., REF" maxlength="10"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                </div>

                                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    Generate Code
                                </button>
                            </form>

                            <!-- Single Code Result -->
                            <div id="singleResult" class="mt-6 hidden">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-green-800 mb-2">Generated Code:</h4>
                                    <div class="flex items-center justify-between">
                                        <code id="generatedCode" class="text-lg font-mono text-green-900 bg-white px-3 py-1 rounded border"></code>
                                        <button onclick="copyToClipboard('generatedCode')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Code Generator -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Bulk Codes</h3>
                            <form id="bulkCodeForm" class="space-y-4">
                                <div>
                                    <label for="bulkCodeType" class="block text-sm font-medium text-gray-700">Code Type</label>
                                    <select name="type" id="bulkCodeType" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">Select Code Type</option>
                                        <option value="product">Product Codes</option>
                                        <option value="customer">Customer Codes</option>
                                        <option value="barcode">Barcodes</option>
                                        <option value="access">Access Codes</option>
                                        <option value="reference">Reference Codes</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="count" class="block text-sm font-medium text-gray-700">Number of Codes</label>
                                    <input type="number" name="count" id="count" min="1" max="100" value="10" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    <p class="mt-1 text-sm text-gray-500">Maximum 100 codes per request</p>
                                </div>

                                <!-- Category field for bulk -->
                                <div id="bulkCategoryField" class="hidden">
                                    <label for="bulkCategory" class="block text-sm font-medium text-gray-700">Product Category</label>
                                    <select name="category" id="bulkCategory"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">Select Category</option>
                                        <option value="Pain Relief">Pain Relief</option>
                                        <option value="Antibiotics">Antibiotics</option>
                                        <option value="Vitamins">Vitamins</option>
                                        <option value="Cold & Flu">Cold & Flu</option>
                                        <option value="Digestive">Digestive</option>
                                        <option value="Allergy">Allergy</option>
                                        <option value="Topical">Topical</option>
                                        <option value="Prescription">Prescription</option>
                                        <option value="OTC">Over-the-Counter</option>
                                    </select>
                                </div>

                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    Generate Codes
                                </button>
                            </form>

                            <!-- Bulk Code Results -->
                            <div id="bulkResult" class="mt-6 hidden">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-medium text-blue-800">Generated Codes:</h4>
                                        <button onclick="copyAllCodes()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                            Copy All
                                        </button>
                                    </div>
                                    <div id="bulkCodes" class="max-h-60 overflow-y-auto bg-white rounded border p-3">
                                        <!-- Codes will be inserted here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Code Validation -->
                    <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Code Validation</h3>
                        <form id="validateForm" class="flex space-x-4">
                            <div class="flex-1">
                                <input type="text" name="code" id="validateCode" placeholder="Enter code to validate" required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <select name="type" id="validateType" required
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Type</option>
                                    <option value="product">Product</option>
                                    <option value="customer">Customer</option>
                                    <option value="purchase">Purchase</option>
                                    <option value="invoice">Invoice</option>
                                    <option value="batch">Batch</option>
                                    <option value="access">Access</option>
                                    <option value="prescription">Prescription</option>
                                </select>
                            </div>
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Validate
                            </button>
                        </form>

                        <div id="validateResult" class="mt-4 hidden">
                            <!-- Validation result will be shown here -->
                        </div>
                    </div>

                    <!-- Code Statistics -->
                    <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Code Statistics</h3>
                        <div id="codeStats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Statistics will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide fields based on code type
        document.getElementById('codeType').addEventListener('change', function() {
            const type = this.value;
            document.getElementById('categoryField').classList.toggle('hidden', type !== 'product');
            document.getElementById('productCodeField').classList.toggle('hidden', type !== 'batch');
            document.getElementById('prefixField').classList.toggle('hidden', type !== 'reference');
        });

        document.getElementById('bulkCodeType').addEventListener('change', function() {
            const type = this.value;
            document.getElementById('bulkCategoryField').classList.toggle('hidden', type !== 'product');
        });

        // Single code generation
        document.getElementById('singleCodeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/admin/code-generator/single', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('generatedCode').textContent = data.code;
                    document.getElementById('singleResult').classList.remove('hidden');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error generating code: ' + error.message);
            }
        });

        // Bulk code generation
        document.getElementById('bulkCodeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/admin/code-generator/bulk', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const codesHtml = data.codes.map(code => 
                        `<div class="font-mono text-sm py-1">${code}</div>`
                    ).join('');
                    
                    document.getElementById('bulkCodes').innerHTML = codesHtml;
                    document.getElementById('bulkResult').classList.remove('hidden');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error generating codes: ' + error.message);
            }
        });

        // Code validation
        document.getElementById('validateForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/admin/code-generator/validate', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const resultClass = data.is_valid ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
                    const icon = data.is_valid ? '✓' : '✗';
                    
                    document.getElementById('validateResult').innerHTML = `
                        <div class="${resultClass} border rounded-lg p-3">
                            <span class="font-bold">${icon}</span> ${data.message}
                        </div>
                    `;
                    document.getElementById('validateResult').classList.remove('hidden');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error validating code: ' + error.message);
            }
        });

        // Copy functions
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            navigator.clipboard.writeText(element.textContent).then(() => {
                alert('Code copied to clipboard!');
            });
        }

        function copyAllCodes() {
            const codes = Array.from(document.querySelectorAll('#bulkCodes div')).map(div => div.textContent).join('\n');
            navigator.clipboard.writeText(codes).then(() => {
                alert('All codes copied to clipboard!');
            });
        }

        // Load statistics on page load
        async function loadStatistics() {
            try {
                const response = await fetch('/admin/code-generator/statistics');
                const data = await response.json();
                
                if (data.success) {
                    const statsHtml = Object.entries(data.total_codes).map(([key, value]) => `
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">${value}</div>
                            <div class="text-sm text-gray-600">${key.replace('_', ' ').toUpperCase()}</div>
                        </div>
                    `).join('');
                    
                    document.getElementById('codeStats').innerHTML = statsHtml;
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        // Load statistics when page loads
        document.addEventListener('DOMContentLoaded', loadStatistics);
    </script>
</x-app-layout>

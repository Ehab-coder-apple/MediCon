<!-- Barcode Scanner Component -->
<div class="barcode-scanner-container">
    <!-- Scanner Toggle Button -->
    <div class="flex items-center space-x-4 mb-4">
        <button type="button" id="toggleScanner" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 4h5m0 0v5m0 0h5m0 0V4"></path>
            </svg>
            <span id="scannerButtonText">Start Barcode Scanner</span>
        </button>

        <button type="button" id="openSimulator" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span>Test Simulator</span>
        </button>

        <div class="text-sm text-gray-600">
            <span>Or manually enter product code/name:</span>
        </div>
    </div>

    <!-- Manual Product Search -->
    <div class="mb-4">
        <div class="relative">
            <input type="text" 
                   id="productSearch" 
                   placeholder="Search by product name or scan/enter barcode..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   autocomplete="off">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        
        <!-- Search Results Dropdown -->
        <div id="searchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden max-h-60 overflow-y-auto">
            <!-- Results will be populated here -->
        </div>
    </div>

    <!-- Camera Scanner -->
    <div id="scannerContainer" class="hidden mb-4">
        <div class="bg-gray-100 rounded-lg p-4">
            <div class="text-center mb-2">
                <span class="text-sm text-gray-600">Position barcode within the frame</span>
            </div>
            <div class="relative">
                <video id="scannerVideo" class="w-full max-w-md mx-auto rounded-lg" autoplay muted playsinline></video>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="border-2 border-red-500 w-64 h-32 rounded-lg opacity-50"></div>
                </div>
            </div>
            <div class="text-center mt-2">
                <button type="button" id="stopScanner" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                    Stop Scanner
                </button>
            </div>
        </div>
    </div>

    <!-- Scanner Status -->
    <div id="scannerStatus" class="hidden mb-4">
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
            <span id="statusMessage">Initializing scanner...</span>
        </div>
    </div>
</div>

<script>
class BarcodeScanner {
    constructor() {
        this.isScanning = false;
        this.stream = null;
        this.video = document.getElementById('scannerVideo');
        this.container = document.getElementById('scannerContainer');
        this.toggleButton = document.getElementById('toggleScanner');
        this.stopButton = document.getElementById('stopScanner');
        this.buttonText = document.getElementById('scannerButtonText');
        this.statusDiv = document.getElementById('scannerStatus');
        this.statusMessage = document.getElementById('statusMessage');
        this.searchInput = document.getElementById('productSearch');
        this.searchResults = document.getElementById('searchResults');
        this.simulatorButton = document.getElementById('openSimulator');

        this.initializeEventListeners();
        this.initializeProductSearch();
    }

    initializeEventListeners() {
        this.toggleButton.addEventListener('click', () => {
            if (this.isScanning) {
                this.stopScanning();
            } else {
                this.startScanning();
            }
        });

        this.stopButton.addEventListener('click', () => {
            this.stopScanning();
        });

        // Open simulator in a popup window
        if (this.simulatorButton) {
            this.simulatorButton.addEventListener('click', () => {
                console.log('🔌 Opening barcode simulator...');
                window.open('/barcode-simulator.html', 'barcodeSimulator', 'width=600,height=700,resizable=yes');
            });
        }

        // Listen for keyboard input (for USB barcode scanners)
        document.addEventListener('keydown', (e) => {
            if (e.target === this.searchInput) {
                console.log('⌨️ Keydown event on search input:', e.key);
                // Handle barcode scanner input (usually ends with Enter)
                if (e.key === 'Enter') {
                    console.log('⌨️ Enter key pressed');
                    e.preventDefault();
                    this.handleBarcodeInput(this.searchInput.value.trim());
                }
            }
        });
    }

    initializeProductSearch() {
        console.log('📝 Initializing product search...');
        let searchTimeout;

        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            console.log('📝 Input event:', query);

            if (query.length < 2) {
                this.hideSearchResults();
                return;
            }

            searchTimeout = setTimeout(() => {
                this.searchProducts(query);
            }, 300);
        });

        // Hide results when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.barcode-scanner-container')) {
                this.hideSearchResults();
            }
        });

        console.log('✅ Product search initialized');
    }

    async startScanning() {
        try {
            this.showStatus('Requesting camera access...');
            
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });
            
            this.video.srcObject = this.stream;
            this.container.classList.remove('hidden');
            this.isScanning = true;
            this.buttonText.textContent = 'Stop Scanner';
            this.toggleButton.classList.remove('bg-blue-500', 'hover:bg-blue-700');
            this.toggleButton.classList.add('bg-red-500', 'hover:bg-red-700');
            
            this.showStatus('Scanner active - point camera at barcode');
            
            // Start barcode detection
            this.detectBarcode();
            
        } catch (error) {
            console.error('Error starting scanner:', error);
            this.showStatus('Camera access denied or not available. Please use manual input.', 'error');
        }
    }

    stopScanning() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        this.container.classList.add('hidden');
        this.isScanning = false;
        this.buttonText.textContent = 'Start Barcode Scanner';
        this.toggleButton.classList.remove('bg-red-500', 'hover:bg-red-700');
        this.toggleButton.classList.add('bg-blue-500', 'hover:bg-blue-700');
        this.hideStatus();
    }

    detectBarcode() {
        if (!this.isScanning) return;
        
        // This is a simplified barcode detection
        // In a real implementation, you would use a library like QuaggaJS or ZXing
        // For now, we'll simulate barcode detection
        
        setTimeout(() => {
            if (this.isScanning) {
                this.detectBarcode();
            }
        }, 100);
    }

    async handleBarcodeInput(barcode) {
        if (!barcode) return;

        console.log('🔍 Barcode scanned:', barcode);
        this.showStatus('Processing barcode: ' + barcode);
        await this.searchProducts(barcode, true);

        // Clear the input after search completes
        this.searchInput.value = '';
    }

    async searchProducts(query, isBarcode = false) {
        try {
            let response;
            let data;

            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Use the product lookup endpoint (available to all authenticated users)
            // The route is at /sales/product-lookup (not /admin/sales/product-lookup)
            const url = `/sales/product-lookup?query=${encodeURIComponent(query)}`;
            console.log('📡 Fetching product from:', url);

            const fetchOptions = {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            // Add CSRF token if available
            if (csrfToken) {
                fetchOptions.headers['X-CSRF-TOKEN'] = csrfToken;
            }

            response = await fetch(url, fetchOptions);

            console.log('📡 Response status:', response.status);

            if (!response.ok) {
                console.log('❌ Response error:', response.statusText);
                this.showStatus('Error fetching product: ' + response.statusText, 'error');
                return;
            }

            data = await response.json();
            console.log('📡 Response data:', data);

            // If it's a barcode scan, add the first product directly
            if (isBarcode) {
                if (data.products && data.products.length > 0) {
                    // Get the first product (exact barcode match)
                    const product = data.products[0];
                    console.log('✅ Product found:', product.name);
                    this.addProductToSale(product);
                    this.hideStatus();
                    return;
                } else {
                    console.log('❌ No products found for barcode:', query);
                    this.showStatus('Barcode not found: ' + query, 'error');
                    this.showSearchResults([]);
                    return;
                }
            }

            // Otherwise show search results
            if (data.products && data.products.length > 0) {
                this.showSearchResults(data.products);
            } else {
                this.showSearchResults([]);
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showStatus('Search failed. Please try again.', 'error');
        }
    }

    /**
     * Get authentication token from localStorage or meta tag
     */
    getAuthToken() {
        // Try to get from localStorage (for API calls)
        let token = localStorage.getItem('auth_token');
        if (token) return token;

        // Try to get from meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) return metaTag.getAttribute('content');

        return '';
    }

    showSearchResults(products) {
        if (products.length === 0) {
            this.searchResults.innerHTML = '<div class="p-3 text-gray-500 text-center">No products found</div>';
        } else {
            this.searchResults.innerHTML = products.map(product => {
                const batchInfo = product.batches && product.batches.length > 0
                    ? `<div class="text-xs text-gray-400 mt-1">Batches: ${product.batches.length}</div>`
                    : '<div class="text-xs text-red-500 mt-1">No stock available</div>';

                return `
                    <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0"
                         onclick="barcodeScanner.addProductToSale(${JSON.stringify(product).replace(/"/g, '&quot;')})">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">${product.name}</div>
                                <div class="text-sm text-gray-500">Code: ${product.code || 'N/A'} | Stock: ${product.available_quantity || 0}</div>
                                ${batchInfo}
                            </div>
                            <div class="text-right ml-4">
                                <div class="font-bold text-green-600">$${parseFloat(product.selling_price).toFixed(2)}</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        this.searchResults.classList.remove('hidden');
    }

    hideSearchResults() {
        this.searchResults.classList.add('hidden');
    }

    addProductToSale(product) {
        // This function will be called by the parent sales form
        if (window.addProductToSaleForm) {
            window.addProductToSaleForm(product);
        }
        
        this.hideSearchResults();
        this.searchInput.value = '';
        
        if (this.isScanning) {
            this.stopScanning();
        }
    }

    showStatus(message, type = 'info') {
        this.statusMessage.textContent = message;
        this.statusDiv.className = `mb-4 px-4 py-3 rounded ${
            type === 'error' 
                ? 'bg-red-100 border border-red-400 text-red-700' 
                : 'bg-blue-100 border border-blue-400 text-blue-700'
        }`;
        this.statusDiv.classList.remove('hidden');
    }

    hideStatus() {
        this.statusDiv.classList.add('hidden');
    }
}

// Initialize scanner when DOM is loaded or immediately if already loaded
function initBarcodeScanner() {
    console.log('🎯 Initializing BarcodeScanner...');
    window.barcodeScanner = new BarcodeScanner();
    console.log('✅ BarcodeScanner initialized');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBarcodeScanner);
} else {
    // DOM is already loaded
    initBarcodeScanner();
}
</script>

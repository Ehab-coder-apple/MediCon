<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\HasRoleBasedRouting;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductController extends Controller
{
    use HasRoleBasedRouting;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['batches', 'category', 'subcategory', 'location'])
            ->withCount('batches');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->get('subcategory_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'low_stock') {
                $query->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM batches WHERE product_id = products.id AND expiry_date > NOW()) <= alert_quantity');
            }
        }

        $products = $query->paginate(15)->withQueryString();

        // Get categories, subcategories, and locations for filter dropdowns
        $categories = \App\Models\Category::active()->ordered()->get();
        $subcategories = \App\Models\Subcategory::active()->ordered()->get();
        $locations = \App\Models\Location::active()->ordered()->get();

        return view('products.index', compact('products', 'categories', 'subcategories', 'locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Product::class);

        $categories = \App\Models\Category::active()->ordered()->get();
        $subcategories = \App\Models\Subcategory::active()->ordered()->get();
        $locations = \App\Models\Location::active()->ordered()->get();

        return view('products.create', compact('categories', 'subcategories', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'location_id' => 'nullable|exists:locations,id',
            'manufacturer' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'alert_quantity' => 'required|integer|min:0',
            'days_on_hand' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // If DOH is not provided, calculate a default based on alert quantity
        if (empty($validated['days_on_hand'])) {
            $validated['days_on_hand'] = max($validated['alert_quantity'] * 2, 30);
        }

        $product = Product::create($validated);

        // Log the product creation with DOH information
        \Log::info('Product created with DOH', [
            'product_id' => $product->id,
            'name' => $product->name,
            'days_on_hand' => $product->days_on_hand,
            'alert_quantity' => $product->alert_quantity
        ]);

        return $this->redirectToIndex('products', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load([
            'batches' => function ($query) {
                $query->orderBy('expiry_date');
            },
            'location',
            'warehouseStocks.warehouse.branch',
        ]);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $categories = \App\Models\Category::active()->ordered()->get();
        $subcategories = \App\Models\Subcategory::active()->ordered()->get();
        $locations = \App\Models\Location::active()->ordered()->get();

        return view('products.edit', compact('product', 'categories', 'subcategories', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'location_id' => 'nullable|exists:locations,id',
            'manufacturer' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code,' . $product->id,
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'alert_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return $this->redirectToIndex('products', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return $this->redirectToIndex('products', 'Product deleted successfully.');
    }

    /**
     * Show the import form
     */
    public function showImport(): View
    {
        $this->authorize('create', Product::class);

        return view('products.import');
    }

    /**
     * Handle the file import
     */
    public function import(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx|max:2048',
            'has_headers' => 'boolean',
            'update_existing' => 'boolean',
        ]);

        // Additional file validation
        $file = $request->file('import_file');

        // Check if file is readable
        if (!$file->isValid()) {
            return redirect()->back()
                ->with('error', 'The uploaded file is corrupted or invalid.')
                ->withInput();
        }

        // Check file size (additional check)
        if ($file->getSize() > 2048 * 1024) { // 2MB in bytes
            return redirect()->back()
                ->with('error', 'File size exceeds 2MB limit.')
                ->withInput();
        }

        // Check if file has content
        if ($file->getSize() < 10) { // Very small files are likely empty
            return redirect()->back()
                ->with('error', 'The uploaded file appears to be empty.')
                ->withInput();
        }

        try {
            $file = $request->file('import_file');
            $hasHeaders = $request->boolean('has_headers', true);
            $updateExisting = $request->boolean('update_existing', false);

            // Store the uploaded file temporarily
            $path = $file->storeAs('temp', 'products_import_' . time() . '.' . $file->getClientOriginalExtension());

            // Process the file based on its type
            $extension = $file->getClientOriginalExtension();
            if (in_array($extension, ['csv', 'txt'])) {
                $result = $this->processCsvFile(storage_path('app/' . $path), $hasHeaders, $updateExisting);
            } elseif ($extension === 'xlsx') {
                $result = $this->processExcelFile(storage_path('app/' . $path), $hasHeaders, $updateExisting);
            }

            // Clean up temporary file
            Storage::delete($path);

            return $this->redirectToIndex('products', "Import completed! {$result['created']} products created, {$result['updated']} updated, {$result['errors']} errors.");

        } catch (\Exception $e) {
            Log::error('Product import failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get template headers and sample data for product import
     */
    private function getImportTemplateData(): array
    {
        $headers = [
            'name',
            'category',
            'manufacturer',
            'code',
            'batch_number',
            'expiry_date',
            'initial_quantity',
            'cost_price',
            'selling_price',
            'alert_quantity',
            'days_on_hand',
            'description',
            'is_active'
        ];

        $sampleData = [
            [
                'Aspirin 325mg Tablets',
                'Pain Relief',
                'Bayer Healthcare',
                'ASP-325-001',
                'BATCH-ASP-001',
                '2025-12-31',
                '100',
                '2.50',
                '4.99',
                '50',
                '30',
                'Pain relief medication',
                '1'
            ],
            [
                'Vitamin C 1000mg',
                'Vitamins',
                'Nature Made',
                'VIT-C-1000',
                'BATCH-VIT-001',
                '2026-06-30',
                '75',
                '8.00',
                '15.99',
                '25',
                '45',
                'Vitamin C supplement',
                '1'
            ]
        ];

        return [$headers, $sampleData];
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
    {
        $this->authorize('create', Product::class);

        [$headers, $sampleData] = $this->getImportTemplateData();

        $filename = 'products_import_template.csv';
        $filePath = storage_path('app/temp/' . $filename);

        // Ensure temp directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $file = fopen($filePath, 'w');
        fputcsv($file, $headers);
        foreach ($sampleData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    /**
     * Download sample Excel template
     */
    public function downloadExcelTemplate()
    {
        $this->authorize('create', Product::class);

        [$headers, $sampleData] = $this->getImportTemplateData();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers in first row
        $sheet->fromArray($headers, null, 'A1');

        // Add sample data starting from second row
        $sheet->fromArray($sampleData, null, 'A2');

        $filename = 'products_import_template.xlsx';
        $filePath = storage_path('app/temp/' . $filename);

        // Ensure temp directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    /**
     * Process CSV file for product import
     */
    private function processCsvFile(string $filePath, bool $hasHeaders, bool $updateExisting): array
    {
        $results = ['created' => 0, 'updated' => 0, 'errors' => 0];
        $errors = [];

        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        // Detect file encoding and convert if necessary
        $content = file_get_contents($filePath);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'UTF-16', 'Windows-1252', 'ISO-8859-1'], true);

        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            file_put_contents($filePath, $content);
        }

        // Try different delimiters to find the correct one
        $delimiters = [',', ';', '\t', '|'];
        $bestDelimiter = ',';
        $maxColumns = 0;

        foreach ($delimiters as $delimiter) {
            $file = fopen($filePath, 'r');
            if ($file) {
                $firstRow = fgetcsv($file, 0, $delimiter);
                if ($firstRow && count($firstRow) > $maxColumns) {
                    $maxColumns = count($firstRow);
                    $bestDelimiter = $delimiter;
                }
                fclose($file);
            }
        }

        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception('Could not open file');
        }

        $rowNumber = 0;
        $headers = null;

        while (($row = fgetcsv($file, 0, $bestDelimiter, '"', '\\')) !== false) {
            $rowNumber++;

            // Skip completely empty rows
            if (empty($row) || (count($row) === 1 && empty(trim($row[0])))) {
                continue;
            }

            // Clean up row data - remove BOM and trim whitespace
            $row = array_map(function($cell) {
                // Remove BOM if present
                $cell = preg_replace('/^\xEF\xBB\xBF/', '', $cell);
                return trim($cell);
            }, $row);

            // Skip header row if specified
            if ($hasHeaders && $rowNumber === 1) {
                $headers = $row;
                continue;
            }

            // Skip rows with insufficient data
            if (count($row) < 9) { // Minimum required columns
                $results['errors']++;
                $errors[] = "Row {$rowNumber}: Insufficient columns (expected at least 9, got " . count($row) . ")";
                continue;
            }

            try {
                $productData = $this->parseProductRow($row, $headers);
                $result = $this->createOrUpdateProduct($productData, $updateExisting);

                if ($result['action'] === 'created') {
                    $results['created']++;
                } elseif ($result['action'] === 'updated') {
                    $results['updated']++;
                }

            } catch (\Exception $e) {
                $results['errors']++;
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();

                Log::warning('Product import row error', [
                    'row' => $rowNumber,
                    'data' => $row,
                    'error' => $e->getMessage()
                ]);
            }
        }

        fclose($file);

        if (!empty($errors)) {
            Log::info('Product import completed with errors', [
                'results' => $results,
                'errors' => $errors
            ]);
        }

        return $results;
    }

    /**
     * Process Excel file for product import (convert to CSV and reuse CSV logic)
     */
    private function processExcelFile(string $filePath, bool $hasHeaders, bool $updateExisting): array
    {
        // Load the Excel file
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Convert sheet data to an array
        $rows = $sheet->toArray(null, false, false, false);

        // Create a temporary CSV file
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempCsvPath = $tempDir . '/products_import_excel_' . time() . '.csv';
        $handle = fopen($tempCsvPath, 'w');

        foreach ($rows as $row) {
            // Normalize row to a simple indexed array for fputcsv
            if (!is_array($row)) {
                $row = [$row];
            }
            fputcsv($handle, $row);
        }

        fclose($handle);

        try {
            // Reuse the existing CSV processing logic
            $result = $this->processCsvFile($tempCsvPath, $hasHeaders, $updateExisting);
        } finally {
            // Clean up temporary CSV file
            if (file_exists($tempCsvPath)) {
                @unlink($tempCsvPath);
            }
        }

        return $result;
    }

    /**
     * Parse a product row from CSV
     */
    private function parseProductRow(array $row, ?array $headers): array
    {
        // Default column mapping (if no headers)
        $defaultMapping = [
            0 => 'name',
            1 => 'category',
            2 => 'manufacturer',
            3 => 'code',
            4 => 'batch_number',
            5 => 'expiry_date',
            6 => 'initial_quantity',
            7 => 'cost_price',
            8 => 'selling_price',
            9 => 'alert_quantity',
            10 => 'days_on_hand',
            11 => 'description',
            12 => 'is_active'
        ];

        $productData = [];

        if ($headers) {
            // Map by header names with flexible matching
            foreach ($headers as $index => $header) {
                $header = strtolower(trim($header));
                // Handle common header variations
                $headerMappings = [
                    'product name' => 'name',
                    'product_name' => 'name',
                    'productname' => 'name',
                    'product code' => 'code',
                    'product_code' => 'code',
                    'productcode' => 'code',
                    'batch number' => 'batch_number',
                    'batch_number' => 'batch_number',
                    'batchnumber' => 'batch_number',
                    'expiry date' => 'expiry_date',
                    'expiry_date' => 'expiry_date',
                    'expirydate' => 'expiry_date',
                    'cost price' => 'cost_price',
                    'cost_price' => 'cost_price',
                    'costprice' => 'cost_price',
                    'selling price' => 'selling_price',
                    'selling_price' => 'selling_price',
                    'sellingprice' => 'selling_price',
                    'alert quantity' => 'alert_quantity',
                    'alert_quantity' => 'alert_quantity',
                    'alertquantity' => 'alert_quantity',
                    'initial quantity' => 'initial_quantity',
                    'initial_quantity' => 'initial_quantity',
                    'initialquantity' => 'initial_quantity',
                    'quantity' => 'initial_quantity',
                    'days on hand' => 'days_on_hand',
                    'days_on_hand' => 'days_on_hand',
                    'daysonhand' => 'days_on_hand',
                    'active' => 'is_active',
                    'is_active' => 'is_active',
                    'isactive' => 'is_active',
                    'status' => 'is_active'
                ];

                $mappedHeader = $headerMappings[$header] ?? $header;

                if (isset($row[$index]) && !empty(trim($row[$index]))) {
                    $productData[$mappedHeader] = trim($row[$index]);
                }
            }
        } else {
            // Map by position
            foreach ($defaultMapping as $index => $field) {
                if (isset($row[$index]) && !empty(trim($row[$index]))) {
                    $productData[$field] = trim($row[$index]);
                }
            }
        }

        // Validate required fields
        $required = ['name', 'category', 'manufacturer', 'code', 'batch_number', 'expiry_date', 'initial_quantity', 'cost_price', 'selling_price'];
        foreach ($required as $field) {
            if (empty($productData[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Validate and clean data types
        try {
            // Clean numeric fields
            if (isset($productData['initial_quantity'])) {
                $productData['initial_quantity'] = (int) preg_replace('/[^0-9]/', '', $productData['initial_quantity']);
                if ($productData['initial_quantity'] <= 0) {
                    throw new \Exception("Initial quantity must be greater than 0");
                }
            }

            if (isset($productData['cost_price'])) {
                $productData['cost_price'] = (float) preg_replace('/[^0-9.]/', '', $productData['cost_price']);
                if ($productData['cost_price'] < 0) {
                    throw new \Exception("Cost price cannot be negative");
                }
            }

            if (isset($productData['selling_price'])) {
                $productData['selling_price'] = (float) preg_replace('/[^0-9.]/', '', $productData['selling_price']);
                if ($productData['selling_price'] < 0) {
                    throw new \Exception("Selling price cannot be negative");
                }
            }

            if (isset($productData['alert_quantity'])) {
                $productData['alert_quantity'] = (int) preg_replace('/[^0-9]/', '', $productData['alert_quantity']);
            } else {
                $productData['alert_quantity'] = 10; // Default value
            }

            if (isset($productData['days_on_hand'])) {
                $productData['days_on_hand'] = (int) preg_replace('/[^0-9]/', '', $productData['days_on_hand']);
            } else {
                $productData['days_on_hand'] = 30; // Default value
            }

            // Validate and format expiry date
            if (isset($productData['expiry_date'])) {
                $date = $productData['expiry_date'];
                // Try different date formats
                $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'Y/m/d'];
                $parsedDate = null;

                foreach ($formats as $format) {
                    $parsedDate = \DateTime::createFromFormat($format, $date);
                    if ($parsedDate !== false) {
                        break;
                    }
                }

                if ($parsedDate === false) {
                    throw new \Exception("Invalid expiry date format: {$date}. Use YYYY-MM-DD format.");
                }

                $productData['expiry_date'] = $parsedDate->format('Y-m-d');
            }

            // Handle boolean fields
            if (isset($productData['is_active'])) {
                $value = strtolower(trim($productData['is_active']));
                $productData['is_active'] = in_array($value, ['1', 'true', 'yes', 'active', 'y']) ? 1 : 0;
            } else {
                $productData['is_active'] = 1; // Default to active
            }

        } catch (\Exception $e) {
            throw new \Exception("Data validation error: " . $e->getMessage());
        }

        // Set defaults for optional fields
        $productData['alert_quantity'] = $productData['alert_quantity'] ?? 10;
        $productData['days_on_hand'] = $productData['days_on_hand'] ?? null;
        $productData['description'] = $productData['description'] ?? null;
        $productData['is_active'] = isset($productData['is_active']) ?
            in_array(strtolower($productData['is_active']), ['1', 'true', 'yes', 'active']) : true;

        // Validate data types
        if (!is_numeric($productData['cost_price']) || $productData['cost_price'] < 0) {
            throw new \Exception('Invalid cost_price: must be a positive number');
        }
        if (!is_numeric($productData['selling_price']) || $productData['selling_price'] < 0) {
            throw new \Exception('Invalid selling_price: must be a positive number');
        }
        if (!is_numeric($productData['alert_quantity']) || $productData['alert_quantity'] < 0) {
            throw new \Exception('Invalid alert_quantity: must be a positive integer');
        }
        if (!is_numeric($productData['initial_quantity']) || $productData['initial_quantity'] < 0) {
            throw new \Exception('Invalid initial_quantity: must be a positive integer');
        }

        // Validate expiry date
        try {
            $expiryDate = \Carbon\Carbon::createFromFormat('Y-m-d', $productData['expiry_date']);
            if ($expiryDate->isPast()) {
                throw new \Exception('Expiry date must be in the future');
            }
        } catch (\Exception $e) {
            throw new \Exception('Invalid expiry_date: must be in YYYY-MM-DD format and in the future');
        }

        return $productData;
    }

    /**
     * Create or update a product with batch information
     */
    private function createOrUpdateProduct(array $productData, bool $updateExisting): array
    {
        return DB::transaction(function () use ($productData, $updateExisting) {
            // Check if product exists by code
            $existingProduct = Product::where('code', $productData['code'])->first();

            if ($existingProduct) {
                if (!$updateExisting) {
                    throw new \Exception("Product with code '{$productData['code']}' already exists");
                }

                // Update existing product
                $existingProduct->update([
                    'name' => $productData['name'],
                    'category' => $productData['category'],
                    'manufacturer' => $productData['manufacturer'],
                    'cost_price' => $productData['cost_price'],
                    'selling_price' => $productData['selling_price'],
                    'alert_quantity' => $productData['alert_quantity'],
                    'days_on_hand' => $productData['days_on_hand'] ?: max($productData['alert_quantity'] * 2, 30),
                    'description' => $productData['description'],
                    'is_active' => $productData['is_active'],
                ]);

                // Create or update batch for existing product
                $this->createOrUpdateBatch($existingProduct, $productData);

                return ['action' => 'updated', 'product' => $existingProduct];
            } else {
                // Create new product
                $product = Product::create([
                    'name' => $productData['name'],
                    'category' => $productData['category'],
                    'manufacturer' => $productData['manufacturer'],
                    'code' => $productData['code'],
                    'cost_price' => $productData['cost_price'],
                    'selling_price' => $productData['selling_price'],
                    'alert_quantity' => $productData['alert_quantity'],
                    'days_on_hand' => $productData['days_on_hand'] ?: max($productData['alert_quantity'] * 2, 30),
                    'description' => $productData['description'],
                    'is_active' => $productData['is_active'],
                ]);

                // Create batch for new product
                $this->createOrUpdateBatch($product, $productData);

                return ['action' => 'created', 'product' => $product];
            }
        });
    }

    /**
     * Create or update batch for a product
     */
    private function createOrUpdateBatch(Product $product, array $productData): Batch
    {
        // Check if batch with same number already exists for this product
        $existingBatch = Batch::where('product_id', $product->id)
            ->where('batch_number', $productData['batch_number'])
            ->first();

        if ($existingBatch) {
            // Update existing batch quantity (add to existing quantity)
            $existingBatch->update([
                'manufacturer' => $productData['manufacturer'],
                'expiry_date' => $productData['expiry_date'],
                'quantity' => $existingBatch->quantity + $productData['initial_quantity'],
                'cost_price' => $productData['cost_price'],
            ]);

            Log::info('Batch updated during product import', [
                'product_id' => $product->id,
                'batch_number' => $productData['batch_number'],
                'added_quantity' => $productData['initial_quantity'],
                'new_total_quantity' => $existingBatch->quantity
            ]);

            return $existingBatch;
        } else {
            // Create new batch
            $batch = Batch::create([
                'product_id' => $product->id,
                'batch_number' => $productData['batch_number'],
                'manufacturer' => $productData['manufacturer'],
                'expiry_date' => $productData['expiry_date'],
                'quantity' => $productData['initial_quantity'],
                'cost_price' => $productData['cost_price'],
            ]);

            Log::info('Batch created during product import', [
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'batch_number' => $productData['batch_number'],
                'quantity' => $productData['initial_quantity']
            ]);

            return $batch;
        }
    }
}

<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\Api\AIDocumentController;
use App\Http\Controllers\Api\ProductInformationController;
use App\Http\Controllers\Api\OpenAIProductController;
use App\Http\Controllers\Api\BarcodeScannerController;
use App\Http\Controllers\Api\QuickSaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Health check endpoint (for testing network connectivity)
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Backend is online',
        'timestamp' => now(),
    ]);
});

// WhatsApp webhook routes (no authentication required)
Route::prefix('whatsapp')->group(function () {
    Route::get('/webhook', [App\Http\Controllers\Api\WhatsAppWebhookController::class, 'verify']);
    Route::post('/webhook', [App\Http\Controllers\Api\WhatsAppWebhookController::class, 'webhook']);
});

// Temporary test login route (without CSRF)
Route::post('/test-login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name,
            'redirect' => $user->is_super_admin ? '/super-admin/dashboard' :
                         ($user->role ? match($user->role->name) {
                             'admin' => '/admin/dashboard',
                             'pharmacist' => '/pharmacist/dashboard',
                             'sales_staff' => '/sales-staff/dashboard',
                             default => '/dashboard'
                         } : '/dashboard')
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Invalid credentials'
    ], 401);
});

// Mobile API login route (returns Sanctum token for use in React Native app)
Route::post('/mobile/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (! Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    /** @var \App\Models\User $user */
    $user = $request->user();

    $token = $user->createToken('mobile')->plainTextToken;

    // Get branches from many-to-many relationship
    $branches = $user->branches()->get();

    // If no branches in many-to-many, fall back to legacy single branch
    if ($branches->isEmpty() && $user->branch) {
        $branches = collect([$user->branch]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name,
            'is_super_admin' => $user->is_super_admin,
        ],
        'branches' => $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'latitude' => $branch->latitude,
                'longitude' => $branch->longitude,
                'geofence_radius' => $branch->geofence_radius,
                'address' => $branch->full_address,
                'code' => $branch->code,
            ];
        })->values(),
    ]);
});


// Category API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}/subcategories', [CategoryController::class, 'subcategories']);
    Route::get('/subcategories', [CategoryController::class, 'allSubcategories']);
    Route::get('/categories/search', [CategoryController::class, 'search']);

    // Location API routes
    Route::get('/locations', function() {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Location::active()->ordered()->get()
        ]);
    });
    Route::get('/locations/zones', function() {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Location::getZones()
        ]);
    });

    // Branch API routes (for mobile app)
    Route::get('/branches', [BranchApiController::class, 'index']);
    Route::get('/branches/{branch}', [BranchApiController::class, 'show']);
    Route::post('/branches/{branch}/set-location', [BranchApiController::class, 'setLocation']);

    // Attendance API routes (for mobile app)
    Route::prefix('attendance')->group(function () {
        Route::post('/check-in', [AttendanceApiController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceApiController::class, 'checkOut']);
        Route::post('/break-start', [AttendanceApiController::class, 'breakStart']);
        Route::post('/break-end', [AttendanceApiController::class, 'breakEnd']);
        Route::get('/today', [AttendanceApiController::class, 'getTodayStatus']);
        Route::get('/branch', [AttendanceApiController::class, 'getBranch']);
        Route::get('/my-branches', [AttendanceApiController::class, 'getMyBranches']);
    });

    // Leave API routes (for mobile app)
    Route::prefix('leaves')->group(function () {
        Route::get('/types', [App\Http\Controllers\Api\LeaveApiController::class, 'getLeaveTypes']);
        Route::post('/apply', [App\Http\Controllers\Api\LeaveApiController::class, 'applyLeave']);
        Route::get('/my-leaves', [App\Http\Controllers\Api\LeaveApiController::class, 'getMyLeaves']);
        Route::get('/{id}', [App\Http\Controllers\Api\LeaveApiController::class, 'getLeaveDetails']);
        Route::post('/{id}/cancel', [App\Http\Controllers\Api\LeaveApiController::class, 'cancelLeave']);
    });

    // AI Document Processing API routes
    Route::prefix('ai')->group(function () {
        // Document upload and processing
        Route::post('/documents/upload', [AIDocumentController::class, 'upload']);
        Route::get('/documents/{documentId}/status', [AIDocumentController::class, 'getStatus']);

        // Invoice processing
        Route::get('/invoices/{invoiceId}', [AIDocumentController::class, 'getInvoice']);
        Route::post('/invoices/{invoiceId}/approve', [AIDocumentController::class, 'approveInvoice']);
        Route::post('/invoices/{invoiceId}/approve-for-processing', [AIDocumentController::class, 'approveForProcessing']);
        Route::post('/invoices/{invoiceId}/approve-for-inventory', [AIDocumentController::class, 'approveForInventory']);
        // PDF Upload and Item Extraction
        Route::post('/invoices/{invoiceId}/upload-pdf', [AIDocumentController::class, 'uploadInvoicePDF']);
        Route::post('/invoices/{invoiceId}/convert-to-items', [AIDocumentController::class, 'convertInvoiceToItems']);
        Route::post('/invoices/{invoiceId}/transfer-to-warehouse', [AIDocumentController::class, 'transferToWarehouse']);
        Route::get('/invoices/{invoiceId}/available-warehouses', [AIDocumentController::class, 'getAvailableWarehouses']);

        // Prescription checking
        Route::get('/prescriptions/{checkId}', [AIDocumentController::class, 'getPrescription']);

        // Product information and alternatives
        Route::get('/products/search', [ProductInformationController::class, 'search']);
        Route::get('/products/{productId}/info', [ProductInformationController::class, 'getProductInfo']);
        Route::get('/products/{productId}/alternatives', [ProductInformationController::class, 'getAlternatives']);
        Route::post('/products/{productId}/info', [ProductInformationController::class, 'updateProductInfo']);

        // OpenAI Product Information
        Route::post('/openai/product-info', [OpenAIProductController::class, 'getProductInfo']);
    });

    // Barcode Scanner API routes (outside of /ai prefix)
    Route::prefix('products')->group(function () {
        Route::get('/by-barcode/{barcode}', [BarcodeScannerController::class, 'lookupByBarcode']);
        Route::get('/{productId}/details', [BarcodeScannerController::class, 'getProductDetails']);
        Route::post('/{productId}/check-stock', [BarcodeScannerController::class, 'checkStock']);
    });

    // Quick Sale API routes (for barcode scanner checkout)
    Route::prefix('sales')->group(function () {
        Route::post('/quick-create', [QuickSaleController::class, 'quickCreate']);
        Route::get('/{saleId}', [QuickSaleController::class, 'getSale']);
    });
});

// API endpoint to get all warehouses for inventory returns
Route::get('/all-warehouses', function () {
    $tenantId = auth()->check() ? auth()->user()->tenant_id : 1;

    $warehouses = \App\Models\Warehouse::where('tenant_id', $tenantId)
        ->get()
        ->map(function ($warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'type' => $warehouse->type,
            ];
        });

    return response()->json([
        'warehouses' => $warehouses,
    ]);
});

// API endpoint to get all products and batches for inventory returns
Route::get('/all-products-batches', function () {
    $products = \App\Models\Product::all()->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
        ];
    });

    $batches = \App\Models\Batch::all()->map(function ($batch) {
        return [
            'id' => $batch->id,
            'product_id' => $batch->product_id,
            'batch_number' => $batch->batch_number,
            'expiry_date' => $batch->expiry_date->format('Y-m-d'),
            'cost_price' => $batch->cost_price,
        ];
    });

    return response()->json([
        'products' => $products,
        'batches' => $batches,
    ]);
});

// Warehouse Stock API routes (for inventory returns) - Outside of auth middleware
Route::get('/warehouse/{warehouse}/stock', function ($warehouseId) {
    $stocks = \App\Models\WarehouseStock::where('warehouse_id', $warehouseId)
        ->with(['product', 'batch'])
        ->get()
        ->map(function ($stock) {
            return [
                'id' => $stock->id,
                'product' => [
                    'id' => $stock->product->id,
                    'name' => $stock->product->name,
                    'code' => $stock->product->code,
                ],
                'batch' => [
                    'id' => $stock->batch->id,
                    'batch_number' => $stock->batch->batch_number,
                    'expiry_date' => $stock->batch->expiry_date->format('Y-m-d'),
                    'cost_price' => $stock->batch->cost_price,
                ],
                'quantity' => $stock->quantity,
            ];
        });

    return response()->json($stocks);
});

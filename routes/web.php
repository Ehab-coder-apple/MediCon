<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PharmacistDashboardController;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\InventoryReturnController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockReceivingController;
use App\Http\Controllers\SalesStaffDashboardController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantRoleController;
use App\Http\Controllers\CodeGeneratorController;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\SuperAdminReportsController;
use App\Http\Controllers\TenantWhatsAppSettingsController;
use App\Http\Controllers\TenantWhatsAppSettingsDualModeController;
use App\Http\Controllers\AIManagementController;
use App\Http\Controllers\Admin\ProductDisplaySettingsController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Simple test login page
Route::get('/simple-login', function () {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Simple Login Test</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
            input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
            button { width: 100%; padding: 12px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; }
            button:hover { background: #005a87; }
            .error { color: red; margin: 10px 0; }
            .success { color: green; margin: 10px 0; }
        </style>
    </head>
    <body>
        <h2>Simple Login Test</h2>
        <form method="POST" action="/simple-login">
            ' . csrf_field() . '
            <input type="email" name="email" placeholder="Email" value="michael.admin@medicon.com" required>
            <input type="password" name="password" placeholder="Password" value="password" required>
            <button type="submit">Login</button>
        </form>
        <p><small>Pre-filled with test credentials</small></p>
    </body>
    </html>';
});

// Simple test login handler
Route::post('/simple-login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $redirectUrl = $user->is_super_admin ? '/super-admin/dashboard' :
                      ($user->role ? match($user->role->name) {
                          'admin' => '/admin/dashboard',
                          'pharmacist' => '/pharmacist/dashboard',
                          'sales_staff' => '/sales-staff/dashboard',
                          default => '/dashboard'
                      } : '/dashboard');

        return redirect($redirectUrl)->with('success', 'Login successful!');
    }

    return back()->with('error', 'Invalid credentials');
});

// Quick login helper for testing (remove in production)
Route::get('/quick-login/{email}', function ($email) {
    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        auth()->login($user);
        return redirect('/dashboard')->with('success', 'Logged in as ' . $user->name);
    }
    return redirect('/login')->with('error', 'User not found');
})->name('quick-login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    // Default dashboard route - redirects based on user role
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Super admin gets their own dashboard
        if ($user->is_super_admin) {
            return redirect()->route('super-admin.dashboard');
        }

        // Check if user has a role assigned
        if (!$user->role) {
            abort(403, 'No role assigned to user. Please contact your administrator.');
        }

        // Redirect based on role name
        return match($user->role->name) {
            \App\Models\Role::ADMIN => redirect()->route('admin.dashboard'),
            \App\Models\Role::PHARMACIST => redirect()->route('pharmacist.dashboard'),
            \App\Models\Role::SALES_STAFF => redirect()->route('sales-staff.dashboard'),
            default => abort(403, 'Invalid role: ' . $user->role->name),
        };
    })->name('dashboard');

    // Super Admin Routes
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

        // Tenant Management
        Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('tenants.index');
        Route::get('/tenants/create', [SuperAdminController::class, 'createTenant'])->name('tenants.create');
        Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [SuperAdminController::class, 'showTenant'])->name('tenants.show');
        Route::get('/tenants/{tenant}/edit', [SuperAdminController::class, 'editTenant'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [SuperAdminController::class, 'updateTenant'])->name('tenants.update');
        Route::delete('/tenants/{tenant}', [SuperAdminController::class, 'deleteTenant'])->name('tenants.delete');

        // Tenant Actions
        Route::post('/tenants/{tenant}/suspend', [SuperAdminController::class, 'suspendTenant'])->name('tenants.suspend');
        Route::post('/tenants/{tenant}/activate', [SuperAdminController::class, 'activateTenant'])->name('tenants.activate');
        Route::post('/tenants/{tenant}/impersonate', [SuperAdminController::class, 'impersonateTenant'])->name('impersonate-tenant');
        Route::post('/stop-impersonating', [SuperAdminController::class, 'stopImpersonating'])->name('stop-impersonating');

        // Platform Management
        Route::get('/analytics', [SuperAdminController::class, 'analytics'])->name('analytics');
        Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');

        // Business Reports
        Route::get('/reports', [SuperAdminReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/tenant-details', [SuperAdminReportsController::class, 'tenantDetails'])->name('reports.tenant-details');
        Route::post('/reports/export', [SuperAdminReportsController::class, 'exportReports'])->name('reports.export');
        Route::get('/reports/financial', [SuperAdminReportsController::class, 'financialOverview'])->name('reports.financial');
    });

    // Tenant Role Management Routes
    Route::prefix('tenant')->name('tenant.')->group(function () {
        Route::resource('roles', TenantRoleController::class);
        Route::post('/roles/{role}/assign-user', [TenantRoleController::class, 'assignToUser'])->name('roles.assign-user');
        Route::post('/roles/remove-user', [TenantRoleController::class, 'removeFromUser'])->name('roles.remove-user');
        Route::post('/roles/{role}/clone', [TenantRoleController::class, 'clone'])->name('roles.clone');
        Route::get('/permissions', [TenantRoleController::class, 'getPermissions'])->name('permissions');
        Route::post('/roles/{role}/bulk-permissions', [TenantRoleController::class, 'bulkAssignPermissions'])->name('roles.bulk-permissions');
    });



    // Analytics quick stats for dashboard
    Route::get('/analytics/quick-stats', [AnalyticsController::class, 'getQuickStats'])->name('analytics.quick-stats');

    // WhatsApp messaging routes (available to all authenticated users)
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [App\Http\Controllers\WhatsAppController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\WhatsAppController::class, 'create'])->name('create');
        Route::post('/preview', [App\Http\Controllers\WhatsAppController::class, 'preview'])->name('preview');
        Route::post('/send', [App\Http\Controllers\WhatsAppController::class, 'store'])->name('send');
        Route::get('/bulk', [App\Http\Controllers\WhatsAppController::class, 'bulk'])->name('bulk');
        Route::post('/bulk/send', [App\Http\Controllers\WhatsAppController::class, 'sendBulk'])->name('bulk.send');
        Route::get('/history', [App\Http\Controllers\WhatsAppController::class, 'history'])->name('history');
        Route::get('/template/{template}', [App\Http\Controllers\WhatsAppController::class, 'getTemplate'])->name('template');
    });

    // Invoice management routes (available to all authenticated users)
    Route::resource('invoices', App\Http\Controllers\InvoiceController::class);
    Route::post('/invoices/{invoice}/payment', [App\Http\Controllers\InvoiceController::class, 'processPayment'])->name('invoices.process-payment');
    Route::post('/invoices/{invoice}/mark-sent', [App\Http\Controllers\InvoiceController::class, 'markAsSent'])->name('invoices.mark-sent');
    Route::post('/invoices/{invoice}/cancel', [App\Http\Controllers\InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('/invoices/{invoice}/send-whatsapp', [App\Http\Controllers\InvoiceController::class, 'sendViaWhatsApp'])->name('invoices.send-whatsapp');
    Route::get('/products/{product}/details', [App\Http\Controllers\InvoiceController::class, 'getProduct'])->name('products.details');

    // Product lookup for barcode scanner (available to all authenticated users)
    Route::get('/sales/product-lookup', [SaleController::class, 'getProductDetails'])->name('sales.product-lookup');

    // Admin routes
    Route::middleware(['can:access-admin-dashboard'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');

        // Admin User Management Routes
        Route::get('/users/create', [TenantRegistrationController::class, 'showCreateUser'])->name('users.create');
        Route::post('/users', [TenantRegistrationController::class, 'createUser'])->name('users.store');
        Route::get('/users/{user}/edit', [TenantRegistrationController::class, 'showEditUser'])->name('users.edit');
        Route::put('/users/{user}', [TenantRegistrationController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [TenantRegistrationController::class, 'deleteUser'])->name('users.destroy');

        Route::resource('products', ProductController::class);
        Route::get('products-import', [ProductController::class, 'showImport'])->name('products.import');
        Route::post('products-import', [ProductController::class, 'import'])->name('products.import.process');
        Route::get('products-template', [ProductController::class, 'downloadTemplate'])->name('products.template');
        Route::get('products-template-excel', [ProductController::class, 'downloadExcelTemplate'])->name('products.template.excel');
        Route::resource('categories', CategoryController::class);
        Route::resource('subcategories', SubcategoryController::class);
        Route::resource('locations', LocationController::class);
        Route::resource('batches', BatchController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('warehouses', App\Http\Controllers\WarehouseController::class);
        Route::resource('stock-transfers', App\Http\Controllers\StockTransferController::class)->only(['index', 'create', 'store', 'show']);

        // Purchase routes - custom routes must come before resource route
        Route::get('/purchases/auto-generate/show', [PurchaseController::class, 'showAutoGenerate'])->name('purchases.auto-generate.show');
        Route::post('/purchases/auto-generate', [PurchaseController::class, 'generateAutoPurchaseOrders'])->name('purchases.auto-generate');
        Route::post('/purchases/{purchase}/complete', [PurchaseController::class, 'complete'])->name('purchases.complete');
        Route::get('/purchases/{purchase}/export-pdf', [PurchaseController::class, 'exportPdf'])->name('purchases.export-pdf');
        Route::get('/purchases/{purchase}/receive-stock', [PurchaseController::class, 'showReceiveStock'])->name('purchases.receive-stock');
        Route::post('/purchases/{purchase}/receive-stock', [PurchaseController::class, 'processReceiveStock'])->name('purchases.process-receive-stock');
        Route::resource('purchases', PurchaseController::class);

        // Purchase Return routes
        Route::get('/purchases/{purchase}/return', [PurchaseReturnController::class, 'create'])->name('purchases.return.create');
        Route::post('/purchases/{purchase}/return', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
        Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'show']);

        // Inventory Return routes
        Route::resource('inventory-returns', InventoryReturnController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

        Route::resource('sales', SaleController::class);
        Route::resource('customers', CustomerController::class);
        Route::get('/products/{product}/details', [PurchaseController::class, 'getProductDetails'])->name('products.details');
        Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
        Route::get('/sales-summary/today', [SaleController::class, 'todaySummary'])->name('sales.today-summary');
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');

        // Sales Return routes
        Route::get('/invoices/{invoice}/return', [SalesReturnController::class, 'create'])->name('invoices.return.create');
        Route::post('/invoices/{invoice}/return', [SalesReturnController::class, 'store'])->name('sales-returns.store');
        Route::resource('sales-returns', SalesReturnController::class)->only(['index', 'show', 'destroy']);
        Route::get('/inventory', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
        Route::get('/inventory/report', [InventoryController::class, 'report'])->name('inventory.report');
        Route::get('/inventory/alerts', [InventoryController::class, 'alerts'])->name('inventory.alerts');

        // Stock Receiving
        Route::get('/stock-receiving', [StockReceivingController::class, 'index'])->name('stock-receiving.index');
        Route::get('/stock-receiving/create', [StockReceivingController::class, 'create'])->name('stock-receiving.create');
        Route::post('/stock-receiving', [StockReceivingController::class, 'store'])->name('stock-receiving.store');
        Route::get('/stock-receiving/{stockReceiving}', [StockReceivingController::class, 'show'])->name('stock-receiving.show');
        Route::get('/products/{product}/quick-add-stock', [StockReceivingController::class, 'quickAdd'])->name('stock-receiving.quick-add');
        Route::post('/products/{product}/quick-add-stock', [StockReceivingController::class, 'processQuickAdd'])->name('stock-receiving.process-quick-add');
        Route::get('/api/products/{product}/details', [StockReceivingController::class, 'getProductDetails'])->name('api.products.details');

        // Attendance Management Routes
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [App\Http\Controllers\AttendanceController::class, 'index'])->name('index');
            Route::get('/{attendance}', [App\Http\Controllers\AttendanceController::class, 'show'])->name('show');
            Route::get('/export/csv', [App\Http\Controllers\AttendanceController::class, 'export'])->name('export');
            Route::get('/statistics/view', [App\Http\Controllers\AttendanceController::class, 'statistics'])->name('statistics');
        });

        // Leave Management Routes
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::get('/', [App\Http\Controllers\LeaveManagementController::class, 'index'])->name('index');
            Route::get('/{leave}', [App\Http\Controllers\LeaveManagementController::class, 'show'])->name('show');
            Route::post('/{leave}/approve', [App\Http\Controllers\LeaveManagementController::class, 'approve'])->name('approve');
            Route::post('/{leave}/reject', [App\Http\Controllers\LeaveManagementController::class, 'reject'])->name('reject');
            Route::get('/export/csv', [App\Http\Controllers\LeaveManagementController::class, 'export'])->name('export');
        });

        // Branch Management Routes
        Route::resource('branches', App\Http\Controllers\BranchManagementController::class);

        // AI Management Routes
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/dashboard', [AIManagementController::class, 'dashboard'])->name('dashboard');

            // Invoice Processing
            Route::prefix('invoices')->name('invoices.')->group(function () {
                Route::get('/', [AIManagementController::class, 'invoices'])->name('index');
                    Route::get('/{id}', [AIManagementController::class, 'showInvoice'])->name('show');
                    Route::get('/{id}/document', [AIManagementController::class, 'viewOriginalDocument'])->name('view-document');
                Route::post('/{id}/approve-for-processing', [AIManagementController::class, 'approveForProcessing'])->name('approve-for-processing');
                Route::post('/{id}/approve-for-inventory', [AIManagementController::class, 'approveForInventory'])->name('approve-for-inventory');
                // PDF Upload and Item Extraction
                Route::post('/{id}/upload-pdf', [AIManagementController::class, 'uploadInvoicePDF'])->name('upload-pdf');
                Route::post('/{id}/convert-to-items', [AIManagementController::class, 'convertToItems'])->name('convert-to-items');
                Route::get('/{id}/select-warehouse', [AIManagementController::class, 'selectWarehouse'])->name('select-warehouse');
                Route::post('/{id}/approve-warehouse-transfer', [AIManagementController::class, 'approveWarehouseTransfer'])->name('approve-warehouse-transfer');
            });

            // Prescription Checking
            Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
                Route::get('/', [AIManagementController::class, 'prescriptions'])->name('index');
                Route::get('/{id}', [AIManagementController::class, 'showPrescription'])->name('show');
            });

            // Product Information
            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/', [AIManagementController::class, 'products'])->name('index');
                Route::get('/{id}', [AIManagementController::class, 'showProduct'])->name('show');
                Route::get('/{id}/edit', [AIManagementController::class, 'editProduct'])->name('edit');
                Route::put('/{id}', [AIManagementController::class, 'updateProduct'])->name('update');
            });
        });

        // Admin analytics
        Route::get('/analytics', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
        Route::get('/analytics/product-movement', [AnalyticsController::class, 'productMovement'])->name('analytics.product-movement');
        Route::get('/analytics/product-movement/data', [AnalyticsController::class, 'getProductMovementData'])->name('analytics.product-movement.data');
        Route::get('/analytics/expiry-alerts', [AnalyticsController::class, 'expiryAlerts'])->name('analytics.expiry-alerts');
        Route::get('/analytics/expiry-alerts/data', [AnalyticsController::class, 'getExpiryAlertsData'])->name('analytics.expiry-alerts.data');
        Route::get('/analytics/sales', [AnalyticsController::class, 'salesAnalytics'])->name('analytics.sales');
        Route::get('/analytics/sales/data', [AnalyticsController::class, 'getSalesData'])->name('analytics.sales.data');
        Route::get('/analytics/suppliers', [AnalyticsController::class, 'supplierAnalytics'])->name('analytics.suppliers');
        Route::get('/analytics/suppliers/data', [AnalyticsController::class, 'getSupplierData'])->name('analytics.suppliers.data');

        // Reports routes
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportsController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportsController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/customers', [ReportsController::class, 'customers'])->name('reports.customers');
        Route::get('/reports/financial', [ReportsController::class, 'financial'])->name('reports.financial');
        Route::get('/reports/export/sales', [ReportsController::class, 'exportSales'])->name('reports.export.sales');
        Route::get('/reports/export/sales-excel', [ReportsController::class, 'exportSalesExcel'])->name('reports.export.sales.excel');
        Route::get('/reports/export/sales-detailed-excel', [ReportsController::class, 'exportDetailedSalesExcel'])->name('reports.export.sales.detailed.excel');
        Route::get('/reports/export/sales-comprehensive-excel', [ReportsController::class, 'exportComprehensiveSalesExcel'])->name('reports.export.sales.comprehensive.excel');
        Route::get('/reports/export/profit-analysis-excel', [ReportsController::class, 'exportProfitAnalysisExcel'])->name('reports.export.profit.analysis.excel');

        // System Settings routes
        Route::get('/settings', [SystemSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SystemSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/backup', [SystemSettingsController::class, 'backup'])->name('settings.backup');
        Route::post('/settings/clear-cache', [SystemSettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/settings/optimize', [SystemSettingsController::class, 'optimize'])->name('settings.optimize');
        Route::post('/settings/test-email', [SystemSettingsController::class, 'testEmail'])->name('settings.test-email');
        Route::get('/settings/logs', [SystemSettingsController::class, 'logs'])->name('settings.logs');
        Route::post('/settings/clear-logs', [SystemSettingsController::class, 'clearLogs'])->name('settings.clear-logs');

        // Product Display Settings routes
        Route::get('/product-display-settings', [ProductDisplaySettingsController::class, 'index'])->name('product-display-settings.index');
        Route::put('/product-display-settings/strategy', [ProductDisplaySettingsController::class, 'updateStrategy'])->name('product-display-settings.update-strategy');
        Route::post('/product-display-settings/featured', [ProductDisplaySettingsController::class, 'addFeaturedProduct'])->name('product-display-settings.add-featured');
        Route::delete('/product-display-settings/featured/{featuredProduct}', [ProductDisplaySettingsController::class, 'removeFeaturedProduct'])->name('product-display-settings.remove-featured');
        Route::post('/product-display-settings/featured/reorder', [ProductDisplaySettingsController::class, 'reorderFeaturedProducts'])->name('product-display-settings.reorder-featured');

        // Activity Logs routes
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::post('/activity-logs/clear-old', [ActivityLogController::class, 'clearOld'])->name('activity-logs.clear-old');

        // WhatsApp Settings routes (Legacy)
        Route::get('/settings/whatsapp', [TenantWhatsAppSettingsController::class, 'show'])->name('whatsapp.show');
        Route::post('/settings/whatsapp', [TenantWhatsAppSettingsController::class, 'store'])->name('whatsapp.store');
        Route::post('/settings/whatsapp/test', [TenantWhatsAppSettingsController::class, 'test'])->name('whatsapp.test');
        Route::post('/settings/whatsapp/enable', [TenantWhatsAppSettingsController::class, 'enable'])->name('whatsapp.enable');
        Route::post('/settings/whatsapp/disable', [TenantWhatsAppSettingsController::class, 'disable'])->name('whatsapp.disable');

        // WhatsApp Dual-Mode Settings routes
        Route::get('/settings/whatsapp/mode-selection', [TenantWhatsAppSettingsDualModeController::class, 'selectMode'])->name('tenant.whatsapp.select-mode');
        Route::post('/settings/whatsapp/mode-selection', [TenantWhatsAppSettingsDualModeController::class, 'storeMode'])->name('tenant.whatsapp.store-mode');
        Route::get('/settings/whatsapp/configure-business-free', [TenantWhatsAppSettingsDualModeController::class, 'configureBusinessFree'])->name('tenant.whatsapp.configure-business-free');
        Route::post('/settings/whatsapp/store-business-free', [TenantWhatsAppSettingsDualModeController::class, 'storeBusinessFreeCredentials'])->name('tenant.whatsapp.store-business-free');
        Route::get('/settings/whatsapp/configure-api', [TenantWhatsAppSettingsDualModeController::class, 'configureApi'])->name('tenant.whatsapp.configure-api');
        Route::post('/settings/whatsapp/store-api', [TenantWhatsAppSettingsDualModeController::class, 'storeApiCredentials'])->name('tenant.whatsapp.store-api');

        // Code Generator routes
        Route::get('/code-generator', [CodeGeneratorController::class, 'index'])->name('code-generator.index');
        Route::post('/code-generator/single', [CodeGeneratorController::class, 'generateSingle'])->name('code-generator.single');
        Route::post('/code-generator/bulk', [CodeGeneratorController::class, 'generateBulk'])->name('code-generator.bulk');
        Route::post('/code-generator/validate', [CodeGeneratorController::class, 'validateCode'])->name('code-generator.validate');
        Route::get('/code-generator/preview', [CodeGeneratorController::class, 'previewNext'])->name('code-generator.preview');
        Route::get('/code-generator/statistics', [CodeGeneratorController::class, 'getStatistics'])->name('code-generator.statistics');
    });

    // Pharmacist routes
    Route::middleware(['can:access-pharmacist-dashboard'])->prefix('pharmacist')->name('pharmacist.')->group(function () {
        Route::get('/dashboard', [PharmacistDashboardController::class, 'index'])->name('dashboard');
        Route::get('/inventory', [PharmacistDashboardController::class, 'inventory'])->name('inventory');

        Route::resource('products', ProductController::class);
        Route::get('products-import', [ProductController::class, 'showImport'])->name('products.import');
        Route::post('products-import', [ProductController::class, 'import'])->name('products.import.process');
        Route::get('products-template', [ProductController::class, 'downloadTemplate'])->name('products.template');
        Route::get('products-template-excel', [ProductController::class, 'downloadExcelTemplate'])->name('products.template.excel');
        Route::resource('batches', BatchController::class);
        Route::resource('suppliers', SupplierController::class);

        // Purchase routes - custom routes must come before resource route
        Route::get('/purchases/auto-generate/show', [PurchaseController::class, 'showAutoGenerate'])->name('purchases.auto-generate.show');
        Route::post('/purchases/auto-generate', [PurchaseController::class, 'generateAutoPurchaseOrders'])->name('purchases.auto-generate');
        Route::post('/purchases/{purchase}/complete', [PurchaseController::class, 'complete'])->name('purchases.complete');
        Route::get('/purchases/{purchase}/export-pdf', [PurchaseController::class, 'exportPdf'])->name('purchases.export-pdf');
        Route::get('/purchases/{purchase}/receive-stock', [PurchaseController::class, 'showReceiveStock'])->name('purchases.receive-stock');
        Route::post('/purchases/{purchase}/receive-stock', [PurchaseController::class, 'processReceiveStock'])->name('purchases.process-receive-stock');
        Route::resource('purchases', PurchaseController::class);

        // Purchase Return routes
        Route::get('/purchases/{purchase}/return', [PurchaseReturnController::class, 'create'])->name('purchases.return.create');
        Route::post('/purchases/{purchase}/return', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
        Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'show']);

        // Inventory Return routes
        Route::resource('inventory-returns', InventoryReturnController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

        Route::resource('sales', SaleController::class);
        Route::resource('customers', CustomerController::class);
        Route::get('/products/{product}/details', [PurchaseController::class, 'getProductDetails'])->name('products.details');
        Route::get('/sales/product-lookup', [SaleController::class, 'getProductDetails'])->name('sales.product-lookup');
        Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
        Route::get('/sales-summary/today', [SaleController::class, 'todaySummary'])->name('sales.today-summary');
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');

        // Sales Return routes
        Route::get('/invoices/{invoice}/return', [SalesReturnController::class, 'create'])->name('invoices.return.create');
        Route::post('/invoices/{invoice}/return', [SalesReturnController::class, 'store'])->name('sales-returns.store');
        Route::resource('sales-returns', SalesReturnController::class)->only(['index', 'show', 'destroy']);
        Route::get('/inventory-dashboard', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
        Route::get('/inventory/report', [InventoryController::class, 'report'])->name('inventory.report');
        Route::get('/inventory/alerts', [InventoryController::class, 'alerts'])->name('inventory.alerts');

        // Pharmacist analytics
        Route::get('/analytics', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
        Route::get('/analytics/expiry-alerts', [AnalyticsController::class, 'expiryAlerts'])->name('analytics.expiry-alerts');
        Route::get('/analytics/expiry-alerts/data', [AnalyticsController::class, 'getExpiryAlertsData'])->name('analytics.expiry-alerts.data');
        Route::get('/analytics/sales', [AnalyticsController::class, 'salesAnalytics'])->name('analytics.sales');
        Route::get('/analytics/sales/data', [AnalyticsController::class, 'getSalesData'])->name('analytics.sales.data');

        // Code Generator routes for Pharmacists
        Route::get('/code-generator', [CodeGeneratorController::class, 'index'])->name('code-generator.index');
        Route::post('/code-generator/single', [CodeGeneratorController::class, 'generateSingle'])->name('code-generator.single');
        Route::post('/code-generator/bulk', [CodeGeneratorController::class, 'generateBulk'])->name('code-generator.bulk');
        Route::post('/code-generator/validate', [CodeGeneratorController::class, 'validateCode'])->name('code-generator.validate');
        Route::get('/code-generator/preview', [CodeGeneratorController::class, 'previewNext'])->name('code-generator.preview');
        Route::get('/code-generator/statistics', [CodeGeneratorController::class, 'getStatistics'])->name('code-generator.statistics');
    });

    // Sales Staff routes
    Route::middleware(['can:access-sales-dashboard'])->prefix('sales-staff')->name('sales-staff.')->group(function () {
        Route::get('/dashboard', [SalesStaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/sales-history', [SalesStaffDashboardController::class, 'sales'])->name('sales');
        Route::get('/orders', [SalesStaffDashboardController::class, 'orders'])->name('orders');
        Route::resource('sales', SaleController::class);
        Route::resource('customers', CustomerController::class);
        Route::get('/sales/product-lookup', [SaleController::class, 'getProductDetails'])->name('sales.product-lookup');
        Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
        Route::get('/sales-summary/today', [SaleController::class, 'todaySummary'])->name('sales.today-summary');
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/inventory', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
        Route::get('/inventory/report', [InventoryController::class, 'report'])->name('inventory.report');

        // Sales staff analytics
        Route::get('/analytics', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
        Route::get('/analytics/sales', [AnalyticsController::class, 'salesAnalytics'])->name('analytics.sales');
        Route::get('/analytics/sales/data', [AnalyticsController::class, 'getSalesData'])->name('analytics.sales.data');

        // Code Generator routes for Sales Staff
        Route::get('/code-generator', [CodeGeneratorController::class, 'index'])->name('code-generator.index');
        Route::post('/code-generator/single', [CodeGeneratorController::class, 'generateSingle'])->name('code-generator.single');
        Route::post('/code-generator/bulk', [CodeGeneratorController::class, 'generateBulk'])->name('code-generator.bulk');
        Route::post('/code-generator/validate', [CodeGeneratorController::class, 'validateCode'])->name('code-generator.validate');
        Route::get('/code-generator/preview', [CodeGeneratorController::class, 'previewNext'])->name('code-generator.preview');
        Route::get('/code-generator/statistics', [CodeGeneratorController::class, 'getStatistics'])->name('code-generator.statistics');
    });
});

// Documentation Routes
Route::get('/docs/{file}', function ($file) {
    $allowedFiles = [
        'admin-guide' => 'ADMIN_GUIDE_PDF_READY.md',
        'quick-reference' => 'ADMIN_QUICK_REFERENCE.md',
        'troubleshooting' => 'ADMIN_TROUBLESHOOTING_FAQ.md',
        'training' => 'ADMIN_TRAINING_GUIDE.md',
        'index' => 'ADMIN_DOCUMENTATION_INDEX.md',
        'start-here' => 'ADMIN_DOCS_START_HERE.md',
    ];

    if (!isset($allowedFiles[$file])) {
        abort(404, 'Documentation file not found');
    }

    $filePath = base_path($allowedFiles[$file]);

    if (!file_exists($filePath)) {
        abort(404, 'File not found: ' . $filePath);
    }

    $content = file_get_contents($filePath);

    // Simple markdown to HTML conversion
    $html = nl2br(htmlspecialchars($content));

    return view('documentation', [
        'title' => $file,
        'content' => $content,
        'html' => $html
    ]);
})->name('docs.view');



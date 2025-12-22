<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log an activity
     */
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $entityName = null,
        ?string $description = null,
        ?array $changes = null,
        string $category = 'system',
        string $severity = 'info',
        ?int $tenantId = null
    ): ActivityLog {
        $user = Auth::user();

        // Try to get tenant_id from multiple sources
        // If no tenant_id was passed, try to resolve it
        if (!$tenantId) {
            // Try the user's tenant_id (for non-super-admin users)
            if ($user?->tenant_id) {
                $tenantId = $user->tenant_id;
            }

            // Try the current_tenant from the container
            if (!$tenantId && app()->bound('current_tenant')) {
                $currentTenant = app('current_tenant');
                $tenantId = $currentTenant?->id;
            }

            // Finally try the session
            if (!$tenantId) {
                $tenantId = session('tenant_id');
            }

            // Last resort: get the first active tenant
            if (!$tenantId) {
                $tenant = \App\Models\Tenant::where('is_active', true)->first();
                if ($tenant) {
                    $tenantId = $tenant->id;
                }
            }
        }

        return ActivityLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_name' => $entityName,
            'description' => $description,
            'changes' => $changes,
            'category' => $category,
            'severity' => $severity,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a sale
     */
    public static function logSale(string $action, $sale, ?array $changes = null): ActivityLog
    {
        $description = match($action) {
            'created' => "Sale #{$sale->invoice_number} created for {$sale->customer->name}",
            'updated' => "Sale #{$sale->invoice_number} updated",
            'deleted' => "Sale #{$sale->invoice_number} deleted",
            'completed' => "Sale #{$sale->invoice_number} completed",
            'cancelled' => "Sale #{$sale->invoice_number} cancelled",
            default => "Sale #{$sale->invoice_number} {$action}",
        };

        return self::log(
            $action,
            'Sale',
            $sale->id,
            $sale->invoice_number,
            $description,
            $changes,
            'sales',
            'info',
            $sale->tenant_id ?? $sale->user?->tenant_id
        );
    }

    /**
     * Log a purchase
     */
    public static function logPurchase(string $action, $purchase, ?array $changes = null): ActivityLog
    {
        $description = match($action) {
            'created' => "Purchase #{$purchase->reference_number} created from {$purchase->supplier->name}",
            'updated' => "Purchase #{$purchase->reference_number} updated",
            'deleted' => "Purchase #{$purchase->reference_number} deleted",
            'completed' => "Purchase #{$purchase->reference_number} completed",
            'cancelled' => "Purchase #{$purchase->reference_number} cancelled",
            default => "Purchase #{$purchase->reference_number} {$action}",
        };

        return self::log(
            $action,
            'Purchase',
            $purchase->id,
            $purchase->reference_number,
            $description,
            $changes,
            'purchases',
            'info',
            $purchase->tenant_id ?? $purchase->user?->tenant_id
        );
    }

    /**
     * Log inventory change
     */
    public static function logInventory(string $action, $product, ?array $changes = null): ActivityLog
    {
        $description = match($action) {
            'stock_received' => "Stock received for {$product->name}",
            'stock_adjusted' => "Inventory adjusted for {$product->name}",
            'stock_transferred' => "Stock transferred for {$product->name}",
            default => "Inventory {$action} for {$product->name}",
        };

        return self::log(
            $action,
            'Product',
            $product->id,
            $product->name,
            $description,
            $changes,
            'inventory',
            'info',
            $product->tenant_id
        );
    }

    /**
     * Log user action
     */
    public static function logUser(string $action, $user, ?array $changes = null): ActivityLog
    {
        $description = match($action) {
            'created' => "User {$user->name} ({$user->email}) created",
            'updated' => "User {$user->name} updated",
            'deleted' => "User {$user->name} deleted",
            'login' => "User {$user->name} logged in",
            'logout' => "User {$user->name} logged out",
            default => "User {$user->name} {$action}",
        };

        return self::log(
            $action,
            'User',
            $user->id,
            $user->name,
            $description,
            $changes,
            'users',
            'info',
            $user->tenant_id
        );
    }

    /**
     * Log product action
     */
    public static function logProduct(string $action, $product, ?array $changes = null): ActivityLog
    {
        $description = match($action) {
            'created' => "Product {$product->name} created",
            'updated' => "Product {$product->name} updated",
            'deleted' => "Product {$product->name} deleted",
            default => "Product {$product->name} {$action}",
        };

        return self::log(
            $action,
            'Product',
            $product->id,
            $product->name,
            $description,
            $changes,
            'products',
            'info',
            $product->tenant_id
        );
    }

    /**
     * Log customer action
     */
    public static function logCustomer(string $action, $customer, ?array $changes = null): ActivityLog
    {
        $description = match($action) {
            'created' => "Customer {$customer->name} created",
            'updated' => "Customer {$customer->name} updated",
            'deleted' => "Customer {$customer->name} deleted",
            default => "Customer {$customer->name} {$action}",
        };

        return self::log(
            $action,
            'Customer',
            $customer->id,
            $customer->name,
            $description,
            $changes,
            'customers',
            'info',
            $customer->tenant_id
        );
    }

    /**
     * Log error
     */
    public static function logError(string $message, string $category = 'system', ?array $context = null): ActivityLog
    {
        return self::log(
            'error',
            'System',
            null,
            null,
            $message,
            $context,
            $category,
            'error'
        );
    }

    /**
     * Log warning
     */
    public static function logWarning(string $message, string $category = 'system', ?array $context = null): ActivityLog
    {
        return self::log(
            'warning',
            'System',
            null,
            null,
            $message,
            $context,
            $category,
            'warning'
        );
    }
}


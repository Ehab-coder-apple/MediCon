<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;
use Throwable;

class LoggingService
{
    /**
     * Log transaction events (sales, purchases, inventory changes)
     */
    public static function logTransaction(string $type, array $data, ?string $userId = null): void
    {
        $userId = $userId ?? Auth::id();
        
        $logData = [
            'type' => $type,
            'user_id' => $userId,
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
            'data' => $data,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ];

        Log::channel('transactions')->info("Transaction: {$type}", $logData);
    }

    /**
     * Log database errors with context
     */
    public static function logDatabaseError(Throwable $exception, string $operation, array $context = []): void
    {
        $logData = [
            'operation' => $operation,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
            'context' => $context,
            'ip_address' => request()?->ip(),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
        ];

        Log::channel('errors')->error("Database Error: {$operation}", $logData);
    }

    /**
     * Log validation errors
     */
    public static function logValidationError(array $errors, Request $request): void
    {
        $logData = [
            'validation_errors' => $errors,
            'request_data' => $request->except(['password', 'password_confirmation', '_token']),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
            'ip_address' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
        ];

        Log::channel('errors')->warning('Validation Error', $logData);
    }

    /**
     * Log security events (failed logins, unauthorized access, etc.)
     */
    public static function logSecurityEvent(string $event, array $data = []): void
    {
        $logData = [
            'event' => $event,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
            'data' => $data,
        ];

        Log::channel('security')->warning("Security Event: {$event}", $logData);
    }

    /**
     * Log audit trail for important actions
     */
    public static function logAudit(string $action, string $model, $modelId, array $changes = []): void
    {
        $logData = [
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => $changes,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ];

        Log::channel('audit')->info("Audit: {$action} {$model}", $logData);
    }

    /**
     * Log system performance issues
     */
    public static function logPerformance(string $operation, float $executionTime, array $context = []): void
    {
        if ($executionTime > 1.0) { // Log slow operations (> 1 second)
            $logData = [
                'operation' => $operation,
                'execution_time' => $executionTime,
                'context' => $context,
                'user_id' => Auth::id(),
                'timestamp' => now()->toISOString(),
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true),
            ];

            Log::channel('daily')->warning("Slow Operation: {$operation}", $logData);
        }
    }

    /**
     * Log inventory changes
     */
    public static function logInventoryChange(string $type, int $productId, int $batchId, int $quantityChange, array $context = []): void
    {
        $logData = [
            'type' => $type, // 'sale', 'purchase', 'adjustment', 'expiry'
            'product_id' => $productId,
            'batch_id' => $batchId,
            'quantity_change' => $quantityChange,
            'context' => $context,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('transactions')->info("Inventory Change: {$type}", $logData);
    }

    /**
     * Log failed payment transactions
     */
    public static function logPaymentFailure(string $paymentMethod, float $amount, string $reason, array $context = []): void
    {
        $logData = [
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'failure_reason' => $reason,
            'context' => $context,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()?->ip(),
        ];

        Log::channel('transactions')->error('Payment Failure', $logData);
    }

    /**
     * Log prescription events
     */
    public static function logPrescription(string $action, int $prescriptionId, array $context = []): void
    {
        $logData = [
            'action' => $action, // 'uploaded', 'approved', 'rejected', 'dispensed'
            'prescription_id' => $prescriptionId,
            'context' => $context,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('audit')->info("Prescription: {$action}", $logData);
    }

    /**
     * Log system errors with full context
     */
    public static function logSystemError(Throwable $exception, string $context = ''): void
    {
        $logData = [
            'context' => $context,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()?->ip(),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'request_data' => request()?->except(['password', 'password_confirmation', '_token']),
        ];

        Log::channel('errors')->error("System Error: {$context}", $logData);
    }

    /**
     * Log user activity for analytics
     */
    public static function logUserActivity(string $activity, array $data = []): void
    {
        $logData = [
            'activity' => $activity,
            'data' => $data,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'user_role' => Auth::user()?->role?->name,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'session_id' => session()->getId(),
        ];

        Log::channel('audit')->info("User Activity: {$activity}", $logData);
    }

    /**
     * Log critical system events that require immediate attention
     */
    public static function logCritical(string $event, array $data = []): void
    {
        $logData = [
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'server' => gethostname(),
            'environment' => app()->environment(),
        ];

        Log::channel('errors')->critical("Critical Event: {$event}", $logData);
        
        // Also log to daily for immediate visibility
        Log::channel('daily')->critical("Critical Event: {$event}", $logData);
    }
}

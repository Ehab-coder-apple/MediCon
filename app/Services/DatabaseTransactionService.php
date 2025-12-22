<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class DatabaseTransactionService
{
    /**
     * Execute a database transaction with proper error handling
     */
    public static function executeTransaction(callable $callback, string $operation = 'database_operation'): array
    {
        $startTime = microtime(true);
        
        try {
            DB::beginTransaction();
            
            $result = $callback();
            
            DB::commit();
            
            $executionTime = microtime(true) - $startTime;
            LoggingService::logPerformance($operation, $executionTime);
            
            LoggingService::logTransaction($operation, [
                'status' => 'success',
                'execution_time' => $executionTime,
                'result' => is_array($result) ? $result : ['data' => $result]
            ]);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'Operation completed successfully'
            ];
            
        } catch (Throwable $e) {
            DB::rollBack();
            
            $executionTime = microtime(true) - $startTime;
            
            LoggingService::logDatabaseError($e, $operation, [
                'execution_time' => $executionTime,
                'transaction_rolled_back' => true
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Execute a sale transaction with inventory updates
     */
    public static function executeSaleTransaction(array $saleData, array $items): array
    {
        return self::executeTransaction(function() use ($saleData, $items) {
            // Create the sale
            $sale = \App\Models\Sale::create($saleData);

            $totalPrice = 0;

            foreach ($items as $item) {
                // Validate product exists
                $product = \App\Models\Product::findOrFail($item['product_id']);

                // Determine batch to use: either the provided one or auto-selected (FIFO)
                $batchId = $item['batch_id'] ?? null;

                if ($batchId) {
                    $batch = \App\Models\Batch::where('id', $batchId)
                        ->where('product_id', $product->id)
                        ->first();

                    if (! $batch) {
                        throw new Exception("Selected batch not found for product: {$product->name}");
                    }

                    if ($batch->quantity < $item['quantity']) {
                        throw new Exception("Insufficient stock in selected batch for product: {$product->name}");
                    }
                } else {
                    // Legacy behaviour: auto-select a batch with sufficient quantity (FIFO)
                    $batch = \App\Models\Batch::where('product_id', $product->id)
                        ->where('quantity', '>=', $item['quantity'])
                        ->orderBy('expiry_date')
                        ->first();

                    if (! $batch) {
                        throw new Exception("Insufficient stock for product: {$product->name}");
                    }

                    $batchId = $batch->id;
                }

                // Create sale item (inventory updates are handled by SaleItem model events)
                $saleItem = \App\Models\SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'batch_id' => $batchId,
                ]);

                $totalPrice += $saleItem->total_price;
            }

            // Update sale total
            $sale->update(['total_price' => $totalPrice]);

            // Eager-load relations needed for invoice creation and response
            return $sale->load('saleItems.product', 'customer');

        }, 'sale_transaction');
    }

    /**
     * Execute a purchase transaction with inventory updates
     */
    public static function executePurchaseTransaction(array $purchaseData, array $items): array
    {
        return self::executeTransaction(function() use ($purchaseData, $items) {
            // Create the purchase
            $purchase = \App\Models\Purchase::create($purchaseData);
            
            $totalCost = 0;
            
            foreach ($items as $item) {
                // Validate product exists
                $product = \App\Models\Product::findOrFail($item['product_id']);
                
                // Create purchase item
                $purchaseItem = \App\Models\PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'],
                ]);
                
                // Create or update batch if purchase is completed
                if ($purchase->status === 'completed') {
                    $batch = \App\Models\Batch::updateOrCreate(
                        ['batch_number' => $item['batch_number']],
                        [
                            'product_id' => $product->id,
                            'quantity' => $item['quantity'],
                            'cost_price' => $item['unit_cost'],
                            'expiry_date' => $item['expiry_date'],
                        ]
                    );
                    
                    // Log inventory change
                    LoggingService::logInventoryChange(
                        'purchase',
                        $product->id,
                        $batch->id,
                        $item['quantity'],
                        ['purchase_id' => $purchase->id, 'purchase_item_id' => $purchaseItem->id]
                    );
                }
                
                $totalCost += $purchaseItem->total_cost;
            }
            
            // Update purchase total
            $purchase->update(['total_cost' => $totalCost]);
            
            return $purchase->load('items.product', 'supplier');
            
        }, 'purchase_transaction');
    }

    /**
     * Execute inventory adjustment transaction
     */
    public static function executeInventoryAdjustment(int $batchId, int $quantityChange, string $reason): array
    {
        return self::executeTransaction(function() use ($batchId, $quantityChange, $reason) {
            $batch = \App\Models\Batch::findOrFail($batchId);
            
            // Validate adjustment
            if ($batch->quantity + $quantityChange < 0) {
                throw new Exception("Cannot adjust quantity below zero. Current: {$batch->quantity}, Adjustment: {$quantityChange}");
            }
            
            $oldQuantity = $batch->quantity;
            $batch->increment('quantity', $quantityChange);
            
            // Log inventory change
            LoggingService::logInventoryChange(
                'adjustment',
                $batch->product_id,
                $batch->id,
                $quantityChange,
                [
                    'reason' => $reason,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $batch->quantity
                ]
            );
            
            return $batch->fresh();
            
        }, 'inventory_adjustment');
    }

    /**
     * Execute user creation with role assignment
     */
    public static function executeUserCreation(array $userData, int $roleId): array
    {
        return self::executeTransaction(function() use ($userData, $roleId) {
            // Validate role exists
            $role = \App\Models\Role::findOrFail($roleId);
            
            // Create user
            $user = \App\Models\User::create(array_merge($userData, ['role_id' => $roleId]));
            
            // Log audit trail
            LoggingService::logAudit('created', 'User', $user->id, [
                'role' => $role->name,
                'email' => $user->email
            ]);
            
            return $user->load('role');
            
        }, 'user_creation');
    }

    /**
     * Execute prescription approval
     */
    public static function executePrescriptionApproval(int $prescriptionId, bool $approved, ?string $notes = null): array
    {
        return self::executeTransaction(function() use ($prescriptionId, $approved, $notes) {
            $prescription = \App\Models\Prescription::findOrFail($prescriptionId);
            
            if ($prescription->approved_at) {
                throw new Exception('Prescription has already been processed');
            }
            
            $prescription->update([
                'approved_at' => $approved ? now() : null,
                'pharmacist_id' => auth()->id(),
                'notes' => $notes,
                'status' => $approved ? 'approved' : 'rejected'
            ]);
            
            // Log prescription action
            LoggingService::logPrescription(
                $approved ? 'approved' : 'rejected',
                $prescription->id,
                ['notes' => $notes]
            );
            
            return $prescription->fresh(['customer', 'pharmacist']);
            
        }, 'prescription_approval');
    }

    /**
     * Execute batch operations with error handling
     */
    public static function executeBatchOperation(array $operations, string $operationType): array
    {
        return self::executeTransaction(function() use ($operations, $operationType) {
            $results = [];
            
            foreach ($operations as $operation) {
                $results[] = $operation();
            }
            
            return $results;
            
        }, "batch_{$operationType}");
    }
}

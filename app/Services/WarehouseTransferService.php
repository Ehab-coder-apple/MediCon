<?php

namespace App\Services;

use App\Models\ProcessedInvoice;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class WarehouseTransferService
{
    /**
     * Transfer invoice items to a warehouse
     */
    public function transferToWarehouse(ProcessedInvoice $invoice, Warehouse $warehouse): array
    {
        try {
            if (!$invoice->items || $invoice->items->isEmpty()) {
                throw new Exception('No items to transfer');
            }

            if ($invoice->transfer_status !== 'pending') {
                throw new Exception('Invoice transfer is not in pending status');
            }

            return DB::transaction(function () use ($invoice, $warehouse) {
                $itemsTransferred = 0;

                foreach ($invoice->items as $item) {
                    $itemsTransferred += $this->transferItem($invoice, $item, $warehouse);
                }

                // Update invoice transfer status
                $invoice->update([
                    'warehouse_id' => $warehouse->id,
                    'transfer_status' => 'completed',
                    'transfer_approved_by' => auth()->id(),
                    'transfer_approved_at' => now(),
                ]);

                Log::info('Invoice items transferred to warehouse', [
                    'invoice_id' => $invoice->id,
                    'warehouse_id' => $warehouse->id,
                    'items_transferred' => $itemsTransferred,
                ]);

                return [
                    'success' => true,
                    'message' => "$itemsTransferred items transferred to warehouse successfully",
                    'items_transferred' => $itemsTransferred,
                ];
            });
        } catch (Exception $e) {
            Log::error('Warehouse transfer failed', [
                'invoice_id' => $invoice->id,
                'warehouse_id' => $warehouse->id ?? null,
                'error' => $e->getMessage(),
            ]);

            $invoice->update(['transfer_status' => 'failed']);

            return [
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Transfer a single invoice item to warehouse
     */
    private function transferItem(ProcessedInvoice $invoice, $item, Warehouse $warehouse): int
    {
        try {
            // Get or create batch if product exists
            $batch = null;
            if ($item->product_id) {
                $batch = $this->getOrCreateBatch($item);
            }

            // Create or update warehouse stock
            $warehouseStock = WarehouseStock::firstOrNew([
                'tenant_id' => $invoice->tenant_id,
                'warehouse_id' => $warehouse->id,
                'product_id' => $item->product_id,
                'batch_id' => $batch?->id,
            ]);

            $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $item->quantity;
            $warehouseStock->save();

            return 1;
        } catch (Exception $e) {
            Log::warning('Failed to transfer item', [
                'invoice_id' => $invoice->id,
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get or create a batch for the item
     */
    private function getOrCreateBatch($item): ?Batch
    {
        if (!$item->product_id) {
            return null;
        }

        if ($item->batch_number) {
            return Batch::firstOrCreate(
                [
                    'product_id' => $item->product_id,
                    'batch_number' => $item->batch_number,
                ],
                [
                    'expiry_date' => $item->expiry_date ?? now()->addYear(),
                    'quantity' => 0,
                    'cost_price' => $item->unit_price ?? 0,
                ]
            );
        }

        // Create a batch without batch number if not provided
        return Batch::create([
            'product_id' => $item->product_id,
            'batch_number' => 'AUTO-' . now()->format('YmdHis'),
            'expiry_date' => $item->expiry_date ?? now()->addYear(),
            'quantity' => 0,
            'cost_price' => $item->unit_price ?? 0,
        ]);
    }

    /**
     * Approve warehouse transfer
     */
    public function approveTransfer(ProcessedInvoice $invoice): array
    {
        try {
            if (!$invoice->warehouse_id) {
                throw new Exception('No warehouse selected for transfer');
            }

            $warehouse = Warehouse::findOrFail($invoice->warehouse_id);

            return $this->transferToWarehouse($invoice, $warehouse);
        } catch (Exception $e) {
            Log::error('Transfer approval failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Approval failed: ' . $e->getMessage(),
            ];
        }
    }
}


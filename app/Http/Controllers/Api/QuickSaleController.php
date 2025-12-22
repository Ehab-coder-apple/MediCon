<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Services\DatabaseTransactionService;
use App\Services\LoggingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuickSaleController extends Controller
{
    /**
     * Create a quick sale from barcode scanner
     * POST /api/sales/quick-create
     * 
     * Request body:
     * {
     *   "items": [
     *     {
     *       "product_id": 1,
     *       "batch_id": 1,
     *       "quantity": 2,
     *       "unit_price": 100.00
     *     }
     *   ],
     *   "customer_id": null,
     *   "customer_name": "Walk-in Customer",
     *   "payment_method": "cash",
     *   "discount_amount": 0,
     *   "tax_amount": 0,
     *   "paid_amount": 200.00,
     *   "notes": "Barcode scanner sale"
     * }
     */
    public function quickCreate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.batch_id' => 'nullable|integer|exists:batches,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'customer_id' => 'nullable|integer|exists:customers,id',
                'customer_name' => 'nullable|string|max:255',
                'payment_method' => 'required|in:cash,card,insurance,mixed',
                'discount_amount' => 'nullable|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'paid_amount' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            // Get authenticated user
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Handle customer
            $customerId = $validated['customer_id'] ?? null;
            if (!$customerId && !empty($validated['customer_name'])) {
                $customer = Customer::create([
                    'name' => $validated['customer_name'],
                    'tenant_id' => $user->tenant_id,
                ]);
                $customerId = $customer->id;
            }

            // Prepare sale data
            $saleData = [
                'customer_id' => $customerId,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'sale_date' => now(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'payment_method' => $validated['payment_method'],
                'paid_amount' => $validated['paid_amount'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'notes' => $validated['notes'] ?? 'Barcode scanner sale',
                'status' => 'completed',
                'total_price' => 0, // Will be updated after items are added
            ];

            // Execute sale transaction
            $result = DatabaseTransactionService::executeSaleTransaction($saleData, $validated['items']);

            if (!$result['success']) {
                LoggingService::logSystemError(
                    new \Exception($result['message']),
                    'quick_sale_creation_failed'
                );

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            if (!isset($result['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale created but data not returned',
                ], 500);
            }

            $sale = $result['data'];

            // Log successful sale
            LoggingService::logUserActivity('quick_sale_created', [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total_price' => $sale->total_price,
                'items_count' => count($validated['items']),
                'source' => 'barcode_scanner',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'total_price' => (float) $sale->total_price,
                    'subtotal' => (float) $sale->subtotal,
                    'discount_amount' => (float) $sale->discount_amount,
                    'tax_amount' => (float) $sale->tax_amount,
                    'paid_amount' => (float) $sale->paid_amount,
                    'change_amount' => (float) ($sale->paid_amount - $sale->total_price),
                    'items_count' => $sale->total_quantity,
                    'sale_date' => $sale->sale_date->format('Y-m-d H:i:s'),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            LoggingService::logSystemError($e, 'quick_sale_controller_error');

            return response()->json([
                'success' => false,
                'message' => 'Error creating sale: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sale details
     * GET /api/sales/{saleId}
     */
    public function getSale(int $saleId): JsonResponse
    {
        try {
            $sale = Sale::with(['customer', 'saleItems.product', 'saleItems.batch'])
                ->find($saleId);

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'sale' => $sale,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching sale: ' . $e->getMessage(),
            ], 500);
        }
    }
}


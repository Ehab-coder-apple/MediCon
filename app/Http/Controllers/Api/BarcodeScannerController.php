<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarcodeScannerController extends Controller
{
    /**
     * Look up product by barcode
     * GET /api/products/by-barcode/{barcode}
     */
    public function lookupByBarcode(string $barcode): JsonResponse
    {
        try {
            // Trim and validate barcode
            $barcode = trim($barcode);
            
            if (empty($barcode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode cannot be empty',
                ], 400);
            }

            // Search by barcode first, then by product code as fallback
            $product = Product::with('batches')
                ->where(function ($query) use ($barcode) {
                    $query->where('barcode', $barcode)
                        ->orWhere('code', $barcode);
                })
                ->where('is_active', true)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found for barcode: ' . $barcode,
                ], 404);
            }

            // Get active batches with stock
            $batches = $product->activeBatches()
                ->select('id', 'product_id', 'batch_number', 'quantity', 'expiry_date')
                ->get();

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'barcode' => $product->barcode,
                    'manufacturer' => $product->manufacturer,
                    'selling_price' => (float) $product->selling_price,
                    'cost_price' => (float) $product->cost_price,
                    'total_quantity' => $product->total_quantity,
                    'active_quantity' => $product->active_quantity,
                    'is_low_stock' => $product->is_low_stock,
                    'alert_quantity' => $product->alert_quantity,
                ],
                'batches' => $batches,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error looking up product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get product details with batch information
     * GET /api/products/{productId}/details
     */
    public function getProductDetails(int $productId): JsonResponse
    {
        try {
            $product = Product::with('batches')->where('is_active', true)->find($productId);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $batches = $product->activeBatches()
                ->select('id', 'product_id', 'batch_number', 'quantity', 'expiry_date')
                ->get();

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'barcode' => $product->barcode,
                    'manufacturer' => $product->manufacturer,
                    'selling_price' => (float) $product->selling_price,
                    'cost_price' => (float) $product->cost_price,
                    'total_quantity' => $product->total_quantity,
                    'active_quantity' => $product->active_quantity,
                    'is_low_stock' => $product->is_low_stock,
                    'alert_quantity' => $product->alert_quantity,
                ],
                'batches' => $batches,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check stock availability for a product
     * POST /api/products/{productId}/check-stock
     */
    public function checkStock(Request $request, int $productId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
                'batch_id' => 'nullable|integer|exists:batches,id',
            ]);

            $product = Product::with('batches')->find($productId);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $availableQuantity = $product->active_quantity;

            if (!empty($validated['batch_id'])) {
                $batch = Batch::find($validated['batch_id']);
                if ($batch && $batch->product_id === $productId) {
                    $availableQuantity = $batch->quantity;
                }
            }

            $hasStock = $availableQuantity >= $validated['quantity'];

            return response()->json([
                'success' => true,
                'has_stock' => $hasStock,
                'requested_quantity' => $validated['quantity'],
                'available_quantity' => $availableQuantity,
                'message' => $hasStock 
                    ? 'Stock available' 
                    : 'Insufficient stock. Available: ' . $availableQuantity,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking stock: ' . $e->getMessage(),
            ], 500);
        }
    }
}


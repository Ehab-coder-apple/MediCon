<?php

namespace App\Http\Controllers;

use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CodeGeneratorController extends Controller
{
    /**
     * Show code generator interface
     */
    public function index(): View
    {
        return view('admin.code-generator.index');
    }

    /**
     * Generate a single code
     */
    public function generateSingle(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:product,customer,batch,barcode,access,prescription,transaction,reference',
            'category' => 'nullable|string',
            'product_code' => 'nullable|string',
            'prefix' => 'nullable|string',
            'length' => 'nullable|integer|min:4|max:20',
        ]);

        try {
            $code = match($request->type) {
                'product' => CodeGeneratorService::generateProductCode($request->category),
                'customer' => CodeGeneratorService::generateCustomerCode(),
                'batch' => CodeGeneratorService::generateBatchNumber($request->product_code ?? 'PROD'),
                'barcode' => CodeGeneratorService::generateBarcode(),
                'access' => \App\Models\AccessCode::generateUniqueCode(),
                'prescription' => CodeGeneratorService::generatePrescriptionNumber(),
                'transaction' => CodeGeneratorService::generateTransactionId(),
                'reference' => CodeGeneratorService::generateReferenceCode(
                    $request->prefix ?? 'REF',
                    $request->length ?? 8
                ),
                default => throw new \InvalidArgumentException('Invalid code type')
            };

            return response()->json([
                'success' => true,
                'code' => $code,
                'type' => $request->type,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate multiple codes
     */
    public function generateBulk(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:product,customer,batch,barcode,access,prescription,reference',
            'count' => 'required|integer|min:1|max:100',
            'category' => 'nullable|string',
            'prefix' => 'nullable|string',
        ]);

        try {
            $options = [
                'category' => $request->category,
                'prefix' => $request->prefix,
            ];

            $codes = CodeGeneratorService::generateBulkCodes(
                $request->type,
                $request->count,
                $options
            );

            return response()->json([
                'success' => true,
                'codes' => $codes,
                'count' => count($codes),
                'type' => $request->type,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Validate a code format
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'type' => 'required|in:product,customer,purchase,invoice,batch,access,prescription',
        ]);

        $isValid = CodeGeneratorService::validateCodeFormat($request->code, $request->type);

        return response()->json([
            'success' => true,
            'code' => $request->code,
            'type' => $request->type,
            'is_valid' => $isValid,
            'message' => $isValid ? 'Code format is valid' : 'Code format is invalid',
        ]);
    }

    /**
     * Get next available code preview
     */
    public function previewNext(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:product,customer,purchase,invoice,batch,access',
            'category' => 'nullable|string',
            'product_code' => 'nullable|string',
        ]);

        try {
            // Generate a preview without saving
            $preview = match($request->type) {
                'product' => CodeGeneratorService::generateProductCode($request->category),
                'customer' => CodeGeneratorService::generateCustomerCode(),
                'batch' => CodeGeneratorService::generateBatchNumber($request->product_code ?? 'PROD'),
                'purchase' => \App\Models\Purchase::generateReferenceNumber(),
                'invoice' => \App\Models\Sale::generateInvoiceNumber(),
                'access' => \App\Models\AccessCode::generateUniqueCode(),
                default => 'N/A'
            };

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'type' => $request->type,
                'note' => 'This is a preview. The actual code may differ when generated.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get code statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = [
                'products' => \App\Models\Product::count(),
                'customers' => \App\Models\Customer::count(),
                'purchases' => \App\Models\Purchase::count(),
                'sales' => \App\Models\Sale::count(),
                'batches' => \App\Models\Batch::count(),
                'access_codes' => \App\Models\AccessCode::count(),
                'prescriptions' => \App\Models\Prescription::count(),
            ];

            $todayStats = [
                'purchases_today' => \App\Models\Purchase::whereDate('created_at', today())->count(),
                'sales_today' => \App\Models\Sale::whereDate('created_at', today())->count(),
                'customers_today' => \App\Models\Customer::whereDate('created_at', today())->count(),
            ];

            return response()->json([
                'success' => true,
                'total_codes' => $stats,
                'today_codes' => $todayStats,
                'last_updated' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}

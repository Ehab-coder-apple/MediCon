<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenAIProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OpenAIProductController extends Controller
{
    public function __construct(private OpenAIProductService $openaiService)
    {
    }

    /**
     * Get product information from OpenAI
     */
    public function getProductInfo(Request $request)
    {
        try {
            $request->validate([
                'product_name' => 'required|string|min:2',
            ]);

            $productInfo = $this->openaiService->getProductInformation(
                $request->input('product_name')
            );

            return response()->json([
                'success' => true,
                'data' => $productInfo,
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI Product Info Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch product information: ' . $e->getMessage(),
            ], 500);
        }
    }
}


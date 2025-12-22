<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductInformation;
use App\Models\AlternativeProduct;
use Illuminate\Http\Request;

class ProductInformationController extends Controller
{
    /**
     * Search for products
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $products = Product::where('name', 'like', '%' . $request->query . '%')
            ->orWhere('code', 'like', '%' . $request->query . '%')
            ->with('information')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    /**
     * Get detailed product information
     */
    public function getProductInfo($productId)
    {
        $product = Product::with('information')->findOrFail($productId);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'manufacturer' => $product->manufacturer,
            'selling_price' => $product->selling_price,
            'active_quantity' => $product->active_quantity,
            'information' => $product->information,
        ]);
    }

    /**
     * Get alternative products for a medication
     */
    public function getAlternatives(Request $request, $productId)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $product = Product::findOrFail($productId);

        // Find alternatives based on category or active ingredients
        $alternatives = Product::where('id', '!=', $productId)
            ->where('category_id', $product->category_id)
            ->where('is_active', true)
            ->with('information')
            ->get()
            ->map(function ($alt) use ($product, $request) {
                return [
                    'id' => $alt->id,
                    'name' => $alt->name,
                    'manufacturer' => $alt->manufacturer,
                    'available_quantity' => $alt->active_quantity,
                    'price' => $alt->selling_price,
                    'shelf_location' => $alt->location?->name,
                    'similarity_score' => $this->calculateSimilarity($product, $alt),
                ];
            });

        return response()->json($alternatives);
    }

    /**
     * Calculate similarity score between two products
     */
    private function calculateSimilarity(Product $original, Product $alternative): int
    {
        $score = 0;

        // Same category = 50 points
        if ($original->category_id === $alternative->category_id) {
            $score += 50;
        }

        // Same manufacturer = 30 points
        if ($original->manufacturer === $alternative->manufacturer) {
            $score += 30;
        }

        // Similar price range = 20 points
        $priceDiff = abs($original->selling_price - $alternative->selling_price);
        if ($priceDiff < ($original->selling_price * 0.2)) {
            $score += 20;
        }

        return min($score, 100);
    }

    /**
     * Update product information
     */
    public function updateProductInfo(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $this->authorize('update', $product);

        $validated = $request->validate([
            'active_ingredients' => 'array',
            'side_effects' => 'array',
            'indications' => 'array',
            'dosage_information' => 'string',
            'contraindications' => 'array',
            'drug_interactions' => 'array',
            'storage_requirements' => 'array',
        ]);

        $info = ProductInformation::updateOrCreate(
            ['product_id' => $productId],
            array_merge($validated, [
                'last_updated_by' => auth()->id(),
                'source' => 'manual_entry',
            ])
        );

        return response()->json(['message' => 'Product information updated', 'data' => $info]);
    }
}


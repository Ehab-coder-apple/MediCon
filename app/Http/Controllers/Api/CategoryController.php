<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all active categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->ordered()
            ->select('id', 'name', 'slug', 'requires_prescription')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get subcategories for a specific category
     */
    public function subcategories(Category $category): JsonResponse
    {
        $subcategories = $category->activeSubcategories()
            ->select('id', 'name', 'slug', 'requires_prescription')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }

    /**
     * Get all active subcategories with their categories
     */
    public function allSubcategories(): JsonResponse
    {
        $subcategories = Subcategory::active()
            ->with('category:id,name')
            ->ordered()
            ->select('id', 'category_id', 'name', 'slug', 'requires_prescription')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }

    /**
     * Search categories and subcategories
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => [],
                    'subcategories' => []
                ]
            ]);
        }

        $categories = Category::active()
            ->where('name', 'LIKE', "%{$query}%")
            ->ordered()
            ->select('id', 'name', 'slug', 'requires_prescription')
            ->limit(10)
            ->get();

        $subcategories = Subcategory::active()
            ->with('category:id,name')
            ->where('name', 'LIKE', "%{$query}%")
            ->ordered()
            ->select('id', 'category_id', 'name', 'slug', 'requires_prescription')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'subcategories' => $subcategories
            ]
        ]);
    }
}

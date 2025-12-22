<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If this is a fresh installation without legacy category tables/column, skip
        if (!Schema::hasTable('categories') || !Schema::hasTable('subcategories') || !Schema::hasColumn('products', 'category')) {
            return;
        }

        // Category mapping from old text categories to new structured categories
        $categoryMapping = [
            'Pain Relief' => [
                'category' => 'OTC Medications',
                'subcategory' => 'Pain Relief & Fever'
            ],
            'Antibiotics' => [
                'category' => 'Prescription Medications',
                'subcategory' => 'Antibiotics'
            ],
            'Vitamins' => [
                'category' => 'Vitamins & Supplements',
                'subcategory' => 'Vitamins'
            ],
            'Multivitamin' => [
                'category' => 'Vitamins & Supplements',
                'subcategory' => 'Multivitamins'
            ],
            'Cold & Flu' => [
                'category' => 'OTC Medications',
                'subcategory' => 'Cold & Flu'
            ],
            'Allergy' => [
                'category' => 'OTC Medications',
                'subcategory' => 'Allergy & Sinus'
            ],
            'Digestive' => [
                'category' => 'OTC Medications',
                'subcategory' => 'Digestive Health'
            ],
            'Topical' => [
                'category' => 'Dermatology & Skin',
                'subcategory' => 'Topical Treatments'
            ],
        ];

        // Get all categories and subcategories for lookup
        $categories = Category::all()->keyBy('name');
        $subcategories = Subcategory::with('category')->get();

        // Create lookup array for subcategories
        $subcategoryLookup = [];
        foreach ($subcategories as $subcategory) {
            $key = $subcategory->category->name . '::' . $subcategory->name;
            $subcategoryLookup[$key] = $subcategory;
        }

        // Process each old category
        foreach ($categoryMapping as $oldCategory => $mapping) {
            $categoryName = $mapping['category'];
            $subcategoryName = $mapping['subcategory'];

            // Find the category
            if (!isset($categories[$categoryName])) {
                echo "Warning: Category '{$categoryName}' not found\n";
                continue;
            }

            $category = $categories[$categoryName];
            $subcategory = null;

            // Find the subcategory
            $subcategoryKey = $categoryName . '::' . $subcategoryName;
            if (isset($subcategoryLookup[$subcategoryKey])) {
                $subcategory = $subcategoryLookup[$subcategoryKey];
            }

            // Update products with this old category
            $products = Product::where('category', $oldCategory)->get();
            
            foreach ($products as $product) {
                $product->category_id = $category->id;
                if ($subcategory) {
                    $product->subcategory_id = $subcategory->id;
                }
                $product->save();
                
                echo "Updated product '{$product->name}': {$oldCategory} -> {$categoryName}";
                if ($subcategory) {
                    echo " -> {$subcategoryName}";
                }
                echo "\n";
            }
        }

        echo "Product category migration completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all category_id and subcategory_id to null
        Product::query()->update([
            'category_id' => null,
            'subcategory_id' => null
        ]);
    }
};

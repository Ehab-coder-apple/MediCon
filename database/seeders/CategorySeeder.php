<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Prescription Medications',
                'description' => 'Medications that require a prescription from a licensed healthcare provider',
                'requires_prescription' => true,
                'sort_order' => 1,
                'subcategories' => [
                    'Antibiotics', 'Antivirals', 'Antifungals', 'Antihypertensives', 'Antidepressants',
                    'Antipsychotics', 'Cardiovascular Drugs', 'Hormonal Therapy', 'Oncology Drugs',
                    'Diabetes Medications', 'Respiratory Medications'
                ]
            ],
            [
                'name' => 'OTC Medications',
                'description' => 'Over-the-counter medications available without prescription',
                'requires_prescription' => false,
                'sort_order' => 2,
                'subcategories' => [
                    'Pain Relievers', 'Cold and Flu', 'Cough Syrups', 'Allergy Relief',
                    'Heartburn and Indigestion', 'Laxatives', 'Anti-Diarrheal', 'Eye and Ear Drops', 'Sleep Aids'
                ]
            ],
            [
                'name' => 'Vitamins & Supplements',
                'description' => 'Nutritional supplements and vitamins for health maintenance',
                'requires_prescription' => false,
                'sort_order' => 3,
                'subcategories' => [
                    'Multivitamins', 'Vitamin D', 'Vitamin C', 'Calcium & Magnesium',
                    'Iron Supplements', 'Omega-3 & Fish Oil', 'Herbal Supplements', 'Protein & Weight Gain'
                ]
            ],
            [
                'name' => 'Baby Care',
                'description' => 'Products specifically designed for infant and baby care',
                'requires_prescription' => false,
                'sort_order' => 4,
                'subcategories' => [
                    'Infant Formula', 'Baby Diapers', 'Baby Wipes', 'Teething Relief',
                    'Baby Creams & Lotions', 'Pediatric Medicines'
                ]
            ],
            [
                'name' => 'Personal Care & Hygiene',
                'description' => 'Personal hygiene and care products for daily use',
                'requires_prescription' => false,
                'sort_order' => 5,
                'subcategories' => [
                    'Toothpaste & Mouthwash', 'Shampoo & Conditioner', 'Soap & Body Wash',
                    'Deodorants', 'Feminine Hygiene', 'Men\'s Care'
                ]
            ],
            [
                'name' => 'Beauty & Cosmetics',
                'description' => 'Beauty and cosmetic products for personal care',
                'requires_prescription' => false,
                'sort_order' => 6,
                'subcategories' => [
                    'Makeup', 'Sunscreen', 'Face Creams', 'Anti-aging Products', 'Skin Whitening'
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $subcategories = $categoryData['subcategories'];
            unset($categoryData['subcategories']);

            $category = Category::create($categoryData);

            foreach ($subcategories as $index => $subcategoryName) {
                Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryName,
                    'requires_prescription' => $category->requires_prescription,
                    'sort_order' => $index + 1
                ]);
            }
        }
    }
}

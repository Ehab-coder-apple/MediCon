<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeederPart2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'First Aid & Medical Supplies',
                'description' => 'Medical supplies and first aid equipment',
                'requires_prescription' => false,
                'sort_order' => 7,
                'subcategories' => [
                    'Bandages & Plasters', 'Antiseptics', 'Thermometers', 'Gloves & Masks',
                    'Wound Care', 'Medical Devices'
                ]
            ],
            [
                'name' => 'Chronic Disease Management',
                'description' => 'Products for managing chronic health conditions',
                'requires_prescription' => false,
                'sort_order' => 8,
                'subcategories' => [
                    'Diabetes Care', 'Hypertension Devices', 'Cardiac Monitoring', 'Asthma & COPD Management'
                ]
            ],
            [
                'name' => 'Sexual & Reproductive Health',
                'description' => 'Products for sexual and reproductive health',
                'requires_prescription' => false,
                'sort_order' => 9,
                'subcategories' => [
                    'Contraceptives', 'Pregnancy Tests', 'Fertility Support', 'Intimate Hygiene', 'Erectile Dysfunction'
                ]
            ],
            [
                'name' => 'Veterinary Products',
                'description' => 'Healthcare products for pets and animals',
                'requires_prescription' => false,
                'sort_order' => 10,
                'subcategories' => [
                    'Pet Medications', 'Dewormers', 'Flea & Tick Treatment', 'Pet Supplements'
                ]
            ],
            [
                'name' => 'Health & Wellness',
                'description' => 'General health and wellness products',
                'requires_prescription' => false,
                'sort_order' => 11,
                'subcategories' => [
                    'Weight Loss', 'Detox Products', 'Energy Boosters', 'Immunity Boosters', 'Stop Smoking Aids'
                ]
            ],
            [
                'name' => 'Dermatology & Skin',
                'description' => 'Skin care and dermatological products',
                'requires_prescription' => false,
                'sort_order' => 12,
                'subcategories' => [
                    'Acne Treatment', 'Eczema & Psoriasis', 'Scar Treatment', 'Skin Infections', 'Fungal Creams'
                ]
            ],
            [
                'name' => 'Controlled Substances',
                'description' => 'Controlled medications requiring special handling',
                'requires_prescription' => true,
                'sort_order' => 13,
                'subcategories' => [
                    'Opioids', 'Sedatives', 'ADHD Medications', 'Anti-Anxiety Drugs'
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

<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PharmaceuticalProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createPharmaceuticalProducts();
    }

    private function createPharmaceuticalProducts(): void
    {
        // Map old category names to new Category IDs
        $categoryMapping = [
            'Pain Relief' => 'OTC Medications',
            'Antibiotics' => 'Prescription Medications',
            'Vitamins' => 'Vitamins & Supplements',
            'Cold & Flu' => 'OTC Medications',
            'Digestive' => 'OTC Medications',
            'Allergy' => 'OTC Medications',
            'Topical' => 'OTC Medications',
        ];

        // Map products to subcategories
        $subcategoryMapping = [
            'Aspirin 325mg Tablets' => 'Pain Relievers',
            'Ibuprofen 400mg Tablets' => 'Pain Relievers',
            'Paracetamol 500mg Tablets' => 'Pain Relievers',
            'Amoxicillin 500mg Capsules' => 'Antibiotics',
            'Azithromycin 250mg Tablets' => 'Antibiotics',
            'Vitamin C 1000mg Tablets' => 'Vitamin C',
            'Vitamin D3 2000 IU Softgels' => 'Vitamin D',
            'Multivitamin Complex' => 'Multivitamins',
            'Cough Syrup 200ml' => 'Cough Syrups',
            'Throat Lozenges' => 'Cold and Flu',
            'Antacid Tablets' => 'Heartburn and Indigestion',
            'Probiotic Capsules' => 'Heartburn and Indigestion',
            'Antihistamine 10mg Tablets' => 'Allergy Relief',
            'Nasal Decongestant Spray' => 'Cold and Flu',
            'Antiseptic Cream 50g' => 'Eye and Ear Drops',
            'Hydrocortisone Cream 1%' => 'Eye and Ear Drops',
        ];

        $products = [
            // Pain Relief & Anti-inflammatory
            [
                'name' => 'Aspirin 325mg Tablets',
                'code' => 'ASP325',
                'category' => 'Pain Relief',
                'description' => 'Acetylsalicylic acid for pain relief and anti-inflammatory',
                'cost_price' => 2.50,
                'selling_price' => 4.99,
                'alert_quantity' => 50,
                'batches' => [
                    ['quantity' => 120, 'expiry_months' => 18, 'cost_price' => 2.50],
                    ['quantity' => 80, 'expiry_months' => 24, 'cost_price' => 2.45],
                    ['quantity' => 60, 'expiry_months' => 6, 'cost_price' => 2.60], // Near expiry
                ]
            ],
            [
                'name' => 'Ibuprofen 400mg Tablets',
                'code' => 'IBU400',
                'category' => 'Pain Relief',
                'description' => 'Non-steroidal anti-inflammatory drug (NSAID)',
                'cost_price' => 3.20,
                'selling_price' => 6.49,
                'alert_quantity' => 40,
                'batches' => [
                    ['quantity' => 150, 'expiry_months' => 20, 'cost_price' => 3.20],
                    ['quantity' => 90, 'expiry_months' => 12, 'cost_price' => 3.15],
                ]
            ],
            [
                'name' => 'Paracetamol 500mg Tablets',
                'code' => 'PAR500',
                'category' => 'Pain Relief',
                'description' => 'Acetaminophen for pain relief and fever reduction',
                'cost_price' => 1.80,
                'selling_price' => 3.99,
                'alert_quantity' => 100,
                'batches' => [
                    ['quantity' => 200, 'expiry_months' => 22, 'cost_price' => 1.80],
                    ['quantity' => 150, 'expiry_months' => 15, 'cost_price' => 1.75],
                    ['quantity' => 75, 'expiry_months' => 8, 'cost_price' => 1.85],
                ]
            ],

            // Antibiotics
            [
                'name' => 'Amoxicillin 500mg Capsules',
                'code' => 'AMX500',
                'category' => 'Antibiotics',
                'description' => 'Broad-spectrum penicillin antibiotic',
                'cost_price' => 8.50,
                'selling_price' => 15.99,
                'alert_quantity' => 30,
                'batches' => [
                    ['quantity' => 80, 'expiry_months' => 16, 'cost_price' => 8.50],
                    ['quantity' => 60, 'expiry_months' => 10, 'cost_price' => 8.40],
                ]
            ],
            [
                'name' => 'Azithromycin 250mg Tablets',
                'code' => 'AZI250',
                'category' => 'Antibiotics',
                'description' => 'Macrolide antibiotic for respiratory infections',
                'cost_price' => 12.75,
                'selling_price' => 24.99,
                'alert_quantity' => 25,
                'batches' => [
                    ['quantity' => 50, 'expiry_months' => 18, 'cost_price' => 12.75],
                    ['quantity' => 40, 'expiry_months' => 14, 'cost_price' => 12.60],
                ]
            ],

            // Vitamins & Supplements
            [
                'name' => 'Vitamin C 1000mg Tablets',
                'code' => 'VTC1000',
                'category' => 'Vitamins',
                'description' => 'Ascorbic acid immune system support',
                'cost_price' => 4.25,
                'selling_price' => 8.99,
                'alert_quantity' => 60,
                'batches' => [
                    ['quantity' => 180, 'expiry_months' => 30, 'cost_price' => 4.25],
                    ['quantity' => 120, 'expiry_months' => 24, 'cost_price' => 4.20],
                    ['quantity' => 90, 'expiry_months' => 4, 'cost_price' => 4.30], // Near expiry
                ]
            ],
            [
                'name' => 'Vitamin D3 2000 IU Softgels',
                'code' => 'VTD2000',
                'category' => 'Vitamins',
                'description' => 'Cholecalciferol for bone health',
                'cost_price' => 6.80,
                'selling_price' => 12.99,
                'alert_quantity' => 40,
                'batches' => [
                    ['quantity' => 100, 'expiry_months' => 28, 'cost_price' => 6.80],
                    ['quantity' => 75, 'expiry_months' => 20, 'cost_price' => 6.75],
                ]
            ],
            [
                'name' => 'Multivitamin Complex',
                'code' => 'MVC100',
                'category' => 'Vitamins',
                'description' => 'Complete daily vitamin and mineral supplement',
                'cost_price' => 9.50,
                'selling_price' => 18.99,
                'alert_quantity' => 35,
                'batches' => [
                    ['quantity' => 85, 'expiry_months' => 26, 'cost_price' => 9.50],
                    ['quantity' => 65, 'expiry_months' => 18, 'cost_price' => 9.40],
                ]
            ],

            // Cold & Flu
            [
                'name' => 'Cough Syrup 200ml',
                'code' => 'CGH200',
                'category' => 'Cold & Flu',
                'description' => 'Dextromethorphan cough suppressant syrup',
                'cost_price' => 5.75,
                'selling_price' => 11.49,
                'alert_quantity' => 25,
                'batches' => [
                    ['quantity' => 60, 'expiry_months' => 14, 'cost_price' => 5.75],
                    ['quantity' => 45, 'expiry_months' => 10, 'cost_price' => 5.70],
                    ['quantity' => 30, 'expiry_months' => 5, 'cost_price' => 5.80], // Near expiry
                ]
            ],
            [
                'name' => 'Throat Lozenges',
                'code' => 'THR24',
                'category' => 'Cold & Flu',
                'description' => 'Menthol and eucalyptus throat lozenges',
                'cost_price' => 2.25,
                'selling_price' => 4.99,
                'alert_quantity' => 50,
                'batches' => [
                    ['quantity' => 120, 'expiry_months' => 20, 'cost_price' => 2.25],
                    ['quantity' => 80, 'expiry_months' => 16, 'cost_price' => 2.20],
                ]
            ],

            // Digestive Health
            [
                'name' => 'Antacid Tablets',
                'code' => 'ANT100',
                'category' => 'Digestive',
                'description' => 'Calcium carbonate for heartburn relief',
                'cost_price' => 3.40,
                'selling_price' => 6.99,
                'alert_quantity' => 45,
                'batches' => [
                    ['quantity' => 110, 'expiry_months' => 22, 'cost_price' => 3.40],
                    ['quantity' => 85, 'expiry_months' => 18, 'cost_price' => 3.35],
                ]
            ],
            [
                'name' => 'Probiotic Capsules',
                'code' => 'PRB30',
                'category' => 'Digestive',
                'description' => 'Multi-strain probiotic for digestive health',
                'cost_price' => 15.25,
                'selling_price' => 29.99,
                'alert_quantity' => 20,
                'batches' => [
                    ['quantity' => 45, 'expiry_months' => 12, 'cost_price' => 15.25],
                    ['quantity' => 35, 'expiry_months' => 8, 'cost_price' => 15.10],
                ]
            ],

            // Allergy & Respiratory
            [
                'name' => 'Antihistamine 10mg Tablets',
                'code' => 'ANH10',
                'category' => 'Allergy',
                'description' => 'Loratadine for allergy relief',
                'cost_price' => 4.60,
                'selling_price' => 9.49,
                'alert_quantity' => 35,
                'batches' => [
                    ['quantity' => 90, 'expiry_months' => 24, 'cost_price' => 4.60],
                    ['quantity' => 70, 'expiry_months' => 16, 'cost_price' => 4.55],
                ]
            ],
            [
                'name' => 'Nasal Decongestant Spray',
                'code' => 'NDS15',
                'category' => 'Allergy',
                'description' => 'Oxymetazoline nasal spray',
                'cost_price' => 6.25,
                'selling_price' => 12.99,
                'alert_quantity' => 30,
                'batches' => [
                    ['quantity' => 55, 'expiry_months' => 18, 'cost_price' => 6.25],
                    ['quantity' => 40, 'expiry_months' => 12, 'cost_price' => 6.20],
                ]
            ],

            // Topical & External
            [
                'name' => 'Antiseptic Cream 50g',
                'code' => 'ASC50',
                'category' => 'Topical',
                'description' => 'Antibiotic cream for minor cuts and wounds',
                'cost_price' => 4.80,
                'selling_price' => 9.99,
                'alert_quantity' => 40,
                'batches' => [
                    ['quantity' => 75, 'expiry_months' => 20, 'cost_price' => 4.80],
                    ['quantity' => 60, 'expiry_months' => 14, 'cost_price' => 4.75],
                ]
            ],
            [
                'name' => 'Hydrocortisone Cream 1%',
                'code' => 'HYD1',
                'category' => 'Topical',
                'description' => 'Anti-inflammatory cream for skin conditions',
                'cost_price' => 7.90,
                'selling_price' => 15.49,
                'alert_quantity' => 25,
                'batches' => [
                    ['quantity' => 50, 'expiry_months' => 16, 'cost_price' => 7.90],
                    ['quantity' => 35, 'expiry_months' => 10, 'cost_price' => 7.85],
                ]
            ],
        ];

        foreach ($products as $productData) {
            $batches = $productData['batches'];
            unset($productData['batches']);

            // Map the old category name to the new category ID
            $oldCategoryName = $productData['category'];
            $newCategoryName = $categoryMapping[$oldCategoryName] ?? 'OTC Medications';
            $category = Category::where('name', $newCategoryName)->first();

            if ($category) {
                $productData['category_id'] = $category->id;
            }

            // Map the product name to subcategory
            $productName = $productData['name'];
            if (isset($subcategoryMapping[$productName])) {
                $subcategoryName = $subcategoryMapping[$productName];
                $subcategory = \App\Models\Subcategory::where('name', $subcategoryName)->first();
                if ($subcategory) {
                    $productData['subcategory_id'] = $subcategory->id;
                }
            }

            $product = Product::updateOrCreate(
                ['code' => $productData['code']],
                $productData
            );

            // Create batches for each product
            foreach ($batches as $batchData) {
                $this->createBatch($product, $batchData);
            }
        }
    }

    private function createBatch(Product $product, array $batchData): void
    {
        $expiryDate = Carbon::now()->addMonths($batchData['expiry_months']);
        $batchNumber = $this->generateBatchNumber($product->code);

        Batch::create([
            'product_id' => $product->id,
            'batch_number' => $batchNumber,
            'quantity' => $batchData['quantity'],
            'cost_price' => $batchData['cost_price'],
            'expiry_date' => $expiryDate,
        ]);
    }

    private function generateBatchNumber(string $productCode): string
    {
        $year = date('Y');
        $month = date('m');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        return "{$productCode}-{$year}{$month}-{$random}";
    }
}

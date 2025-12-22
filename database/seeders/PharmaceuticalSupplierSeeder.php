<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PharmaceuticalSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createPharmaceuticalSuppliers();
    }

    private function createPharmaceuticalSuppliers(): void
    {
        $suppliers = [
            [
                'name' => 'MediSupply International',
                'contact_person' => 'Dr. Robert Anderson',
                'phone' => '+1-555-0123',
                'email' => 'orders@medisupply.com',
                'address' => '1234 Pharmaceutical Ave, Medical District, NY 10001, USA',
                'is_active' => true,
                'notes' => 'Primary supplier for pain relief medications and antibiotics. Excellent delivery times.',
            ],
            [
                'name' => 'PharmaCorp Distributors',
                'contact_person' => 'Sarah Mitchell',
                'phone' => '+1-555-0234',
                'email' => 'procurement@pharmacorp.com',
                'address' => '5678 Healthcare Blvd, Pharma City, CA 90210, USA',
                'is_active' => true,
                'notes' => 'Specializes in vitamins and supplements. Competitive pricing on bulk orders.',
            ],
            [
                'name' => 'VitaHealth Distributors',
                'contact_person' => 'Michael Chen',
                'phone' => '+1-555-0345',
                'email' => 'sales@vitahealth.com',
                'address' => '9012 Wellness Way, Health Plaza, TX 75001, USA',
                'is_active' => true,
                'notes' => 'Leading supplier of vitamins, minerals, and nutritional supplements.',
            ],
            [
                'name' => 'Global Pharma Solutions',
                'contact_person' => 'Dr. Emily Rodriguez',
                'phone' => '+1-555-0456',
                'email' => 'orders@globalpharma.com',
                'address' => '3456 Medical Center Dr, Pharma Hub, FL 33101, USA',
                'is_active' => true,
                'notes' => 'International supplier with extensive antibiotic and specialty medication catalog.',
            ],
            [
                'name' => 'HealthFirst Medical Supply',
                'contact_person' => 'James Wilson',
                'phone' => '+1-555-0567',
                'email' => 'purchasing@healthfirst.com',
                'address' => '7890 Care Street, Medical Mile, IL 60601, USA',
                'is_active' => true,
                'notes' => 'Reliable supplier for cold & flu medications and topical treatments.',
            ],
            [
                'name' => 'BioMed Pharmaceuticals',
                'contact_person' => 'Dr. Lisa Thompson',
                'phone' => '+1-555-0678',
                'email' => 'orders@biomed-pharma.com',
                'address' => '2468 Research Parkway, BioTech Center, MA 02101, USA',
                'is_active' => true,
                'notes' => 'Cutting-edge pharmaceutical research company with premium product lines.',
            ],
            [
                'name' => 'MedTech Distributors',
                'contact_person' => 'Carlos Martinez',
                'phone' => '+1-555-0789',
                'email' => 'sales@medtech-dist.com',
                'address' => '1357 Technology Drive, Innovation Park, WA 98101, USA',
                'is_active' => true,
                'notes' => 'Technology-focused distributor with advanced inventory management systems.',
            ],
            [
                'name' => 'Regional Health Supply Co.',
                'contact_person' => 'Anna Petrov',
                'phone' => '+1-555-0890',
                'email' => 'orders@regionalhealthsupply.com',
                'address' => '8642 Regional Plaza, Commerce Center, OH 44101, USA',
                'is_active' => true,
                'notes' => 'Regional distributor with fast local delivery and competitive pricing.',
            ],
            [
                'name' => 'Premier Pharmaceutical Group',
                'contact_person' => 'Dr. David Kim',
                'phone' => '+1-555-0901',
                'email' => 'procurement@premierpharma.com',
                'address' => '9753 Premier Boulevard, Executive District, GA 30301, USA',
                'is_active' => true,
                'notes' => 'Premium pharmaceutical group with exclusive access to specialty medications.',
            ],
            [
                'name' => 'Wellness Wholesale Inc.',
                'contact_person' => 'Jennifer Brown',
                'phone' => '+1-555-1012',
                'email' => 'orders@wellnesswholesale.com',
                'address' => '4681 Wholesale Way, Distribution Hub, AZ 85001, USA',
                'is_active' => true,
                'notes' => 'Wholesale distributor specializing in over-the-counter medications and health products.',
            ],
            [
                'name' => 'Metro Medical Supplies',
                'contact_person' => 'Thomas Anderson',
                'phone' => '+1-555-1123',
                'email' => 'sales@metromedical.com',
                'address' => '1122 Metro Center, Urban Plaza, NV 89101, USA',
                'is_active' => true,
                'notes' => 'Urban-focused supplier with same-day delivery options for emergency orders.',
            ],
            [
                'name' => 'Advanced Therapeutics Ltd.',
                'contact_person' => 'Dr. Maria Garcia',
                'phone' => '+1-555-1234',
                'email' => 'orders@advancedtherapeutics.com',
                'address' => '5544 Therapeutic Lane, Research Triangle, NC 27601, USA',
                'is_active' => true,
                'notes' => 'Specialized in advanced therapeutic medications and clinical trial supplies.',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::updateOrCreate(
                ['email' => $supplierData['email']],
                $supplierData
            );
        }
    }
}

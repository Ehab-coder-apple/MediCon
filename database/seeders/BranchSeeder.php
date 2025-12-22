<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'MediCon Main Pharmacy',
                'code' => 'MAIN',
                'description' => 'Main pharmacy location with full services',
                'address' => '123 Healthcare Ave',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10001',
                'latitude' => 40.7589, // Times Square area for demo
                'longitude' => -73.9851,
                'geofence_radius' => 100,
                'phone' => '+1-555-0123',
                'email' => 'main@medicon.com',
                'manager_name' => 'John Smith',
                'operating_hours' => Branch::getDefaultOperatingHours(),
                'is_active' => true,
                'requires_geofencing' => true,
                'settings' => [
                    'allow_early_checkin' => 15, // minutes
                    'allow_late_checkout' => 30, // minutes
                    'require_photo_verification' => false,
                    'send_location_alerts' => true,
                ],
            ],
            [
                'name' => 'MediCon Downtown Branch',
                'code' => 'DOWNTOWN',
                'description' => 'Downtown branch serving the business district',
                'address' => '456 Business Blvd',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10002',
                'latitude' => 40.7505, // Financial District area
                'longitude' => -74.0087,
                'geofence_radius' => 150,
                'phone' => '+1-555-0124',
                'email' => 'downtown@medicon.com',
                'manager_name' => 'Sarah Johnson',
                'operating_hours' => [
                    'monday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                    'tuesday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                    'wednesday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                    'thursday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                    'friday' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
                    'saturday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                    'sunday' => ['open' => '00:00', 'close' => '00:00', 'closed' => true],
                ],
                'is_active' => true,
                'requires_geofencing' => true,
                'settings' => [
                    'allow_early_checkin' => 10,
                    'allow_late_checkout' => 15,
                    'require_photo_verification' => true,
                    'send_location_alerts' => true,
                ],
            ],
            [
                'name' => 'MediCon Suburban Pharmacy',
                'code' => 'SUBURBAN',
                'description' => 'Family-friendly suburban location',
                'address' => '789 Family Way',
                'city' => 'Brooklyn',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '11201',
                'latitude' => 40.6892, // Brooklyn Heights area
                'longitude' => -73.9942,
                'geofence_radius' => 200,
                'phone' => '+1-555-0125',
                'email' => 'suburban@medicon.com',
                'manager_name' => 'Michael Davis',
                'operating_hours' => Branch::getDefaultOperatingHours(),
                'is_active' => true,
                'requires_geofencing' => false, // More relaxed for suburban location
                'settings' => [
                    'allow_early_checkin' => 20,
                    'allow_late_checkout' => 45,
                    'require_photo_verification' => false,
                    'send_location_alerts' => false,
                ],
            ],
        ];

        foreach ($branches as $branchData) {
            Branch::updateOrCreate(
                ['code' => $branchData['code']],
                $branchData
            );
        }

        $this->command->info('Created ' . count($branches) . ' branch locations with GPS coordinates');
    }
}

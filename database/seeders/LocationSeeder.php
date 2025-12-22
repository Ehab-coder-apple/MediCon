<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            // Front Store locations
            ['zone' => 'Front Store', 'cabinet_shelf' => 'Shelf A', 'row_level' => 'Row 1', 'position_side' => 'Position 1'],
            ['zone' => 'Front Store', 'cabinet_shelf' => 'Shelf A', 'row_level' => 'Row 1', 'position_side' => 'Position 2'],
            ['zone' => 'Front Store', 'cabinet_shelf' => 'Shelf A', 'row_level' => 'Row 2', 'position_side' => 'Position 1'],
            ['zone' => 'Front Store', 'cabinet_shelf' => 'Shelf A', 'row_level' => 'Row 2', 'position_side' => 'Position 2'],
            ['zone' => 'Front Store', 'cabinet_shelf' => 'Shelf B', 'row_level' => 'Row 1', 'position_side' => 'Left'],
            ['zone' => 'Front Store', 'cabinet_shelf' => 'Shelf B', 'row_level' => 'Row 1', 'position_side' => 'Right'],

            // OTC Section
            ['zone' => 'OTC Section', 'cabinet_shelf' => 'Display A', 'row_level' => 'Top', 'position_side' => 'Left'],
            ['zone' => 'OTC Section', 'cabinet_shelf' => 'Display A', 'row_level' => 'Top', 'position_side' => 'Right'],
            ['zone' => 'OTC Section', 'cabinet_shelf' => 'Display A', 'row_level' => 'Bottom', 'position_side' => 'Left'],
            ['zone' => 'OTC Section', 'cabinet_shelf' => 'Display B', 'row_level' => 'Eye Level', 'position_side' => 'Center'],

            // Prescription Zone
            ['zone' => 'Prescription Zone', 'cabinet_shelf' => 'Cabinet 1', 'row_level' => 'Shelf 1', 'position_side' => 'A-F'],
            ['zone' => 'Prescription Zone', 'cabinet_shelf' => 'Cabinet 1', 'row_level' => 'Shelf 1', 'position_side' => 'G-M'],
            ['zone' => 'Prescription Zone', 'cabinet_shelf' => 'Cabinet 1', 'row_level' => 'Shelf 2', 'position_side' => 'N-S'],
            ['zone' => 'Prescription Zone', 'cabinet_shelf' => 'Cabinet 2', 'row_level' => 'Shelf 1', 'position_side' => 'T-Z'],

            // Storage Room
            ['zone' => 'Storage Room', 'cabinet_shelf' => 'Rack 1', 'row_level' => 'Level 1', 'position_side' => 'Left'],
            ['zone' => 'Storage Room', 'cabinet_shelf' => 'Rack 1', 'row_level' => 'Level 2', 'position_side' => 'Right'],
            ['zone' => 'Storage Room', 'cabinet_shelf' => 'Rack 2', 'row_level' => 'Level 1', 'position_side' => 'Center'],

            // Controlled Access Cabinet
            ['zone' => 'Controlled Cabinet', 'cabinet_shelf' => 'Compartment 1', 'row_level' => null, 'position_side' => null],
            ['zone' => 'Controlled Cabinet', 'cabinet_shelf' => 'Compartment 2', 'row_level' => null, 'position_side' => null],
            ['zone' => 'Controlled Cabinet', 'cabinet_shelf' => 'Compartment 3', 'row_level' => null, 'position_side' => null],

            // Fridge (Cold Chain)
            ['zone' => 'Fridge', 'cabinet_shelf' => 'Drawer 1', 'row_level' => null, 'position_side' => null],
            ['zone' => 'Fridge', 'cabinet_shelf' => 'Drawer 2', 'row_level' => null, 'position_side' => null],
            ['zone' => 'Fridge', 'cabinet_shelf' => 'Door Shelf', 'row_level' => 'Top', 'position_side' => null],
            ['zone' => 'Fridge', 'cabinet_shelf' => 'Door Shelf', 'row_level' => 'Bottom', 'position_side' => null],

            // Baby Care Area
            ['zone' => 'Baby Care Area', 'cabinet_shelf' => 'Display Unit', 'row_level' => 'Top Shelf', 'position_side' => 'Left'],
            ['zone' => 'Baby Care Area', 'cabinet_shelf' => 'Display Unit', 'row_level' => 'Middle Shelf', 'position_side' => 'Center'],
            ['zone' => 'Baby Care Area', 'cabinet_shelf' => 'Display Unit', 'row_level' => 'Bottom Shelf', 'position_side' => 'Right'],

            // Cosmetics Section
            ['zone' => 'Cosmetics Section', 'cabinet_shelf' => 'Beauty Counter', 'row_level' => 'Display', 'position_side' => 'Front'],
            ['zone' => 'Cosmetics Section', 'cabinet_shelf' => 'Beauty Counter', 'row_level' => 'Storage', 'position_side' => 'Back'],
        ];

        foreach ($locations as $locationData) {
            $fullLocation = $this->generateFullLocation($locationData);

            Location::create([
                'zone' => $locationData['zone'],
                'cabinet_shelf' => $locationData['cabinet_shelf'],
                'row_level' => $locationData['row_level'],
                'position_side' => $locationData['position_side'],
                'full_location' => $fullLocation,
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }
    }

    private function generateFullLocation(array $locationData): string
    {
        $parts = array_filter([
            $locationData['zone'],
            $locationData['cabinet_shelf'],
            $locationData['row_level'],
            $locationData['position_side']
        ]);

        return implode(' > ', $parts);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use App\Models\Tenant;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        $leaveTypes = [
            [
                'name' => 'Sick Leave',
                'description' => 'Leave for medical reasons',
                'annual_limit' => 12,
                'requires_approval' => true,
            ],
            [
                'name' => 'Casual Leave',
                'description' => 'General casual leave',
                'annual_limit' => 10,
                'requires_approval' => true,
            ],
            [
                'name' => 'Annual Leave',
                'description' => 'Paid annual vacation',
                'annual_limit' => 20,
                'requires_approval' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for maternity',
                'annual_limit' => 90,
                'requires_approval' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for paternity',
                'annual_limit' => 10,
                'requires_approval' => true,
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $type['name'],
                ],
                [
                    'description' => $type['description'],
                    'annual_limit' => $type['annual_limit'],
                    'requires_approval' => $type['requires_approval'],
                    'is_active' => true,
                ]
            );
        }
    }
}

<?php

namespace App;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PHARMACIST = 'pharmacist';
    case SALES_STAFF = 'sales_staff';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::PHARMACIST => 'Pharmacist',
            self::SALES_STAFF => 'Sales Staff',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN => [
                'manage_users',
                'manage_inventory',
                'view_reports',
                'manage_sales',
                'manage_system'
            ],
            self::PHARMACIST => [
                'manage_inventory',
                'view_reports',
                'manage_prescriptions'
            ],
            self::SALES_STAFF => [
                'manage_sales',
                'view_inventory'
            ],
        };
    }
}

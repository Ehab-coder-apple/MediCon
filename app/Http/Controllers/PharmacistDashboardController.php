<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PharmacistDashboardController extends Controller
{
    public function index()
    {
        // Sample data for pharmacist dashboard
        $salesToday = 25;
        $inventoryAlerts = 5;
        $totalMedicines = 150;

        return view('pharmacist.dashboard', compact(
            'salesToday',
            'inventoryAlerts',
            'totalMedicines'
        ));
    }

    public function inventory()
    {
        return view('pharmacist.inventory');
    }


}

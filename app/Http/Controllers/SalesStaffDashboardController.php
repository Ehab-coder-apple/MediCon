<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesStaffDashboardController extends Controller
{
    public function index()
    {
        // Sample data for sales staff dashboard
        $salesToday = 1250.50;
        $ordersToday = 15;
        $pendingOrders = 3;

        return view('sales.dashboard', compact(
            'salesToday',
            'ordersToday',
            'pendingOrders'
        ));
    }

    public function sales()
    {
        return view('sales.sales');
    }

    public function orders()
    {
        return view('sales.orders');
    }
}

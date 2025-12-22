<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Traits\HasRoleBasedRouting;

class CustomerController extends Controller
{
    use HasRoleBasedRouting;
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $customers = Customer::withCount(['sales', 'invoices'])
            ->orderBy('name')
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers',
        ]);

        Customer::create($validated);

        return $this->redirectToIndex('customers', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): View
    {
        $customer->load([
            'sales' => function($query) {
                $query->with('user')->latest('sale_date');
            }
        ]);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
        ]);

        $customer->update($validated);

        return $this->redirectToIndex('customers', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        // Check if customer has any sales
        if ($customer->sales()->count() > 0) {
            return $this->redirectToIndex('customers', 'Cannot delete customer with existing sales.', 'error');
        }

        $customer->delete();

        return $this->redirectToIndex('customers', 'Customer deleted successfully.');
    }

    /**
     * Search customers for AJAX requests
     */
    public function search(Request $request)
    {
        $query = $request->get('query');

        if (!$query) {
            return response()->json(['customers' => []]);
        }

        $customers = Customer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'customers' => $customers->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'contact_info' => $customer->contact_info,
                ];
            })
        ]);
    }
}

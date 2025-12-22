<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Location::class);

        $query = Location::with('products')->withCount('products');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('zone', 'LIKE', "%{$search}%")
                  ->orWhere('cabinet_shelf', 'LIKE', "%{$search}%")
                  ->orWhere('row_level', 'LIKE', "%{$search}%")
                  ->orWhere('position_side', 'LIKE', "%{$search}%")
                  ->orWhere('full_location', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('zone')) {
            $query->where('zone', $request->get('zone'));
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'occupied') {
                $query->has('products');
            } elseif ($status === 'empty') {
                $query->doesntHave('products');
            }
        }

        $locations = $query->ordered()->paginate(15)->withQueryString();
        $zones = Location::getZones();

        return view('admin.locations.index', compact('locations', 'zones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Location::class);

        $zones = Location::getZones();

        return view('admin.locations.create', compact('zones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Location::class);

        $validated = $request->validate([
            'zone' => 'required|string|max:255',
            'cabinet_shelf' => 'nullable|string|max:255',
            'row_level' => 'nullable|string|max:255',
            'position_side' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Check for duplicate location
        $existingLocation = Location::where('zone', $validated['zone'])
            ->where('cabinet_shelf', $validated['cabinet_shelf'] ?? null)
            ->where('row_level', $validated['row_level'] ?? null)
            ->where('position_side', $validated['position_side'] ?? null)
            ->first();

        if ($existingLocation) {
            return back()->withErrors(['location' => 'This location already exists.'])->withInput();
        }

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location): View
    {
        $this->authorize('view', $location);

        $location->load(['products.category', 'products.subcategory', 'products.batches']);

        return view('admin.locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location): View
    {
        $this->authorize('update', $location);

        $zones = Location::getZones();

        return view('admin.locations.edit', compact('location', 'zones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location): RedirectResponse
    {
        $this->authorize('update', $location);

        $validated = $request->validate([
            'zone' => 'required|string|max:255',
            'cabinet_shelf' => 'nullable|string|max:255',
            'row_level' => 'nullable|string|max:255',
            'position_side' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Check for duplicate location (excluding current location)
        $existingLocation = Location::where('zone', $validated['zone'])
            ->where('cabinet_shelf', $validated['cabinet_shelf'] ?? null)
            ->where('row_level', $validated['row_level'] ?? null)
            ->where('position_side', $validated['position_side'] ?? null)
            ->where('id', '!=', $location->id)
            ->first();

        if ($existingLocation) {
            return back()->withErrors(['location' => 'This location already exists.'])->withInput();
        }

        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location): RedirectResponse
    {
        $this->authorize('delete', $location);

        // Check if location has products
        if ($location->products()->count() > 0) {
            return redirect()->route('admin.locations.index')
                ->with('error', 'Cannot delete location that has products assigned to it.');
        }

        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}

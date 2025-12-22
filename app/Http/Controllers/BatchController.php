<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Batch::class);

        $query = Batch::with('product');

        // Filter by product name
        if ($request->filled('product_name')) {
            $productName = $request->get('product_name');
            $query->whereHas('product', function ($q) use ($productName) {
                $q->where('name', 'like', '%' . $productName . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            switch ($status) {
                case 'active':
                    $query->active();
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'expiring_soon':
                    $query->expiringSoon();
                    break;
                case 'out_of_stock':
                    $query->where('quantity', 0);
                    break;
            }
        }

        // Filter by manufacturer
        if ($request->filled('manufacturer')) {
            $manufacturer = $request->get('manufacturer');
            $query->where(function ($q) use ($manufacturer) {
                $q->where('manufacturer', 'like', '%' . $manufacturer . '%')
                  ->orWhereHas('product', function ($subQ) use ($manufacturer) {
                      $subQ->where('manufacturer', 'like', '%' . $manufacturer . '%');
                  });
            });
        }

        $batches = $query->orderBy('expiry_date')->paginate(15);

        // Get unique manufacturers for the dropdown (from both batches and products)
        $batchManufacturers = Batch::whereNotNull('manufacturer')
            ->where('manufacturer', '!=', '')
            ->distinct()
            ->pluck('manufacturer');

        $productManufacturers = Product::whereNotNull('manufacturer')
            ->where('manufacturer', '!=', '')
            ->distinct()
            ->pluck('manufacturer');

        $manufacturers = $batchManufacturers->merge($productManufacturers)
            ->unique()
            ->sort()
            ->values();

        return view('batches.index', compact('batches', 'manufacturers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Batch::class);

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $selectedProduct = $request->get('product_id') ? Product::find($request->get('product_id')) : null;

        return view('batches.create', compact('products', 'selectedProduct'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Batch::class);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|max:255',
            'expiry_date' => 'required|date|after:today',
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'nullable|numeric|min:0',
        ]);

        // Check for duplicate batch number for the same product
        $existingBatch = Batch::where('product_id', $validated['product_id'])
            ->where('batch_number', $validated['batch_number'])
            ->first();

        if ($existingBatch) {
            return back()->withErrors(['batch_number' => 'This batch number already exists for this product.'])
                ->withInput();
        }

        Batch::create($validated);

        return redirect()->route('batches.index')
            ->with('success', 'Batch created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Batch $batch): View
    {
        $this->authorize('view', $batch);

        $batch->load('product');
        return view('batches.show', compact('batch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Batch $batch): View
    {
        $this->authorize('update', $batch);

        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('batches.edit', compact('batch', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Batch $batch): RedirectResponse
    {
        $this->authorize('update', $batch);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|max:255',
            'expiry_date' => 'required|date',
            'quantity' => 'required|integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
        ]);

        // Check for duplicate batch number for the same product (excluding current batch)
        $existingBatch = Batch::where('product_id', $validated['product_id'])
            ->where('batch_number', $validated['batch_number'])
            ->where('id', '!=', $batch->id)
            ->first();

        if ($existingBatch) {
            return back()->withErrors(['batch_number' => 'This batch number already exists for this product.'])
                ->withInput();
        }

        $batch->update($validated);

        return redirect()->route('batches.index')
            ->with('success', 'Batch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Batch $batch): RedirectResponse
    {
        $this->authorize('delete', $batch);

        $batch->delete();

        return redirect()->route('batches.index')
            ->with('success', 'Batch deleted successfully.');
    }
}

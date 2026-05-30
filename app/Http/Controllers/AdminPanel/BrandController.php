<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Brand::query()->withCount('products');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Map/transform the paginated results for the frontend without using getCollection()
        $brandsPaginator = $query->latest()->paginate(10);
        $brandsData = collect($brandsPaginator->items())->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'image' => $brand->image,
                'products_count' => $brand->products_count,
                // The 'status' accessor returns 'Active'/'Inactive'
                'status' => $brand->status,
                // We send the raw boolean value for toggles/switches in the UI
                'is_active' => $brand->getAttributes()['status'] == 1,
                'created_at' => $brand->created_at->format('Y-m-d H:i'),
                'updated_at' => $brand->updated_at->format('Y-m-d H:i'),
            ];
        })->all();

        $brands = [
            'data' => $brandsData,
            'current_page' => $brandsPaginator->currentPage(),
            'last_page' => $brandsPaginator->lastPage(),
            'per_page' => $brandsPaginator->perPage(),
            'total' => $brandsPaginator->total(),
            'links' => $brandsPaginator->toArray()['links'],
        ];

        return Inertia::render('Admin/Brand/Index', [
            'brands' => $brands,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Brands/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string', // Expecting URL string from ImageController
            'status' => 'boolean',
        ]);

        Brand::create([
            'name' => $request->name,
            'image' => $request->image,
            'status' => $request->status ? 1 : 0,
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        return Inertia::render('Admin/Brands/Edit', [
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'image' => $brand->image,
                // Pass raw boolean so the toggle switch knows if it's true/false
                'is_active' => $brand->getAttributes()['status'] == 1,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'status' => 'boolean',
        ]);

        // If a new image URL is provided and it's different from the old one, delete the old file
        if ($request->image && $brand->image && $request->image !== $brand->image) {
            $oldImagePath = str_replace(asset('storage/'), '', $brand->image);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImagePath);
        }

        $brand->update([
            'name' => $request->name,
            'image' => $request->image,
            'status' => $request->status ? 1 : 0,
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // Delete image file if it exists in local storage
        if ($brand->image) {
            $imagePath = str_replace(asset('storage/'), '', $brand->image);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
        }

        $brand->delete();
        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted.');
    }
}

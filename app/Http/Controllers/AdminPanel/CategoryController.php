<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Category;
use App\Models\AdminPanel\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('brand');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Manual transformation structure matching your BrandController
        $categoriesPaginator = $query->latest()->paginate(10);
        $categoriesData = collect($categoriesPaginator->items())->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'brand_id' => $category->brand_id,
                'description' => $category->description,
                'image' => $category->image,
                'brand' => $category->brand ? $category->brand->name : 'N/A',
                // 'status' returns 'Active'/'Inactive' via model accessor
                'status' => $category->status, 
                // 'is_active' returns raw boolean for the toggle switch
                'is_active' => $category->getAttributes()['status'] == 1,
                'created_at' => $category->created_at->format('Y-m-d H:i'),
                'updated_at' => $category->updated_at->format('Y-m-d H:i'),
            ];
        })->all();

        $categories = [
            'data' => $categoriesData,
            'current_page' => $categoriesPaginator->currentPage(),
            'last_page' => $categoriesPaginator->lastPage(),
            'per_page' => $categoriesPaginator->perPage(),
            'total' => $categoriesPaginator->total(),
            'links' => $categoriesPaginator->toArray()['links'],
        ];

        return Inertia::render('Admin/Category/Index', [
            'categories' => $categories,
            'brands' => Brand::select('id', 'name')->get(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categories/Create', [
            'brands' => Brand::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'boolean',
        ]);

        Category::create([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'description' => $request->description,
            'image' => $request->image,
            'status' => $request->status ? 1 : 0,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'brand_id' => $category->brand_id,
                'description' => $category->description,
                'image' => $category->image,
                'is_active' => $category->getAttributes()['status'] == 1,
            ],
            'brands' => Brand::select('id', 'name')->get()
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'boolean',
        ]);

        // Delete old image if a new one is uploaded
        if ($request->image && $category->image && $request->image !== $category->image) {
            $oldImagePath = str_replace(asset('storage/'), '', $category->image);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImagePath);
        }

        $category->update([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'description' => $request->description,
            'image' => $request->image,
            'status' => $request->status ? 1 : 0,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Delete image file if it exists in local storage
        if ($category->image) {
            $imagePath = str_replace(asset('storage/'), '', $category->image);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
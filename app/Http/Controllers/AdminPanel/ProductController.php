<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Product;
use App\Models\AdminPanel\Brand;
use App\Models\AdminPanel\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('product_code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Manual transformation structure matching your BrandController
        $productsPaginator = $query->latest()->paginate(10);
        $productsData = collect($productsPaginator->items())->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->product_code,
                'price' => $product->price,
                'stock' => $product->stock,
                'image' => $product->image,
                'category_id' => $product->category_id,
                'brand_id' => $product->brand_id,
                'category' => $product->category ? $product->category->name : 'N/A',
                'brand' => $product->brand ? $product->brand->name : 'N/A',
                'status' => $product->status,
                'is_active' => $product->getAttributes()['status'] == 1,
                'created_at' => $product->created_at->format('Y-m-d H:i'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i'),
            ];
        })->all();

        $products = [
            'data' => $productsData,
            'current_page' => $productsPaginator->currentPage(),
            'last_page' => $productsPaginator->lastPage(),
            'per_page' => $productsPaginator->perPage(),
            'total' => $productsPaginator->total(),
        ];

        return Inertia::render('Admin/Product/Index', [
            'products' => $products,
            'categories' => Category::select('id', 'name')->get(),
            'brands' => Brand::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'from_date', 'to_date']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'brands' => Brand::select('id', 'name')->get(),
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'required|string|unique:products,product_code',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'boolean',
        ]);

        Product::create([
            'name' => $request->name,
            'product_code' => $request->product_code,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'description' => $request->description,
            'image' => $request->image,
            'status' => $request->status ? 1 : 0,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code,
                'price' => $product->price,
                'stock' => $product->stock,
                'category_id' => $product->category_id,
                'brand_id' => $product->brand_id,
                'description' => $product->description,
                'image' => $product->image,
                'is_active' => $product->getAttributes()['status'] == 1,
            ],
            'brands' => Brand::select('id', 'name')->get(),
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => ['required', 'string', Rule::unique('products')->ignore($product->id)],
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'boolean',
        ]);

        // Delete old image if a new one is uploaded
        if ($request->image && $product->image && $request->image !== $product->image) {
            $oldImagePath = str_replace(asset('storage/'), '', $product->image);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImagePath);
        }

        $product->update([
            'name' => $request->name,
            'product_code' => $request->product_code,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'description' => $request->description,
            'image' => $request->image,
            'status' => $request->status ? 1 : 0,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete image file if it exists in local storage
        if ($product->image) {
            $imagePath = str_replace(asset('storage/'), '', $product->image);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
        }

        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}
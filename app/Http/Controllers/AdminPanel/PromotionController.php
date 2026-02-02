<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Promotion;
use App\Models\AdminPanel\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        // Auto-expire promotions that have reached their end date
        Promotion::where('status', '!=', 'EXPIRED')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->update(['status' => 'EXPIRED']);

        $query = Promotion::with('product');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $promosPaginator = $query->latest()->paginate(10);
        $promosData = collect($promosPaginator->items())->map(function ($promo) {
            return [
                'id' => $promo->id,
                'code' => $promo->code,
                'name' => $promo->name,
                'description' => $promo->description,
                'type' => $promo->promo_type,
                'value' => $promo->discount_value,
                'product_id' => $promo->product_id,
                'product_name' => $promo->product ? $promo->product->name : 'Global',
                'buy_qty' => $promo->buy_qty,
                'get_qty' => $promo->get_qty,
                'status' => $promo->status,
                'status_color' => $promo->status_color, // Using accessor
                'start_date' => $promo->start_date ? $promo->start_date->format('Y-m-d') : null,
                'end_date' => $promo->end_date ? $promo->end_date->format('Y-m-d') : null,
                'created_at' => $promo->created_at->format('Y-m-d H:i'),
            ];
        })->all();

        $promotions = [
            'data' => $promosData,
            'current_page' => $promosPaginator->currentPage(),
            'last_page' => $promosPaginator->lastPage(),
            'per_page' => $promosPaginator->perPage(),
            'total' => $promosPaginator->total(),
            'links' => $promosPaginator->linkCollection()->toArray(),
        ];

        return Inertia::render('Admin/Promotion/Index', [
            'promotions' => $promotions,
            'products' => Product::select('id', 'name')->get(), 
            'filters' => $request->only(['search', 'from_date', 'to_date']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:promotions,code|max:50',
            'name' => 'required|string|max:255',
            'promo_type' => 'required|in:PERCENTAGE,BOGO',
            'discount_value' => 'required_if:promo_type,PERCENTAGE|nullable|numeric|min:0',
            'buy_qty' => 'required_if:promo_type,BOGO|nullable|integer|min:1',
            'get_qty' => 'required_if:promo_type,BOGO|nullable|integer|min:1',
            'product_id' => [
                'nullable', 
                'exists:products,id', 
                Rule::unique('promotions')->whereNotNull('product_id')
            ],
            'status' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        Promotion::create(array_merge($request->all(), [
            'discount_value' => $request->promo_type === 'PERCENTAGE' ? $request->discount_value : 0,
            'buy_qty' => $request->promo_type === 'BOGO' ? $request->buy_qty : null,
            'get_qty' => $request->promo_type === 'BOGO' ? $request->get_qty : null,
        ]));

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion created successfully.');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('promotions')->ignore($promotion->id)],
            'name' => 'required|string|max:255',
            'promo_type' => 'required|in:PERCENTAGE,BOGO',
            'discount_value' => 'required_if:promo_type,PERCENTAGE|nullable|numeric|min:0',
            'buy_qty' => 'required_if:promo_type,BOGO|nullable|integer|min:1',
            'get_qty' => 'required_if:promo_type,BOGO|nullable|integer|min:1',
            'product_id' => [
                'nullable', 
                'exists:products,id', 
                Rule::unique('promotions')->ignore($promotion->id)->whereNotNull('product_id')
            ],
            'status' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $promotion->update(array_merge($request->all(), [
            'discount_value' => $request->promo_type === 'PERCENTAGE' ? $request->discount_value : 0,
            'buy_qty' => $request->promo_type === 'BOGO' ? $request->buy_qty : null,
            'get_qty' => $request->promo_type === 'BOGO' ? $request->get_qty : null,
        ]));

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion updated successfully.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return redirect()->route('admin.promotions.index')->with('success', 'Promotion deleted.');
    }
}
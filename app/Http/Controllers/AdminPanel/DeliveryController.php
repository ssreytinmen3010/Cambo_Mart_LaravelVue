<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::query()->latest();

        if ($request->search) {
            $query->where('discount_type', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $deliveriesPaginator = $query->paginate(10);
        $deliveriesData = collect($deliveriesPaginator->items())->map(function ($delivery) {
            $delivery->calculateTotal();

            $subtotal = (float) $delivery->fee_amount_per * (float) $delivery->qty_kg;

            return [
                'id' => $delivery->id,
                'fee_amount_per' => $delivery->fee_amount_per,
                'qty_kg' => $delivery->qty_kg,
                'subtotal' => round($subtotal, 2),
                'total' => $delivery->total,
                'discount_type' => $delivery->discount_type,
                'discount_value' => $delivery->discount_value,
                'created_at' => $delivery->created_at->format('Y-m-d H:i'),
            ];
        })->all();

        $deliveries = [
            'data' => $deliveriesData,
            'current_page' => $deliveriesPaginator->currentPage(),
            'last_page' => $deliveriesPaginator->lastPage(),
            'per_page' => $deliveriesPaginator->perPage(),
            'total' => $deliveriesPaginator->total(),
            'links' => $deliveriesPaginator->linkCollection()->toArray(),
        ];

        return Inertia::render('Admin/Delivery/Index', [
            'deliveries' => $deliveries,
            'filters' => $request->only(['search', 'from_date', 'to_date']),
            'discountTypes' => Delivery::discountTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDelivery($request);
        $validated['qty_kg'] = $validated['qty_kg'] ?? 0;
        $validated['discount_type'] = $validated['discount_type'] ?? '';
        $validated['discount_value'] = $validated['discount_value'] ?? 0;

        $delivery = Delivery::create($validated);
        $delivery->calculateTotal();
        $delivery->save();

        return redirect()->route('admin.deliveries.index')
            ->with('success', 'Delivery fee created successfully.');
    }

    public function update(Request $request, Delivery $delivery)
    {
        $validated = $this->validateDelivery($request);
        $validated['qty_kg'] = $validated['qty_kg'] ?? 0;
        $validated['discount_type'] = $validated['discount_type'] ?? '';
        $validated['discount_value'] = $validated['discount_value'] ?? 0;

        $delivery->update($validated);
        $delivery->calculateTotal();
        $delivery->save();

        return redirect()->route('admin.deliveries.index')
            ->with('success', 'Delivery fee updated successfully.');
    }

    public function destroy(Delivery $delivery)
    {
        $delivery->delete();

        return redirect()->route('admin.deliveries.index')
            ->with('success', 'Delivery fee deleted.');
    }

    private function validateDelivery(Request $request): array
    {
        return $request->validate([
            'fee_amount_per' => ['required', 'numeric', 'min:0'],
            'qty_kg' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', Rule::in(Delivery::discountTypes())],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'cart_id' => ['nullable', 'integer', 'exists:cart,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ]);
    }
}

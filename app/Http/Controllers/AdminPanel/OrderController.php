<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'promotion.product']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($uq) use ($request) {
                      $uq->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('email', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('promotion', function ($pq) use ($request) {
                      $pq->where('code', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->status) {
            $query->where('order_status', $request->status);
        }

        $orders = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user ? $order->user->name : 'Guest',
                'customer_image' => $order->user ? $order->user->image : null,
                'promotion_code' => $order->promotion ? $order->promotion->code : 'None',
                'promotion_description' => $order->promotion ? $order->promotion->description : null,
                'subtotal' => $order->subtotal_amount,
                'discount' => $order->discount_amount,
                'total_amount' => $order->total_amount,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'status_color' => $order->order_status_color,
                'payment_color' => $order->payment_status_color,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'items' => $order->items->map(function ($item) use ($order) {
                    $itemDiscount = 0;
                    $promo = $order->promotion;
                    
                    if ($promo && $promo->promo_type === 'PERCENTAGE') {
                        $isPromoProduct = ($promo->product_id == $item->product_id);
                        $isGlobalPromo = (!$promo->product_id);
                        if ($isPromoProduct || $isGlobalPromo) {
                            $itemDiscount = ($item->qty * $item->unit_price * $promo->discount_value) / 100;
                        }
                    }

                    return [
                        'id' => $item->id,
                        'product_name' => $item->product ? $item->product->name : 'Unknown Product',
                        'image' => $item->product ? $item->product->image : null,
                        'qty' => $item->qty,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->line_total,
                        'discount' => round($itemDiscount, 2),
                    ];
                }),
            ]);

        return Inertia::render('Admin/Order/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'from_date', 'to_date', 'status']),
        ]);
    }

    // Note: 'create' and 'edit' pages are omitted because you are using Modals.
    // Logic is handled in 'store' and 'update'.

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        $order->update([
            'order_status' => $request->order_status,
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->route('admin.orders.index')->with('success', 'Order updated successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id',
            'field' => 'required|in:order_status,payment_status',
            'value' => 'required|string',
        ]);

        Order::whereIn('id', $request->ids)->update([
            $request->field => $request->value
        ]);

        return redirect()->route('admin.orders.index')->with('success', count($request->ids) . ' orders updated.');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }
}
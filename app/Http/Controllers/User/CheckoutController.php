<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AdminPanel\Order;
use App\Models\AdminPanel\OrderItem;
use App\Models\AdminPanel\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:5000'],
            'floor' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:' . Order::PAYMENT_METHOD_CASH . ',' . Order::PAYMENT_METHOD_ONLINE],
        ]);

        return DB::transaction(function () use ($validated) {
            $cart = Cart::query()
                ->where('user_id', Auth::id())
                ->with(['items.product'])
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return back()->withErrors(['cart' => 'Your cart is empty.']);
            }

            $address = Address::query()->create([
                'user_id' => Auth::id(),
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'floor' => $validated['floor'] ?? null,
            ]);

            $order = Order::query()->create([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8)),
                'user_id' => Auth::id(),
                'address_id' => $address->id,
                'promotion_id' => null,
                'subtotal_amount' => $cart->subtotal,
                'discount_amount' => $cart->discount,
                'total_amount' => $cart->total_amount,
                'order_status' => Order::ORDER_PENDING,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => $validated['payment_method'],
            ]);

            foreach ($cart->items as $item) {
                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'cart' => 'One of the products in your cart is no longer available.',
                    ]);
                }

                if ((int) $product->stock < (int) $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Not enough stock for {$product->name}. Available: {$product->stock}.",
                    ]);
                }

                $product->stock = (int) $product->stock - (int) $item->quantity;
                $product->save();

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'qty' => $item->quantity,
                    'unit_price' => (float) $item->price + (float) $item->discount_amount,
                ]);
            }

            $cart->items()->delete();
            $cart->subtotal = 0;
            $cart->discount = 0;
            $cart->total_amount = 0;
            $cart->save();

            return redirect()
                ->route('user.profile')
                ->with('success', 'Order placed successfully.')
                ->with('checkout', [
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                ]);
        });
    }
}

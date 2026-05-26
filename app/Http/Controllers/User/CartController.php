<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $cart = Cart::query()
            ->where('user_id', Auth::id())
            ->with(['items.product.promotion'])
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
                'subtotal' => 0,
                'discount' => 0,
                'total_amount' => 0,
            ]);

            $cart->load(['items.product.promotion']);
        }

        return response()->json([
            'cart' => $cart,
        ]);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($validated) {
            $product = Product::query()
                ->with(['promotion' => fn ($q) => $q->active()])
                ->findOrFail($validated['product_id']);

            $cart = Cart::query()->firstOrCreate(
                ['user_id' => Auth::id()],
                ['subtotal' => 0, 'discount' => 0, 'total_amount' => 0]
            );

            $item = CartItem::query()->firstOrNew([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ]);

            $item->quantity = (int)($item->quantity ?? 0) + (int)$validated['quantity'];

            $pricing = $this->calculateUnitPricing($product);
            $item->price = $pricing['price'];
            $item->discount_value = $pricing['discount_value'];
            $item->discount_amount = $pricing['discount_amount'];
            $item->discount_amount_total = round(((float) $item->discount_amount) * (int) $item->quantity, 2);
            $item->save();

            $cart->recalculateTotals()->save();
            $cart->load(['items.product.promotion']);

            return response()->json(['cart' => $cart]);
        });
    }

    public function updateItem(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($cartItem->cart?->user_id !== Auth::id()) {
            abort(403);
        }

        return DB::transaction(function () use ($cartItem, $validated) {
            $cartItem->update([
                'quantity' => (int)$validated['quantity'],
            ]);

            $cartItem->discount_amount_total = round(((float) $cartItem->discount_amount) * (int) $cartItem->quantity, 2);
            $cartItem->save();

            $cart = $cartItem->cart;
            $cart->recalculateTotals()->save();
            $cart->load(['items.product.promotion']);

            return response()->json(['cart' => $cart]);
        });
    }

    public function removeItem(CartItem $cartItem)
    {
        if ($cartItem->cart?->user_id !== Auth::id()) {
            abort(403);
        }

        return DB::transaction(function () use ($cartItem) {
            $cart = $cartItem->cart;
            $cartItem->delete();

            $cart->recalculateTotals()->save();
            $cart->load(['items.product.promotion']);

            return response()->json(['cart' => $cart]);
        });
    }

    public function clear()
    {
        return DB::transaction(function () {
            $cart = Cart::query()->where('user_id', Auth::id())->first();
            if (!$cart) {
                return response()->json(['cart' => null]);
            }

            $cart->items()->delete();
            $cart->subtotal = 0;
            $cart->discount = 0;
            $cart->total_amount = 0;
            $cart->save();

            $cart->load(['items.product.promotion']);
            return response()->json(['cart' => $cart]);
        });
    }

    private function calculateUnitPrice(Product $product): float
    {
        return $this->calculateUnitPricing($product)['price'];
    }

    private function calculateUnitPricing(Product $product): array
    {
        $basePrice = (float) $product->price;

        $promotion = $product->promotion;
        if (!$promotion) {
            return [
                'price' => $basePrice,
                'discount_value' => 0.0,
                'discount_amount' => 0.0,
            ];
        }

        if ($promotion->promo_type === 'PERCENTAGE') {
            $discountValue = (float) ($promotion->discount_value ?? 0);
            $price = max(0, round($basePrice * (1 - $discountValue / 100), 2));
            $discountAmount = max(0, round($basePrice - $price, 2));

            return [
                'price' => $price,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
            ];
        }

        return [
            'price' => $basePrice,
            'discount_value' => 0.0,
            'discount_amount' => 0.0,
        ];
    }
}

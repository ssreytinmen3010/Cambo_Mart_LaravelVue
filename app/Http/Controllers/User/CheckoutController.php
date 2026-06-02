<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AdminPanel\Order;
use App\Models\AdminPanel\OrderItem;
use App\Models\AdminPanel\Product;
use App\Models\Cart;
use App\Models\Delivery;
use App\Models\HistoryOrder;
use App\Models\PromotionSeason;
use App\Models\Setting;
use App\Services\GeocodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $cart = Cart::query()
            ->where('user_id', Auth::id())
            ->with(['items.product'])
            ->first();

        $delivery = Delivery::query()->latest()->first();

        $savedAddress = Address::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->first(['address', 'floor', 'qty_kilo']);

        return Inertia::render('User/Checkout/Index', [
            'cart' => $cart?->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount_amount' => $item->discount_amount,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image' => $item->product->image,
                        'product_code' => $item->product->product_code,
                    ],
                ];
            }) ?? [],
            'delivery' => [
                'fee_amount_per' => (float) ($delivery?->fee_amount_per ?? 0),
                'qty_kg' => (float) ($delivery?->qty_kg ?? 1),
                'discount_type' => $delivery?->discount_type ?? null,
                'discount_value' => (float) ($delivery?->discount_value ?? 0),
            ],
            'savedAddress' => $savedAddress ? [
                'address' => $savedAddress->address,
                'floor' => $savedAddress->floor,
                'qty_kilo' => (float) $savedAddress->qty_kilo,
            ] : null,
            'store' => [
                'name' => Setting::get('store_name', config('app.name', 'CamboMart')),
                'address' => Setting::get('address'),
                'map_lat' => (float) Setting::get('map_lat', 11.5564),
                'map_long' => (float) Setting::get('map_long', 104.9282),
            ],
        ]);
    }

    public function geocode(Request $request, GeocodingService $geocoding)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $result = $geocoding->geocode($validated['q']);

        if ($result === null) {
            return response()->json([
                'message' => 'Address not found. Include street/area in Phnom Penh, or tap “Use my location”.',
            ], 404);
        }

        return response()->json($result);
    }

    public function reverseGeocode(Request $request, GeocodingService $geocoding)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $displayName = $geocoding->reverseGeocode(
            (float) $validated['lat'],
            (float) $validated['lng'],
        );

        if ($displayName === null) {
            return response()->json(['message' => 'Reverse geocoding unavailable.'], 503);
        }

        return response()->json([
            'display_name' => $displayName,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:5000'],
            'floor' => ['nullable', 'string', 'max:255'],
            'qty_kilo' => ['nullable', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['required', 'in:'.Order::PAYMENT_METHOD_CASH.','.Order::PAYMENT_METHOD_ONLINE],
        ]);

        return DB::transaction(function () use ($validated) {
            $cart = Cart::query()
                ->where('user_id', Auth::id())
                ->with(['items.product'])
                ->first();
            $delivery = Delivery::query()->latest()->first();
            $deliveryQtyKilo = (float) ($validated['qty_kilo'] ?? 1);
            $deliverySubtotal = (float) ($delivery?->fee_amount_per ?? 0) * $deliveryQtyKilo;
            $deliveryFee = match ($delivery?->discount_type) {
                Delivery::DISCOUNT_FREE => 0,
                Delivery::DISCOUNT_PERCENTAGE => max(
                    0,
                    $deliverySubtotal - ($deliverySubtotal * ((float) ($delivery?->discount_value ?? 0) / 100))
                ),
                default => max(0, $deliverySubtotal - (float) ($delivery?->discount_value ?? 0)),
            };

            if (! $cart || $cart->items->isEmpty()) {
                return back()->withErrors(['cart' => 'Your cart is empty.']);
            }

            $coupon = null;
            $couponDiscount = 0.0;
            $couponCode = trim((string) ($validated['coupon_code'] ?? ''));
            $cartSubtotal = (float) $cart->subtotal;
            $cartTotalAfterItemDiscount = (float) $cart->total_amount;

            if ($couponCode !== '') {
                $coupon = PromotionSeason::query()
                    ->whereRaw('LOWER(code) = ?', [strtolower($couponCode)])
                    ->where('status', PromotionSeason::STATUS_ACTIVE)
                    ->where(function ($query) {
                        $query->whereNull('start_date')->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('end_date')->orWhere('end_date', '>=', now());
                    })
                    ->first();

                if (! $coupon) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'Invalid or expired coupon code.',
                    ]);
                }

                if ($coupon->promotion_type === PromotionSeason::PROMOTION_FIX) {
                    $couponDiscount = min($cartTotalAfterItemDiscount, (float) ($coupon->promotion_value ?? 0));
                } elseif ($coupon->promotion_type === PromotionSeason::PROMOTION_PERCENTAGE) {
                    $couponDiscount = ($cartTotalAfterItemDiscount * (float) ($coupon->promotion_value ?? 0)) / 100;
                } elseif ($coupon->promotion_type === PromotionSeason::PROMOTION_FREE_DELIVERY) {
                    $deliveryFee = 0;
                }
            }

            $cartDiscount = (float) $cart->discount;
            $orderDiscountAmount = $cartDiscount + $couponDiscount;
            $orderTotalAmount = max(0, $cartSubtotal - $orderDiscountAmount) + $deliveryFee;

            $address = Address::query()->create([
                'user_id' => Auth::id(),
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'floor' => $validated['floor'] ?? null,
                'qty_kilo' => $deliveryQtyKilo,
            ]);

            $order = Order::query()->create([
                'order_number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'user_id' => Auth::id(),
                'address_id' => $address->id,
                'promotion_id' => null,
                'subtotal_amount' => $cartSubtotal,
                'discount_amount' => $orderDiscountAmount,
                'total_amount' => $orderTotalAmount,
                'delivery_fee' => $deliveryFee,
                'discount_type' => $delivery?->discount_type,
                'discount_value' => (float) ($delivery?->discount_value ?? 0),
                'order_status' => Order::ORDER_PENDING,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => $validated['payment_method'],
            ]);

            if ($coupon) {
                $coupon->update(['order_id' => $order->id]);
            }

            foreach ($cart->items as $item) {
                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
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

            HistoryOrder::query()->create([
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'address_id' => $address->id,
            ]);

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
                    'delivery_fee' => $order->delivery_fee,
                    'discount_type' => $order->discount_type,
                    'discount_value' => $order->discount_value,
                    'coupon_code' => $coupon?->code,
                    'coupon_type' => $coupon?->promotion_type,
                    'coupon_value' => $coupon?->promotion_value,
                    'coupon_discount' => $couponDiscount,
                ]);
        });
    }

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
            'qty_kilo' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cart = Cart::query()
            ->where('user_id', Auth::id())
            ->with(['items.product'])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty.',
            ], 422);
        }

        $delivery = Delivery::query()->latest()->first();
        $deliveryQtyKilo = (float) ($validated['qty_kilo'] ?? 1);
        $deliveryFee = $this->calculateDeliveryFee($delivery, $deliveryQtyKilo);
        $coupon = $this->findActiveCoupon($validated['coupon_code']);

        if (! $coupon) {
            return response()->json([
                'message' => 'Invalid or expired coupon code.',
            ], 422);
        }

        [$couponDiscount, $finalDeliveryFee] = $this->calculateCouponEffect(
            $coupon,
            (float) $cart->total_amount,
            $deliveryFee
        );

        $totalAmount = max(0, (float) $cart->subtotal - (float) $cart->discount - $couponDiscount) + $finalDeliveryFee;

        return response()->json([
            'coupon_code' => $coupon->code,
            'coupon_type' => $coupon->promotion_type,
            'coupon_value' => (float) ($coupon->promotion_value ?? 0),
            'coupon_discount' => round($couponDiscount, 2),
            'delivery_fee' => round($finalDeliveryFee, 2),
            'total_amount' => round($totalAmount, 2),
        ]);
    }

    private function findActiveCoupon(string $couponCode): ?PromotionSeason
    {
        $couponCode = trim($couponCode);

        if ($couponCode === '') {
            return null;
        }

        return PromotionSeason::query()
            ->whereRaw('LOWER(code) = ?', [strtolower($couponCode)])
            ->where('status', PromotionSeason::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->first();
    }

    private function calculateCouponEffect(PromotionSeason $coupon, float $baseAmount, float $deliveryFee): array
    {
        $couponDiscount = 0.0;
        $finalDeliveryFee = $deliveryFee;

        if ($coupon->promotion_type === PromotionSeason::PROMOTION_FIX) {
            $couponDiscount = min($baseAmount, (float) ($coupon->promotion_value ?? 0));
        } elseif ($coupon->promotion_type === PromotionSeason::PROMOTION_PERCENTAGE) {
            $couponDiscount = ($baseAmount * (float) ($coupon->promotion_value ?? 0)) / 100;
        } elseif ($coupon->promotion_type === PromotionSeason::PROMOTION_FREE_DELIVERY) {
            $finalDeliveryFee = 0;
        }

        return [$couponDiscount, $finalDeliveryFee];
    }

    private function calculateDeliveryFee(?Delivery $delivery, float $deliveryQtyKilo): float
    {
        $deliverySubtotal = (float) ($delivery?->fee_amount_per ?? 0) * $deliveryQtyKilo;

        return match ($delivery?->discount_type) {
            Delivery::DISCOUNT_FREE => 0,
            Delivery::DISCOUNT_PERCENTAGE => max(
                0,
                $deliverySubtotal - ($deliverySubtotal * ((float) ($delivery?->discount_value ?? 0) / 100))
            ),
            default => max(0, $deliverySubtotal - (float) ($delivery?->discount_value ?? 0)),
        };
    }
}

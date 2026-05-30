<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AdminPanel\Order;
use App\Models\AdminPanel\OrderItem;
use App\Models\AdminPanel\Product;
use App\Models\Cart;
use App\Models\HistoryOrder;
use App\Models\Setting;
use App\Services\Bakong\BakongKhqrService;
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

    public function store(Request $request, BakongKhqrService $bakong)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:5000'],
            'floor' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:' . Order::PAYMENT_METHOD_CASH . ',' . Order::PAYMENT_METHOD_ONLINE],
        ]);

        $bakongAccountId = Setting::get('bakong_account_id', config('services.bakong.account_id'));

        if ($validated['payment_method'] === Order::PAYMENT_METHOD_ONLINE && ! $bakongAccountId) {
            return back()
                ->withErrors(['payment_method' => 'Bakong account is not configured. Please contact the admin team.'])
                ->withInput();
        }

        return DB::transaction(function () use ($validated, $bakong) {
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

            $isOnline = $validated['payment_method'] === Order::PAYMENT_METHOD_ONLINE;

            $order = Order::query()->create([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8)),
                'user_id' => Auth::id(),
                'address_id' => $address->id,
                'promotion_id' => null,
                'subtotal_amount' => $cart->subtotal,
                'discount_amount' => $cart->discount,
                'total_amount' => $cart->total_amount,
                'order_status' => Order::ORDER_PENDING,
                'payment_status' => $isOnline ? Order::PAYMENT_PENDING : Order::PAYMENT_PAID,
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

            HistoryOrder::query()->create([
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'address_id' => $address->id,
            ]);

            $bakongQr = null;

            if ($isOnline) {
                $bakongQr = $bakong->generateMerchantQr([
                    'account_id' => Setting::get('bakong_account_id', config('services.bakong.account_id')),
                    'merchant_name' => config('services.bakong.merchant_name', 'DAVIT YEM'),
                    'merchant_city' => config('services.bakong.merchant_city', 'Phnom Penh'),
                    'acquiring_bank' => config('services.bakong.acquiring_bank', 'Dev Bank'),
                    'merchant_category_code' => config('services.bakong.merchant_category_code', '5999'),
                    'currency' => config('services.bakong.currency', 'USD'),
                    'amount' => $order->total_amount,
                    'bill_number' => $order->order_number,
                    'expires_in_minutes' => config('services.bakong.qr_timeout_minutes', 10),
                    'purpose' => "Order {$order->order_number}",
                ]);

                $order->forceFill([
                    'bakong_qr' => $bakongQr['qr'],
                    'bakong_md5' => $bakongQr['md5'],
                    'bakong_expires_at' => $bakongQr['expires_at'],
                ])->save();
            }

            $cart->items()->delete();
            $cart->subtotal = 0;
            $cart->discount = 0;
            $cart->total_amount = 0;
            $cart->save();

            $redirect = $isOnline ? route('checkout') : route('user.profile');
            $response = redirect()->to($redirect)->with('success', 'Order placed successfully.');

            if ($isOnline) {
                $response->with('checkout', [
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'bakong' => $bakongQr ? [
                        'qr' => $bakongQr['qr'],
                        'md5' => $bakongQr['md5'],
                        'expires_at' => $bakongQr['expires_at']->toIso8601String(),
                        'merchant_name' => $bakongQr['merchant_name'],
                        'merchant_city' => $bakongQr['merchant_city'],
                    ] : null,
                ]);
            }

            return $response;
        });
    }

    public function index()
    {
        $cart = Cart::query()
            ->where('user_id', Auth::id())
            ->with(['items.product'])
            ->first();

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
        ]);
    }

    public function qrImage($orderNumber)
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->bakong_qr) {
            abort(404, 'QR code not found for this order');
        }

        return response($order->bakong_qr)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}

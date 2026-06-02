<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\AdminPanel\BrandController;
use App\Http\Controllers\AdminPanel\CategoryController;
use App\Http\Controllers\AdminPanel\ProductController;
use App\Http\Controllers\AdminPanel\PromotionController;
use App\Http\Controllers\AdminPanel\PromotionSeasonController;
use App\Http\Controllers\AdminPanel\DeliveryController;
use App\Http\Controllers\AdminPanel\OrderController;
use App\Http\Controllers\AdminPanel\DashboardController;
use App\Http\Controllers\AdminPanel\ReviewController;
use App\Http\Controllers\AdminPanel\LocationController;
use App\Http\Controllers\AdminPanel\ContactController;
use App\Models\Delivery;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\WishlistController;
use App\Http\Controllers\User\ReviewController as UserReviewController;
use App\Models\User;
use App\Models\AdminPanel\Product;
use App\Models\AdminPanel\Category;
use App\Models\AdminPanel\Brand;
use App\Models\AdminPanel\Order;
use App\Models\AdminPanel\Review;
use App\Models\AdminPanel\Promotion;
use App\Models\HistoryOrder;
use App\Http\Controllers\AdminPanel\SettingController;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->check() && auth()->user()->role->name === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    $categories = Category::query()
        ->where('status', 1)
        ->select(['id', 'name', 'image', 'qty_item'])
        ->withCount('products')
        ->orderBy('name')
        ->limit(8)
        ->get()
        ->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image,
                'qty_item' => $category->products_count,
            ];
        });

    $products = Product::query()
        ->where('status', 1)
        ->select(['id', 'name', 'image', 'product_code', 'price', 'stock', 'status_stock'])
        ->with(['promotion' => function ($q) {
            $q->active()->select(['id', 'product_id', 'promo_type', 'discount_value', 'buy_qty', 'get_qty', 'status']);
        }])
        ->addSelect(DB::raw('cal_avg_rating as rating'))
        ->selectSub(
            Review::query()
                ->selectRaw('COUNT(*)')
                ->whereColumn('product_id', 'products.id')
                ->where('review_status', Review::STATUS_APPROVED),
            'reviews_count'
        )
        ->orderBy('name')
        ->limit(4)
        ->get();

    $brands = Brand::query()
        ->where('status', 1)
        ->select(['id', 'name', 'image'])
        ->orderBy('name')
        ->limit(8)
        ->get();

    return Inertia::render('User/HomePage/Index', [
        'categories' => $categories,
        'products' => $products,
        'brands' => $brands,
    ]);
})->name('home');

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    if (auth()->user()->role->name == 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/upload-image', [ImageController::class, 'upload'])->name('images.upload');
});

Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('admin.dashboard');

Route::get('/user/dashboard', function () {
    return Inertia::render('User/Dashboard');
})->middleware(['auth', 'verified', 'role:user'])->name('user.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/user/cart', [CartController::class, 'show'])->name('user.cart.show');
    Route::post('/user/cart/items', [CartController::class, 'addItem'])->name('user.cart.items.add');
    Route::put('/user/cart/items/{cartItem}', [CartController::class, 'updateItem'])->name('user.cart.items.update');
    Route::delete('/user/cart/items/{cartItem}', [CartController::class, 'removeItem'])->name('user.cart.items.remove');
    Route::delete('/user/cart/clear', [CartController::class, 'clear'])->name('user.cart.clear');

    Route::get('/user/wishlist', [WishlistController::class, 'index'])->name('user.wishlist.index');
    Route::post('/user/wishlist/toggle', [WishlistController::class, 'toggle'])->name('user.wishlist.toggle');

    Route::post('/user/reviews', [UserReviewController::class, 'store'])->name('user.reviews.store');
    Route::get('/user/reviews/my', [UserReviewController::class, 'myRatings'])->name('user.reviews.my');

    Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.apply-coupon');
    Route::get('/checkout/geocode', [CheckoutController::class, 'geocode'])->name('checkout.geocode');
    Route::get('/checkout/reverse-geocode', [CheckoutController::class, 'reverseGeocode'])->name('checkout.reverse-geocode');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

Route::prefix('')->group(function () {
    Route::get('/shop', function () {
        $categories = Category::query()
            ->where('status', 1)
            ->select(['id', 'name', 'image', 'qty_item'])
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image,
                    'qty_item' => $category->products_count,
                ];
            });

        $brands = Brand::query()
            ->where('status', 1)
            ->select(['id', 'name', 'image'])
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('status', 1)
            ->select(['id', 'name', 'image', 'product_code', 'category_id', 'brand_id', 'price', 'stock', 'status_stock', 'created_at'])
            ->with(['promotion' => function ($q) {
                $q->active()->select(['id', 'product_id', 'promo_type', 'discount_value', 'buy_qty', 'get_qty', 'status']);
            }])
            ->addSelect(DB::raw('cal_avg_rating as rating'))
            ->selectSub(
                Review::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('review_status', Review::STATUS_APPROVED),
                'reviews_count'
            )
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('User/Shop/Index', [
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
        ]);
    })->name('shop');
    Route::get('/cart', function () {
        $delivery = Delivery::query()->latest()->first();

        return Inertia::render('User/Cart/Index', [
            'delivery' => [
                'fee_amount_per' => (float) ($delivery?->fee_amount_per ?? 0),
                'qty_kg' => (float) ($delivery?->qty_kg ?? 1),
            ],
        ]);
    })->name('cart');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::get('/wishlist', fn () => Inertia::render('User/Wishlist/Index'))->name('wishlist');
    Route::get('/account', function () {
        $historyOrders = HistoryOrder::query()
            ->where('user_id', auth()->id())
            ->with([
                'order' => function ($query) {
                    $query->select([
                        'id',
                        'order_number',
                        'address_id',
                        'payment_method',
                        'subtotal_amount',
                        'discount_amount',
                        'delivery_fee',
                        'discount_type',
                        'discount_value',
                        'total_amount',
                        'order_status',
                        'payment_status',
                        'created_at',
                    ])
                        ->with([
                            'address:id,name,phone,address,floor,qty_kilo',
                            'promotionSeasons:id,order_id,code,promotion_type,promotion_value',
                            'items' => function ($items) {
                                $items->select(['id', 'order_id', 'product_id', 'qty', 'unit_price'])
                                    ->with(['product:id,name,image']);
                            },
                        ])
                        ->withCount('items');
                },
            ])
            ->latest()
            ->get()
            ->map(function ($historyOrder) {
                $order = $historyOrder->order;

                return [
                    'id' => $order?->order_number ?? ('ORDER-' . $historyOrder->order_id),
                    'date' => optional($order?->created_at)->format('M d, Y') ?? now()->format('M d, Y'),
                    'status' => $order?->order_status ?? Order::ORDER_PENDING,
                    'total' => (float) ($order?->total_amount ?? 0),
                    'items' => (int) ($order?->items_count ?? 0),
                    'payment_method' => $order?->payment_method ?? null,
                    'subtotal' => (float) ($order?->subtotal_amount ?? 0),
                    'discount' => (float) ($order?->discount_amount ?? 0),
                    'delivery_fee' => (float) ($order?->delivery_fee ?? 0),
                    'discount_type' => $order?->discount_type ?? null,
                    'discount_value' => (float) ($order?->discount_value ?? 0),
                    'promotion_code' => $order?->promotionSeasons?->first()?->code ?? null,
                    'promotion_type' => $order?->promotionSeasons?->first()?->promotion_type ?? null,
                    'promotion_value' => (float) ($order?->promotionSeasons?->first()?->promotion_value ?? 0),
                    'address' => $order?->address ? [
                        'name' => $order->address->name,
                        'phone' => $order->address->phone,
                        'address' => $order->address->address,
                        'floor' => $order->address->floor,
                    ] : null,
                    'items_list' => ($order?->items ?? collect())->map(function ($item) {
                        return [
                            'name' => $item->product?->name ?? 'Product',
                            'qty' => (int) $item->qty,
                            'unit_price' => (float) $item->unit_price,
                            'line_total' => (float) ($item->qty * $item->unit_price),
                        ];
                    })->values(),
                ];
            });

        $recentActivities = $historyOrders
            ->take(5)
            ->map(function ($order) {
                $status = strtoupper((string) ($order['status'] ?? Order::ORDER_PENDING));
                $amount = number_format((float) ($order['total'] ?? 0), 2);

                if ($status === Order::ORDER_COMPLETED) {
                    return "Completed order {$order['id']} · \${$amount}";
                }

                if ($status === Order::ORDER_CANCELLED) {
                    return "Cancelled order {$order['id']} · \${$amount}";
                }

                return "Placed order {$order['id']} · \${$amount}";
            })
            ->values();

        return Inertia::render('User/Profile/Index', [
            'historyOrders' => $historyOrders,
            'recentActivities' => $recentActivities,
        ]);
    })->name('user.profile');
    Route::get('/categories', function () {
        $categories = Category::query()
            ->where('status', 1)
            ->select(['id', 'name', 'image', 'qty_item'])
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image,
                    'qty_item' => $category->products_count,
                ];
            });

        $brands = Brand::query()
            ->where('status', 1)
            ->select(['id', 'name', 'image'])
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('status', 1)
            ->select(['id', 'name', 'image', 'product_code', 'category_id', 'brand_id', 'price', 'stock', 'status_stock', 'created_at'])
            ->with(['promotion' => function ($q) {
                $q->active()->select(['id', 'product_id', 'promo_type', 'discount_value', 'buy_qty', 'get_qty', 'status']);
            }])
            ->addSelect(DB::raw('cal_avg_rating as rating'))
            ->selectSub(
                Review::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('review_status', Review::STATUS_APPROVED),
                'reviews_count'
            )
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return Inertia::render('User/Categories/Index', [
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
        ]);
    })->name('categories');
    Route::get('/brand', function () {
        $brands = Brand::query()
            ->where('status', 1)
            ->select(['id', 'name', 'image'])
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('status', 1)
            ->select(['id', 'name', 'image', 'product_code', 'category_id', 'brand_id', 'price', 'stock', 'status_stock', 'created_at'])
            ->with(['promotion' => function ($q) {
                $q->active()->select(['id', 'product_id', 'promo_type', 'discount_value', 'buy_qty', 'get_qty', 'status']);
            }])
            ->addSelect(DB::raw('cal_avg_rating as rating'))
            ->selectSub(
                Review::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('review_status', Review::STATUS_APPROVED),
                'reviews_count'
            )
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return Inertia::render('User/Brand/Index', [
            'brands' => $brands,
            'products' => $products,
        ]);
    })->name('brand');
    Route::get('/about', fn () => Inertia::render('User/About/Index'))->name('about');
    Route::get('/contact', fn () => Inertia::render('User/Contact/Index'))->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
    Route::redirect('/root', '/')->name('root');
});

Route::get('/admin/users/list', [UserController::class, 'list'])
    ->middleware(['auth', 'verified', 'role:admin']);

Route::post('/admin/users/list/add', [UserController::class, 'store'])
    ->middleware(['auth', 'verified', 'role:admin'])->name('admin.users.store');

Route::patch('/admin/users/list/update/{user}', [UserController::class, 'update'])
    ->middleware(['auth', 'verified', 'role:admin']);

Route::patch('/admin/users/{user}/password', [UserController::class, 'changePassword'])
    ->middleware(['auth', 'verified', 'role:admin']);

Route::patch('/admin/users/{user}/status', [UserController::class, 'updateStatus'])
    ->middleware(['auth', 'verified', 'role:admin']);

Route::get('/admin/users', [UserController::class, 'list'])
    ->middleware(['auth', 'verified', 'role:admin']);



 Route::post('upload-image', [ImageController::class, 'upload'])
        ->name('image.upload');

Route::prefix('admin')->middleware(['auth','role:admin'])->group(function () {
    Route::get('/users/list', [UserController::class, 'list']);
    Route::post('/users/list/add', [UserController::class, 'store']);
    Route::get('/users/list/update/{userId}', [UserController::class, 'edit']);
    Route::patch('/users/list/update/{userId}', [UserController::class, 'update']);
    Route::delete('/users/list/{userId}', [UserController::class, 'destroy']);
    Route::patch('/users/list/{userId}/status', [UserController::class, 'updateStatus']);

    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

    Route::get('/brands', [BrandController::class, 'index'])->name('admin.brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('admin.brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('admin.brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('admin.brands.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    Route::get('/promotions', [PromotionController::class, 'index'])->name('admin.promotions.index');
    Route::post('/promotions', [PromotionController::class, 'store'])->name('admin.promotions.store');
    Route::put('/promotions/{promotion}', [PromotionController::class, 'update'])->name('admin.promotions.update');
    Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy'])->name('admin.promotions.destroy');

    Route::get('/promotion-seasons', [PromotionSeasonController::class, 'index'])->name('admin.promotion-seasons.index');
    Route::post('/promotion-seasons', [PromotionSeasonController::class, 'store'])->name('admin.promotion-seasons.store');
    Route::put('/promotion-seasons/{promotionSeason}', [PromotionSeasonController::class, 'update'])->name('admin.promotion-seasons.update');
    Route::delete('/promotion-seasons/{promotionSeason}', [PromotionSeasonController::class, 'destroy'])->name('admin.promotion-seasons.destroy');

    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('admin.deliveries.index');
    Route::post('/deliveries', [DeliveryController::class, 'store'])->name('admin.deliveries.store');
    Route::put('/deliveries/{delivery}', [DeliveryController::class, 'update'])->name('admin.deliveries.update');
    Route::delete('/deliveries/{delivery}', [DeliveryController::class, 'destroy'])->name('admin.deliveries.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');
    Route::post('/orders/bulk-update', [OrderController::class, 'bulkUpdate'])->name('admin.orders.bulk-update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');

    Route::get('/reviews', [ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('admin.reviews.update');
    Route::post('/reviews/bulk-update', [ReviewController::class, 'bulkUpdate'])->name('admin.reviews.bulk-update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');

    Route::get('/contacts', [ContactController::class, 'index'])->name('admin.contacts.index');

    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::put('/settings/profile', [SettingController::class, 'updateProfile'])->name('admin.settings.update-profile');
    Route::put('/settings/password', [SettingController::class, 'updatePassword'])->name('admin.settings.update-password');
    Route::put('/settings/system', [SettingController::class, 'updateSystem'])->name('admin.settings.update-system');

    Route::resource('locations', LocationController::class)->names('admin.locations');
});



require __DIR__.'/auth.php';

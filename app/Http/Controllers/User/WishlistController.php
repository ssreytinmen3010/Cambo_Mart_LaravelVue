<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Product;
use App\Models\AdminPanel\Wishlist;
use App\Models\AdminPanel\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = Wishlist::query()
            ->where('user_id', Auth::id())
            ->with(['product'])
            ->latest()
            ->get();

        // If you ever see empty results here while user has rows, check that
        // wishlists.user_id is being saved (and you're logged in).

        $productIds = $items->pluck('product_id')->filter()->values();
        $reviewAggregates = DB::table('reviews')
            ->selectRaw('product_id, COALESCE(AVG(rating_score), 0) as rating, COUNT(*) as reviews_count')
            ->whereIn('product_id', $productIds)
            ->where('review_status', Review::STATUS_APPROVED)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $products = $items
            ->filter(fn ($w) => $w->product)
            ->map(function ($w) use ($reviewAggregates) {
                $p = $w->product;
                $agg = $reviewAggregates->get($p->id);

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'image' => $p->image,
                    'product_code' => $p->product_code,
                    'price' => (float) $p->price,
                    'stock' => (int) $p->stock,
                    'status_stock' => $p->status_stock,
                    'rating' => (float) ($agg->rating ?? 0),
                    'reviews_count' => (int) ($agg->reviews_count ?? 0),
                    'created_at' => optional($p->created_at)->toISOString(),
                ];
            })
            ->values();

        return response()->json([
            'user_id' => Auth::id(),
            'count' => $products->count(),
            'product_ids' => $products->pluck('id')->values(),
            'products' => $products,
        ]);
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            $productId = (int) $validated['product_id'];

            Product::query()->findOrFail($productId);

            Log::info('wishlist.toggle', [
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'auth_check' => Auth::check(),
            ]);

            $existing = Wishlist::query()
                ->where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->first();

            if ($existing) {
                $existing->delete();
            } else {
                Wishlist::query()->create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'added_date' => now(),
                ]);
            }

            return $this->index();
        });
    }
}

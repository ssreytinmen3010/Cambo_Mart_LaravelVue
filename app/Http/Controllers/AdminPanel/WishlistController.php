<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Wishlist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $query = Wishlist::with(['product', 'user']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->whereHas('product', function($pq) use ($request) {
                      $pq->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('product_code', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('user', function($uq) use ($request) {
                      $uq->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('added_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('added_date', '<=', $request->to_date);
        }

        // Manual transformation
        $wishlistPaginator = $query->latest()->paginate(10);
        $wishlistData = collect($wishlistPaginator->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'added_date' => $item->added_date ? $item->added_date->format('Y-m-d H:i') : null,
                
                'product_id' => $item->product_id,
                'product_name' => $item->product ? $item->product->name : 'Unknown Product',
                'product_image' => $item->product ? $item->product->image : null,
                'product_price' => $item->product ? $item->product->price : 0,
                
                'user_id' => $item->user_id,
                'user_name' => $item->user ? $item->user->name : 'Guest',
                'user_email' => $item->user ? $item->user->email : 'N/A',
                
                'created_at' => $item->created_at->format('Y-m-d H:i'),
            ];
        })->all();

        $wishlists = [
            'data' => $wishlistData,
            'current_page' => $wishlistPaginator->currentPage(),
            'last_page' => $wishlistPaginator->lastPage(),
            'per_page' => $wishlistPaginator->perPage(),
            'total' => $wishlistPaginator->total(),
            'links' => $wishlistPaginator->toArray()['links'],
        ];

        return Inertia::render('Admin/Wishlists/Index', [
            'wishlists' => $wishlists,
            'filters' => $request->only(['search', 'from_date', 'to_date']),
        ]);
    }

    public function destroy(Wishlist $wishlist)
    {
        $wishlist->delete();
        return redirect()->route('admin.wishlists.index')->with('success', 'Item removed from wishlist.');
    }
}   
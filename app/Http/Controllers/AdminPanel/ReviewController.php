<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Review;
use App\Models\AdminPanel\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;


class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('review_number', 'like', '%' . $request->search . '%')
                  ->orWhere('review_text', 'like', '%' . $request->search . '%')
                  ->orWhereHas('product', function($pq) use ($request) {
                      $pq->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('user', function($uq) use ($request) {
                      $uq->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('create_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('create_date', '<=', $request->to_date);
        }

        if ($request->status) {
            $query->where('review_status', $request->status);
        }

        // Manual transformation
        $reviewsPaginator = $query->latest()->paginate(10);
        $reviewsData = collect($reviewsPaginator->items())->map(function ($review) {
            return [
                'id' => $review->id,
                'review_number' => $review->review_number,
                'rating' => $review->rating_score,
                'text' => $review->review_text,
                'status' => $review->review_status,
                'status_color' => $review->status_color, // Model accessor
                
                'product_id' => $review->product_id,
                'product_name' => $review->product ? $review->product->name : 'Unknown Product',
                'product_image' => $review->product ? $review->product->image : null,
                
                'user_id' => $review->user_id,
                'user_name' => $review->user ? $review->user->name : 'Guest',
                'user_image' => $review->user ? $review->user->image : null,
                
                'create_date' => $review->create_date ? $review->create_date->format('Y-m-d H:i') : null,
                'created_at' => $review->created_at->format('Y-m-d H:i'),
            ];
        })->all();

        $reviews = [
            'data' => $reviewsData,
            'current_page' => $reviewsPaginator->currentPage(),
            'last_page' => $reviewsPaginator->lastPage(),
            'per_page' => $reviewsPaginator->perPage(),
            'total' => $reviewsPaginator->total(),
            'links' => $reviewsPaginator->toArray()['links'],
        ];

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['search', 'from_date', 'to_date', 'status']),
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'review_status' => 'required|in:PENDING,APPROVED,REJECTED',
        ]);

        $review->update([
            'review_status' => $request->review_status
        ]);

        return redirect()->route('admin.reviews.index')->with('success', 'Review status updated.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id',
            'review_status' => 'required|in:PENDING,APPROVED,REJECTED',
        ]);

        Review::whereIn('id', $request->ids)->update([
            'review_status' => $request->review_status
        ]);

        return redirect()->route('admin.reviews.index')->with('success', 'Reviews updated successfully.');
    }
}
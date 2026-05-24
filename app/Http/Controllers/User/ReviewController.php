<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating_score' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = Review::query()
            ->where('product_id', $validated['product_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$review) {
            $review = new Review();
            $review->review_number = $this->generateReviewNumber();
            $review->product_id = (int) $validated['product_id'];
            $review->user_id = Auth::id();
        }

        $review->rating_score = (int) $validated['rating_score'];
        $review->review_text = $validated['review_text'] ?? null;
        $review->review_status = Review::STATUS_PENDING;
        $review->create_date = now();
        $review->save();

        return response()->json([
            'message' => 'Rating saved (pending approval).',
            'review' => $review,
        ]);
    }

    public function myRatings(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer'],
        ]);

        $ratings = Review::query()
            ->where('user_id', Auth::id())
            ->whereIn('product_id', $validated['product_ids'])
            ->latest('updated_at')
            ->get(['product_id', 'rating_score'])
            ->unique('product_id')
            ->mapWithKeys(fn ($r) => [(int) $r->product_id => (int) $r->rating_score]);

        return response()->json([
            'ratings' => $ratings,
        ]);
    }

    private function generateReviewNumber(): string
    {
        do {
            $number = 'REV-' . strtoupper(Str::random(10));
        } while (Review::query()->where('review_number', $number)->exists());

        return $number;
    }
}

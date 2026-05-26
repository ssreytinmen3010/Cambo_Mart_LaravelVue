<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Review extends Model
{
    use HasFactory;

    // --- Status Constants ---
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'review_number',
        'product_id',
        'user_id',
        'rating_score',
        'review_text',
        'review_status', // "PENDING", "APPROVED", "REJECTED"
        'create_date',
    ];

    protected $casts = [
        'create_date' => 'datetime',
        'rating_score' => 'integer',
    ];

    // --- Accessors ---

    public function getStatusColorAttribute()
    {
        return match ($this->review_status) {
            self::STATUS_APPROVED => 'green',
            self::STATUS_PENDING => 'orange',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    // --- Relationships ---

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saved(function (Review $review) {
            static::recalculateProductAvgRating($review->product_id);
        });

        static::deleted(function (Review $review) {
            static::recalculateProductAvgRating($review->product_id);
        });
    }

    public static function recalculateProductAvgRating(?int $productId): void
    {
        if (!$productId) return;

        $avg = DB::table('reviews')
            ->where('product_id', $productId)
            ->where('review_status', self::STATUS_APPROVED)
            ->avg('rating_score');

        DB::table('products')
            ->where('id', $productId)
            ->update(['cal_avg_rating' => round((float)($avg ?? 0), 2)]);
    }
}

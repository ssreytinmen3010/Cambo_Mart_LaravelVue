<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
}
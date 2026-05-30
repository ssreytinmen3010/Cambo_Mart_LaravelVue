<?php

namespace App\Models;

use App\Models\AdminPanel\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionSeason extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_PAUSE = 'PAUSE';
    const STATUS_EXPIRE = 'EXPIRE';

    const PROMOTION_FIX = 'FIX';
    const PROMOTION_PERCENTAGE = 'PERCENTAGE';
    const PROMOTION_FREE_DELIVERY = 'FREE_DELIVERY';

    protected $fillable = [
        'image',
        'code',
        'start_date',
        'end_date',
        'promotion_type',
        'promotion_value',
        'cart_id',
        'order_id',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'promotion_value' => 'decimal:2',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_DRAFT,
            self::STATUS_PAUSE,
            self::STATUS_EXPIRE,
        ];
    }

    public static function promotionTypes(): array
    {
        return [
            self::PROMOTION_FIX,
            self::PROMOTION_PERCENTAGE,
            self::PROMOTION_FREE_DELIVERY,
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeRunning($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', now());
        })->where(function ($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', now());
        });
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

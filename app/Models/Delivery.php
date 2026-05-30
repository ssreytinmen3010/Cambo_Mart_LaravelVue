<?php

namespace App\Models;

use App\Models\AdminPanel\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    const DISCOUNT_FIX = 'fix';
    const DISCOUNT_PERCENTAGE = 'percentage';
    const DISCOUNT_FREE = 'free';

    protected $fillable = [
        'fee_amount_per',
        'qty_kg',
        'total',
        'discount_type',
        'discount_value',
        'cart_id',
        'order_id',
    ];

    protected $casts = [
        'fee_amount_per' => 'decimal:2',
        'qty_kg' => 'decimal:3',
        'total' => 'decimal:2',
        'discount_value' => 'decimal:2',
    ];

    public static function discountTypes(): array
    {
        return [
            self::DISCOUNT_FIX,
            self::DISCOUNT_PERCENTAGE,
            self::DISCOUNT_FREE,
        ];
    }

    public function calculateTotal(): float
    {
        $subtotal = (float) $this->fee_amount_per * (float) $this->qty_kg;

        $total = match ($this->discount_type) {
            self::DISCOUNT_FREE => 0,
            self::DISCOUNT_PERCENTAGE => max(
                0,
                $subtotal - ($subtotal * ((float) ($this->discount_value ?? 0) / 100))
            ),
            default => max(0, $subtotal - (float) ($this->discount_value ?? 0)),
        };

        $this->total = round($total, 2);

        return (float) $this->total;
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

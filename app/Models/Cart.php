<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';

    protected $fillable = [
        'user_id',
        'subtotal',
        'discount',
        'total_amount',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    public function recalculateTotals(): self
    {
        // Subtotal is before discount, total_amount is after discount.
        // We store discounted unit price in cart_items.price and per-unit discount in cart_items.discount_amount.
        $subtotalBeforeDiscount = (float) $this->items()->sum(DB::raw('quantity * (price + discount_amount)'));
        $discountAmount = (float) $this->items()->sum(DB::raw('quantity * discount_amount'));
        $totalAfterDiscount = (float) $this->items()->sum(DB::raw('quantity * price'));

        $this->subtotal = $subtotalBeforeDiscount;
        $this->discount = $discountAmount;
        $this->total_amount = max(0, $totalAfterDiscount);

        return $this;
    }
}

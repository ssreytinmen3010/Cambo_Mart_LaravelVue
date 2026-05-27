<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; 
use App\Models\Address;

class Order extends Model
{
    use HasFactory;

    // --- Order Status Constants ---
    const ORDER_PENDING = 'PENDING';
    const ORDER_COMPLETED = 'COMPLETED';
    const ORDER_CANCELLED = 'CANCELLED';
    const ORDER_REFUNDED = 'REFUNDED';

    // --- Payment Status Constants ---
    const PAYMENT_PENDING = 'PENDING';
    const PAYMENT_PAID = 'PAID';
    const PAYMENT_FAILED = 'FAILED';
    const PAYMENT_REFUNDED = 'REFUNDED';

    // --- Payment Method Constants ---
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_ONLINE = 'online';

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'promotion_id',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'order_status',   // "PENDING", "COMPLETED", "CANCELLED", "REFUNDED"
        'payment_status', // "PENDING", "PAID", "FAILED", "REFUNDED"
        'payment_method', // "online", "cash"
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // --- Accessors for UI Colors ---

    public function getOrderStatusColorAttribute()
    {
        return match ($this->order_status) {
            self::ORDER_COMPLETED => 'green',
            self::ORDER_PENDING => 'orange',
            self::ORDER_CANCELLED => 'red',
            self::ORDER_REFUNDED => 'blue',
            default => 'gray',
        };
    }

    public function getPaymentStatusColorAttribute()
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID => 'green',
            self::PAYMENT_PENDING => 'orange',
            self::PAYMENT_FAILED => 'red',
            self::PAYMENT_REFUNDED => 'blue',
            default => 'gray',
        };
    }

    /**
     * Centralized logic to calculate order totals based on items and applied promotion.
     * This follows the business rules:
     * 1. BOGO: Items are added with $0 price (Handled by OrderItem creation). Discount Amount is $0.
     * 2. PERCENTAGE (Specific): Discount only applies to the specific product's subtotal.
     * 3. PERCENTAGE (Global): Discount applies to the entire subtotal.
     */
    public function recalculateTotals()
    {
        $this->load(['items.product', 'promotion']);
        
        $subtotal = 0;
        $discount = 0;
        $promo = $this->promotion;

        foreach ($this->items as $item) {
            $itemTotal = $item->qty * $item->unit_price;
            $subtotal += $itemTotal;

            if ($promo && $promo->promo_type === 'PERCENTAGE') {
                $isPromoProduct = ($promo->product_id == $item->product_id);
                $isGlobalPromo = (!$promo->product_id);

                if ($isPromoProduct || $isGlobalPromo) {
                    $discount += ($itemTotal * $promo->discount_value) / 100;
                }
            }
        }

        // Note: For BOGO, the "Free" items should already be in 'items' with unit_price = 0
        // So they contribute $0 to subtotal and $0 to discount_amount automatically.

        $this->subtotal_amount = $subtotal;
        $this->discount_amount = $discount;
        $this->total_amount = max(0, $subtotal - $discount);
        $this->save();
        
        return $this;
    }

    // --- Relationships ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

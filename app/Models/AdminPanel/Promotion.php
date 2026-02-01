<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    // Define status constants for cleaner usage in controllers/views
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_EXPIRED = 'EXPIRED';

    protected $fillable = [
        'code',
        'name',
        'product_id',
        'promo_type',
        'discount_value',
        'buy_qty',
        'get_qty',
        'status', // "ACTIVE", "PAUSED", "DRAFT", "EXPIRED"
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'discount_value' => 'decimal:2',
    ];

    // --- Scopes for easier querying ---

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where(function($q) {
                         $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                     })
                     ->where(function($q) {
                         $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                     });
    }

    // --- Accessors ---

    // Optional: Return a human-readable status badge color for frontend
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_PAUSED => 'orange',
            self::STATUS_DRAFT => 'gray',
            self::STATUS_EXPIRED => 'red',
            default => 'gray',
        };
    }

    public function getDescriptionAttribute()
    {
        $suffix = $this->product ? " on {$this->product->name}" : "";
        
        if ($this->promo_type === 'PERCENTAGE') {
            return $this->discount_value . '% Discount' . $suffix;
        }
        
        if ($this->promo_type === 'BOGO') {
            return "Buy {$this->buy_qty} Get {$this->get_qty}" . $suffix;
        }

        return 'Fixed Discount' . $suffix;
    }

    // --- Relationships ---

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
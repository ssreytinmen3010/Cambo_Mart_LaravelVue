<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        // 'qty' is automatically cast to integer by Laravel usually, but explicit doesn't hurt
        'qty' => 'integer',
    ];

    // --- Accessors ---

    // Helper to get the total cost of this line item (e.g. 2 items * $10 = $20)
    // Access in Vue as: item.line_total
    public function getLineTotalAttribute()
    {
        return $this->qty * $this->unit_price;
    }

    // --- Relationships ---

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
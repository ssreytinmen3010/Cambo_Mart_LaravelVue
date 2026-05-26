<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    const STATUS_SALE = 'Sale';
    const STATUS_SALE_OUT = 'Sale Out';

    protected $fillable = [
        'name',
        'product_code',
        'description',
        'price',
        'stock',
        'category_id',
        'brand_id',
        'image',
        'status',
        'status_stock',
        'cal_avg_rating',
    ];

    protected $casts = [
        'cal_avg_rating' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($product) {
            // Force status_stock based on stock count
            $stock = (int)$product->stock;
            if ($stock <= 0) {
                $product->status_stock = self::STATUS_SALE_OUT;
            } else {
                $product->status_stock = self::STATUS_SALE;
            }
        });
    }

    public function getStatusAttribute($value)
    {
        return $value ? 'Active' : 'Inactive';
    }
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = ($value === 'Active' || $value === 1) ? true : false;
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function promotion(): HasOne
    {
        return $this->hasOne(Promotion::class, 'product_id');
    }
}

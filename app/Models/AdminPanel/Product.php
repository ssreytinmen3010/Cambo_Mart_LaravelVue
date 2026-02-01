<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

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
    ];

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
}

<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'image',
        'description',
        'brand_id',
        'qty_item',
        'status',
    ];

    protected $casts = [
        'qty_item' => 'integer',
        'status' => 'boolean',
    ];

    public function getStatusAttribute($value)
    {
        return $value ? 'Active' : 'Inactive';
    }
    
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = ($value === 'Active' || $value === 1) ? true : false;
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_category');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

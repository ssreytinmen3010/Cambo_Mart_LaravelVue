<?php

namespace App\Models\AdminPanel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'brand_category');
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

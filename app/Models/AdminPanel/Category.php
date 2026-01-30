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

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

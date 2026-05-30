<?php

namespace App\Models;

use App\Models\AdminPanel\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryOrder extends Model
{
    use HasFactory;

    protected $table = 'history_orders';

    protected $fillable = [
        'user_id',
        'order_id',
        'address_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

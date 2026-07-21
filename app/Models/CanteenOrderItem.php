<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanteenOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(CanteenOrder::class, 'order_id');
    }

    public function menu()
    {
        return $this->belongsTo(CanteenMenu::class, 'menu_id');
    }
}

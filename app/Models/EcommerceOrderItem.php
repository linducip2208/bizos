<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceOrderItem extends Model
{
    protected $fillable = [
        'ecommerce_order_id',
        'channel_sku',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function ecommerceOrder()
    {
        return $this->belongsTo(EcommerceOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

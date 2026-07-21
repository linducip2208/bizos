<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'description',
        'quantity',
        'delivered_qty',
        'unit_price',
        'tax_rate',
        'discount_percent',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'delivered_qty' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class, 'so_item_id');
    }
}

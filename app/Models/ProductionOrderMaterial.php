<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderMaterial extends Model
{
    protected $table = 'production_order_materials';

    protected $fillable = [
        'production_order_id',
        'bom_item_id',
        'product_id',
        'required_quantity',
        'issued_quantity',
        'returned_quantity',
        'status',
    ];

    protected $casts = [
        'required_quantity' => 'decimal:4',
        'issued_quantity' => 'decimal:4',
        'returned_quantity' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function bomItem()
    {
        return $this->belongsTo(BomItem::class, 'bom_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

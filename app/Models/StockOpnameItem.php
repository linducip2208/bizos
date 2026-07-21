<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpnameItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'product_variant_id',
        'system_quantity',
        'physical_quantity',
        'difference',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:4',
        'physical_quantity' => 'decimal:4',
        'difference' => 'decimal:4',
        'unit_cost' => 'decimal:2',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}

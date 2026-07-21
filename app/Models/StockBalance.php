<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockBalance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'quantity',
        'average_cost',
        'last_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'average_cost' => 'decimal:2',
        'last_cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}

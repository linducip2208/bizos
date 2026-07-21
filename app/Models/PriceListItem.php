<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceListItem extends Model
{
    protected $fillable = [
        'price_list_id',
        'product_id',
        'unit_price',
        'min_quantity',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'min_quantity' => 'decimal:2',
    ];

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

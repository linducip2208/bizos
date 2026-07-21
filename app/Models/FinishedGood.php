<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinishedGood extends Model
{
    protected $fillable = [
        'production_order_id',
        'product_id',
        'quantity',
        'accepted_at',
        'quality_status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'accepted_at' => 'datetime',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VanInventory extends Model
{
    protected $table = 'van_inventories';

    protected $fillable = [
        'van_id',
        'product_id',
        'quantity',
        'min_quantity',
        'reorder_point',
        'last_restock_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'min_quantity' => 'decimal:3',
        'reorder_point' => 'decimal:3',
        'last_restock_date' => 'date',
    ];

    public function van()
    {
        return $this->belongsTo(TechnicianVan::class, 'van_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

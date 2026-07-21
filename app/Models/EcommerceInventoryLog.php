<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceInventoryLog extends Model
{
    protected $fillable = [
        'channel_id',
        'product_id',
        'old_stock',
        'new_stock',
        'channel_stock',
        'sync_status',
        'synced_at',
    ];

    protected $casts = [
        'old_stock' => 'decimal:2',
        'new_stock' => 'decimal:2',
        'channel_stock' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(EcommerceChannel::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

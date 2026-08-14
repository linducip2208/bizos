<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenOrderItem extends Model
{
    protected $fillable = [
        'kitchen_order_id',
        'product_id',
        'quantity',
        'notes',
        'modifiers',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'modifiers' => 'array',
    ];

    public function kitchenOrder()
    {
        return $this->belongsTo(KitchenOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPreparing(): bool
    {
        return $this->status === 'preparing';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isServed(): bool
    {
        return $this->status === 'served';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}

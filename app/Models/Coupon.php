<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'promotion_id',
        'code',
        'discount',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'min_purchase',
        'is_active',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'min_purchase' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}

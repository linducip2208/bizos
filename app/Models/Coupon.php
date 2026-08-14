<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'promotion_id',
        'code',
        'discount',
        'discount_type',
        'auto_apply',
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
        'auto_apply' => 'boolean',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function isApplicable(float $subtotal): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($subtotal < (float) $this->min_purchase) {
            return false;
        }

        return true;
    }

    public function discountAmount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round($subtotal * ((float) $this->discount / 100), 2);
        }

        return round(min((float) $this->discount, $subtotal), 2);
    }
}

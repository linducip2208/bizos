<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'short_code',
        'type',
        'pack_price',
        'pack_charge_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'pack_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isDineIn(): bool
    {
        return $this->type === 'dine_in';
    }

    public function isTakeaway(): bool
    {
        return $this->type === 'takeaway';
    }

    public function isDelivery(): bool
    {
        return $this->type === 'delivery';
    }

    public function getPackCharge(float $subtotal): float
    {
        if (!$this->pack_price || (float) $this->pack_price <= 0) {
            return 0.0;
        }

        $price = (float) $this->pack_price;

        if ($this->pack_charge_type === 'percentage') {
            return round($subtotal * ($price / 100), 2);
        }

        return round($price, 2);
    }
}

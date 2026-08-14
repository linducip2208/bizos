<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'gateway_type',
        'gateway_config_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function gatewayConfig()
    {
        return $this->belongsTo(PaymentGatewayConfig::class, 'gateway_config_id');
    }
}

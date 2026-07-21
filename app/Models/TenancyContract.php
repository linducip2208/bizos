<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenancyContract extends Model
{
    protected $fillable = [
        'company_id',
        'property_unit_id',
        'client_id',
        'contract_number',
        'start_date',
        'end_date',
        'monthly_rent',
        'deposit_amount',
        'service_charge_monthly',
        'sinking_fund_monthly',
        'payment_due_day',
        'late_fee_percent',
        'renewal_option',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'service_charge_monthly' => 'decimal:2',
        'sinking_fund_monthly' => 'decimal:2',
        'late_fee_percent' => 'decimal:2',
        'renewal_option' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function propertyUnit()
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceChargeInvoices()
    {
        return $this->hasMany(ServiceChargeInvoice::class);
    }
}

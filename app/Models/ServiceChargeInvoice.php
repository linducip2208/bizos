<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceChargeInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'property_unit_id',
        'tenancy_contract_id',
        'period_start',
        'period_end',
        'invoice_number',
        'rent_amount',
        'service_charge',
        'sinking_fund',
        'electricity',
        'water',
        'other_charges',
        'total_amount',
        'due_date',
        'status',
        'finance_invoice_id',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'rent_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'sinking_fund' => 'decimal:2',
        'electricity' => 'decimal:2',
        'water' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function propertyUnit()
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function tenancyContract()
    {
        return $this->belongsTo(TenancyContract::class);
    }

    public function financeInvoice()
    {
        return $this->belongsTo(Invoice::class, 'finance_invoice_id');
    }
}

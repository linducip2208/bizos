<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    protected $fillable = [
        'company_id',
        'property_unit_id',
        'client_id',
        'contract_number',
        'start_date',
        'end_date',
        'monthly_rent',
        'deposit',
        'renewal_option',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'deposit' => 'decimal:2',
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
}

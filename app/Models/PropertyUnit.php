<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyUnit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'property_type',
        'unit_number',
        'building_name',
        'floor',
        'land_area_sqm',
        'building_area_sqm',
        'bedrooms',
        'bathrooms',
        'address',
        'ownership_certificate',
        'purchase_date',
        'purchase_price',
        'current_market_value',
        'status',
    ];

    protected $casts = [
        'land_area_sqm' => 'decimal:2',
        'building_area_sqm' => 'decimal:2',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_market_value' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tenancyContracts()
    {
        return $this->hasMany(TenancyContract::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}

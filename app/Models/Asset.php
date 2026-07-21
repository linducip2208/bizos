<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'company_id',
        'category_id',
        'asset_code',
        'name',
        'description',
        'acquisition_date',
        'acquisition_cost',
        'useful_life_years',
        'salvage_value',
        'current_value',
        'accumulated_depreciation',
        'location',
        'current_employee_id',
        'status',
        'purchase_invoice_id',
        'warranty_expiry',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'useful_life_years' => 'integer',
        'salvage_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'warranty_expiry' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function currentEmployee()
    {
        return $this->belongsTo(Employee::class, 'current_employee_id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(Invoice::class, 'purchase_invoice_id');
    }

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function mutations()
    {
        return $this->hasMany(AssetMutation::class);
    }

    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class);
    }
}

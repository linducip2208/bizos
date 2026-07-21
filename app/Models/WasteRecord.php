<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteRecord extends Model
{
    protected $fillable = [
        'company_id', 'branch_id', 'record_date', 'waste_type',
        'waste_subtype', 'quantity_kg', 'source', 'production_waste_log_id',
        'disposal_method', 'disposal_vendor', 'disposal_cost', 'is_hazardous',
        'manifest_number', 'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'quantity_kg' => 'decimal:3',
        'disposal_cost' => 'decimal:2',
        'is_hazardous' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function productionWasteLog()
    {
        return $this->belongsTo(WasteLog::class, 'production_waste_log_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeForPeriod($query, string $period)
    {
        [$year, $month] = explode('-', $period);
        return $query->whereYear('record_date', $year)
            ->whereMonth('record_date', $month);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('waste_type', $type);
    }
}

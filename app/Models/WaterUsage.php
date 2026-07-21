<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterUsage extends Model
{
    protected $fillable = [
        'company_id', 'branch_id', 'record_date', 'source',
        'quantity_m3', 'purpose', 'cost', 'meter_number',
        'meter_reading_start', 'meter_reading_end', 'is_recycled',
        'recycled_percentage', 'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'quantity_m3' => 'decimal:3',
        'cost' => 'decimal:2',
        'meter_reading_start' => 'decimal:3',
        'meter_reading_end' => 'decimal:3',
        'is_recycled' => 'boolean',
        'recycled_percentage' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
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
}

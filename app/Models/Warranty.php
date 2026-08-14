<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'duration_value',
        'duration_type',
        'terms',
        'is_active',
    ];

    protected $casts = [
        'duration_value' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function registrations()
    {
        return $this->hasMany(WarrantyRegistration::class);
    }

    public function getDurationLabel(): string
    {
        $value = (int) $this->duration_value;

        $unit = match ($this->duration_type) {
            'days' => $value > 1 ? 'Hari' : 'Hari',
            'months' => $value > 1 ? 'Bulan' : 'Bulan',
            'years' => $value > 1 ? 'Tahun' : 'Tahun',
            default => 'Bulan',
        };

        return $value . ' ' . $unit;
    }
}

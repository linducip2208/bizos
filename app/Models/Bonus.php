<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'type',
        'calculation_type',
        'amount',
        'is_active',
    ];

    protected $casts = [
        'type' => 'string',
        'calculation_type' => 'string',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeBonuses()
    {
        return $this->hasMany(EmployeeBonus::class);
    }
}

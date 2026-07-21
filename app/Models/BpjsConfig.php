<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpjsConfig extends Model
{
    protected $fillable = [
        'company_id',
        'bpjs_type',
        'company_rate',
        'employee_rate',
        'max_salary_cap',
        'effective_year',
        'is_active',
    ];

    protected $casts = [
        'company_rate' => 'decimal:4',
        'employee_rate' => 'decimal:4',
        'max_salary_cap' => 'decimal:2',
        'effective_year' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialConsolidation extends Model
{
    protected $fillable = [
        'parent_company_id',
        'child_company_id',
        'consolidation_type',
        'period_year',
        'period_month',
        'mapping_config',
        'status',
    ];

    protected $casts = [
        'mapping_config' => 'array',
    ];

    public function parentCompany()
    {
        return $this->belongsTo(Company::class, 'parent_company_id');
    }

    public function childCompany()
    {
        return $this->belongsTo(Company::class, 'child_company_id');
    }
}

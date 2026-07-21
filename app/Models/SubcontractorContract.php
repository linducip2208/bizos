<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcontractorContract extends Model
{
    protected $fillable = [
        'company_id',
        'project_id',
        'supplier_id',
        'contract_number',
        'scope_of_work',
        'contract_amount',
        'retention_percent',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'contract_amount' => 'decimal:2',
        'retention_percent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

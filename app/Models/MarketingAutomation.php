<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class MarketingAutomation extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'name',
        'trigger_type',
        'trigger_config',
        'actions',
        'status',
        'execution_count',
        'last_executed_at',
        'created_by',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'actions' => 'array',
        'execution_count' => 'integer',
        'last_executed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}

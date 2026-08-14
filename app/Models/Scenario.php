<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scenario extends Model
{
    protected $table = 'scenarios';

    protected $fillable = [
        'company_id',
        'name',
        'scenario_type',
        'description',
        'assumptions',
        'parent_budget_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'assumptions' => 'array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentBudget()
    {
        return $this->belongsTo(Budget::class, 'parent_budget_id');
    }
}

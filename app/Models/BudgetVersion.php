<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetVersion extends Model
{
    protected $fillable = [
        'budget_id',
        'company_id',
        'version_number',
        'name',
        'description',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

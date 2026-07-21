<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'module',
        'is_active',
        'min_approvers',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_approvers' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function levels()
    {
        return $this->hasMany(ApprovalLevel::class, 'workflow_id')->orderBy('level');
    }

    public function requests()
    {
        return $this->hasMany(ApprovalRequest::class, 'workflow_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }
}

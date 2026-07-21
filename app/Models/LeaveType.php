<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'default_days',
        'max_days',
        'is_annual',
        'is_paid',
        'require_attachment',
        'require_approval',
        'min_approval_level',
        'applicable_gender',
        'applicable_marital',
        'color',
        'is_active',
    ];

    protected $casts = [
        'default_days' => 'integer',
        'max_days' => 'integer',
        'is_annual' => 'boolean',
        'is_paid' => 'boolean',
        'require_attachment' => 'boolean',
        'require_approval' => 'boolean',
        'min_approval_level' => 'integer',
        'applicable_gender' => 'string',
        'applicable_marital' => 'string',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}

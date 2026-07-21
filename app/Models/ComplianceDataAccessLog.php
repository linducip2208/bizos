<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceDataAccessLog extends Model
{
    protected $fillable = [
        'company_id',
        'accessed_by',
        'data_subject_type',
        'data_subject_id',
        'purpose',
        'legal_basis',
        'access_method',
        'accessed_fields',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'accessed_fields' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function accessor()
    {
        return $this->belongsTo(User::class, 'accessed_by');
    }
}

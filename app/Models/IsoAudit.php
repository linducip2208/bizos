<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsoAudit extends Model
{
    protected $fillable = [
        'company_id',
        'audit_number',
        'title',
        'audit_type',
        'scope',
        'criteria',
        'auditor_name',
        'auditor_external',
        'planned_date',
        'actual_date',
        'completed_date',
        'status',
        'result',
        'summary',
        'conclusion',
        'lead_auditor_id',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
        'completed_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function leadAuditor()
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    public function findings()
    {
        return $this->hasMany(IsoAuditFinding::class);
    }
}

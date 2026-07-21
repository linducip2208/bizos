<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsoAuditFinding extends Model
{
    protected $fillable = [
        'iso_audit_id',
        'finding_number',
        'classification',
        'iso_clause',
        'description',
        'evidence',
        'corrective_action',
        'responsible_person_id',
        'target_date',
        'closed_date',
        'status',
        'verification_notes',
        'verified_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'closed_date' => 'date',
    ];

    public function audit()
    {
        return $this->belongsTo(IsoAudit::class, 'iso_audit_id');
    }

    public function responsiblePerson()
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

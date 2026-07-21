<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsoIncident extends Model
{
    protected $fillable = [
        'company_id',
        'incident_number',
        'title',
        'incident_type',
        'severity',
        'description',
        'detected_at',
        'resolved_at',
        'affected_assets',
        'affected_systems',
        'findings',
        'root_cause',
        'corrective_actions',
        'preventive_actions',
        'status',
        'reported_by',
        'investigated_by',
        'closed_by',
        'reportable_to_regulator',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'reportable_to_regulator' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}

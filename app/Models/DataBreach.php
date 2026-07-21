<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataBreach extends Model
{
    protected $fillable = [
        'company_id',
        'breach_type',
        'severity',
        'description',
        'discovered_at',
        'contained_at',
        'resolved_at',
        'affected_records_count',
        'affected_data_types',
        'root_cause',
        'immediate_actions',
        'corrective_actions',
        'notified_dpa_at',
        'notified_subjects_at',
        'dpa_report_number',
        'status',
        'reported_by',
        'investigated_by',
    ];

    protected $casts = [
        'discovered_at' => 'datetime',
        'contained_at' => 'datetime',
        'resolved_at' => 'datetime',
        'notified_dpa_at' => 'datetime',
        'notified_subjects_at' => 'datetime',
        'affected_data_types' => 'array',
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
}

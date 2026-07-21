<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpiaAssessment extends Model
{
    protected $table = 'dpia_assessments';

    protected $fillable = [
        'company_id',
        'title',
        'processing_activity',
        'description',
        'data_controller',
        'data_processor',
        'data_types',
        'data_subjects',
        'risks',
        'mitigations',
        'necessity_proportionality',
        'status',
        'risk_level',
        'reviewed_at',
        'reviewed_by',
        'created_by',
        'review_notes',
    ];

    protected $casts = [
        'data_types' => 'array',
        'data_subjects' => 'array',
        'risks' => 'array',
        'mitigations' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

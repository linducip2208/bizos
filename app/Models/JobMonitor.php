<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobMonitor extends Model
{
    protected $fillable = [
        'company_id',
        'job_id',
        'job_name',
        'status',
        'progress_percent',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'failed']);
    }
}

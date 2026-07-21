<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueMonitor extends Model
{
    protected $fillable = [
        'company_id',
        'queue_name',
        'pending_count',
        'processing_count',
        'failed_count',
        'checked_at',
    ];

    protected $casts = [
        'pending_count' => 'integer',
        'processing_count' => 'integer',
        'failed_count' => 'integer',
        'checked_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getTotalJobs(): int
    {
        return $this->pending_count + $this->processing_count + $this->failed_count;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressBilling extends Model
{
    protected $fillable = [
        'company_id',
        'project_id',
        'invoice_id',
        'billing_number',
        'billing_period_start',
        'billing_period_end',
        'physical_progress_percent',
        'previous_claimed_percent',
        'current_claimed_percent',
        'gross_amount',
        'retention_percent',
        'retention_amount',
        'net_amount',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'physical_progress_percent' => 'decimal:2',
        'previous_claimed_percent' => 'decimal:2',
        'current_claimed_percent' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'retention_percent' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

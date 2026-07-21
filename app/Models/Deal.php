<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $fillable = [
        'company_id',
        'lead_id',
        'client_id',
        'project_id',
        'stage_id',
        'assigned_to',
        'title',
        'expected_value',
        'expected_close_date',
        'actual_close_date',
        'status',
        'lost_reason',
        'notes',
        'invoice_id',
    ];

    protected $casts = [
        'expected_value' => 'decimal:2',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function commissions()
    {
        return $this->hasMany(DealCommission::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function salesOrder()
    {
        return $this->hasOne(SalesOrder::class);
    }
}

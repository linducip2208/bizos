<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarrantyClaim extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'warranty_registration_id',
        'claim_number',
        'claim_date',
        'issue_description',
        'diagnosis',
        'resolution',
        'status',
        'resolution_type',
        'cost',
        'approved_by',
        'resolved_at',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'resolved_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function registration()
    {
        return $this->belongsTo(WarrantyRegistration::class, 'warranty_registration_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }
}

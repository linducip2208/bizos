<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'contract_number',
        'title',
        'contract_type',
        'status',
        'party_type',
        'party_id',
        'start_date',
        'end_date',
        'renewal_date',
        'value',
        'currency_id',
        'description',
        'terms_conditions',
        'sla_details',
        'obligations',
        'template_id',
        'signed_by',
        'signed_at',
        'approved_by',
        'approved_at',
        'parent_contract_id',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'value' => 'decimal:2',
        'sla_details' => 'json',
        'obligations' => 'json',
        'metadata' => 'json',
        'signed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentContract()
    {
        return $this->belongsTo(Contract::class, 'parent_contract_id');
    }

    public function childContracts()
    {
        return $this->hasMany(Contract::class, 'parent_contract_id');
    }

    public function party(): MorphTo
    {
        return $this->morphTo('party', 'party_type', 'party_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('contract_type', $type);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }
}

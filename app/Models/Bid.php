<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bid extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rfq_id',
        'supplier_id',
        'bid_number',
        'status',
        'submitted_at',
        'total_amount',
        'currency_id',
        'delivery_lead_time_days',
        'validity_days',
        'notes',
        'documents',
        'evaluated_by',
        'evaluated_at',
        'evaluation_score',
        'evaluation_notes',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'evaluated_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'evaluation_score' => 'decimal:2',
            'documents' => 'json',
        ];
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function items()
    {
        return $this->hasMany(BidItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isShortlisted(): bool
    {
        return $this->status === 'shortlisted';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'rfq_number',
        'title',
        'description',
        'supplier_category',
        'submission_deadline',
        'expected_delivery_date',
        'status',
        'currency_id',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'submission_deadline' => 'datetime',
            'expected_delivery_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(RfqItem::class);
    }

    public function rfqSuppliers()
    {
        return $this->hasMany(RfqSupplier::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'rfq_suppliers')
            ->withPivot('invited_at', 'responded_at', 'status')
            ->withTimestamps();
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isAwarded(): bool
    {
        return $this->status === 'awarded';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeAwarded($query)
    {
        return $query->where('status', 'awarded');
    }
}

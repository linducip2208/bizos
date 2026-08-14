<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntercompanyTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'from_company_id',
        'to_company_id',
        'transaction_type',
        'transaction_date',
        'reference_number',
        'amount',
        'currency_id',
        'exchange_rate',
        'description',
        'status',
        'journal_entry_id_from',
        'journal_entry_id_to',
        'approved_by',
        'approved_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'notes' => 'array',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fromCompany()
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    public function toCompany()
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function journalEntryFrom()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id_from');
    }

    public function journalEntryTo()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id_to');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    public function approve(?int $approvedBy = null): void
    {
        $this->status = 'approved';
        $this->approved_by = $approvedBy ?? auth()->id();
        $this->approved_at = now();
        $this->save();

        app(\App\Services\IntercompanyTransactionService::class)->processApproval($this);
    }

    public function reject(?int $rejectedBy = null): void
    {
        $this->status = 'rejected';
        $this->approved_by = $rejectedBy ?? auth()->id();
        $this->approved_at = now();
        $this->save();
    }

    public function complete(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    public function void(): void
    {
        $this->status = 'void';
        $this->save();
    }

    public function submitForApproval(): void
    {
        $this->status = 'pending_approval';
        $this->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalDelegation extends Model
{
    protected $fillable = [
        'approver_id',
        'delegate_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function delegate()
    {
        return $this->belongsTo(Employee::class, 'delegate_id');
    }

    public function scopeActive($query)
    {
        $today = now()->toDateString();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    public function scopeForApprover($query, int $approverId)
    {
        return $query->where('approver_id', $approverId);
    }
}

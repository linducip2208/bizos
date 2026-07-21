<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosingPeriod extends Model
{
    protected $fillable = [
        'company_id',
        'year',
        'month',
        'status',
        'closed_by',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}

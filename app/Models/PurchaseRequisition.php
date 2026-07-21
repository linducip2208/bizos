<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'pr_number',
        'department_id',
        'requested_by',
        'date_required',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_required' => 'date',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }
}

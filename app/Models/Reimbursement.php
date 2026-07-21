<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Concerns\HasBranchScope;

class Reimbursement extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'employee_id',
        'category_id',
        'date',
        'amount',
        'description',
        'status',
        'rejection_reason',
        'paid_date',
        'paid_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'status' => 'string',
        'paid_date' => 'date',
        'paid_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function category()
    {
        return $this->belongsTo(ReimbursementCategory::class, 'category_id');
    }

    public function reimbursementAttachments()
    {
        return $this->hasMany(ReimbursementAttachment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementCategory extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'max_amount',
        'require_receipt',
        'is_active',
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'require_receipt' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class, 'category_id');
    }
}

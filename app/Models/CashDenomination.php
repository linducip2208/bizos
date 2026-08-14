<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashDenomination extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'label',
        'value',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shiftDenominations()
    {
        return $this->hasMany(ShiftDenomination::class, 'denomination_id');
    }
}

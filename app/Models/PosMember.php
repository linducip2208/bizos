<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosMember extends Model
{
    protected $fillable = [
        'company_id',
        'member_code',
        'name',
        'phone',
        'email',
        'points',
        'total_spent',
        'join_date',
        'is_active',
    ];

    protected $casts = [
        'points' => 'integer',
        'total_spent' => 'decimal:2',
        'join_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function posTransactions()
    {
        return $this->hasMany(PosTransaction::class, 'member_id');
    }
}

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
        'points_balance',
        'tier',
        'total_points_earned',
        'birthday',
    ];

    protected $casts = [
        'points' => 'integer',
        'total_spent' => 'decimal:2',
        'join_date' => 'date',
        'is_active' => 'boolean',
        'points_balance' => 'integer',
        'total_points_earned' => 'integer',
        'birthday' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function posTransactions()
    {
        return $this->hasMany(PosTransaction::class, 'member_id');
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'member_id');
    }
}

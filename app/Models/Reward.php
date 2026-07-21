<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reward extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'image',
        'points_cost',
        'stock',
        'category',
        'is_active',
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function getRemainingStockAttribute(): int
    {
        return max(0, $this->stock - $this->redemptions()->count());
    }
}

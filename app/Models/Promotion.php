<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'config',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }
}

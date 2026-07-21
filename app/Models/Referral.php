<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'referrer_client_id',
        'referred_name',
        'referred_phone',
        'status',
        'reward_status',
        'notes',
    ];

    protected $casts = [];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function referrerClient()
    {
        return $this->belongsTo(Client::class, 'referrer_client_id');
    }
}

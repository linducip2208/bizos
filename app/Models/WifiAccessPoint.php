<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiAccessPoint extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'ssid',
        'bssid',
        'ip_address',
        'is_active',
    ];

    protected $casts = [
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
}

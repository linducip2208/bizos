<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SodConflict extends Model
{
    protected $fillable = [
        'company_id',
        'sod_rule_id',
        'user_id',
        'sensitive_permission',
        'conflicting_permission',
        'risk_level',
        'status',
        'mitigation',
        'resolution',
        'detected_at',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function rule()
    {
        return $this->belongsTo(SodRule::class, 'sod_rule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsoPolicyAcknowledgment extends Model
{
    protected $table = 'iso_policy_acks';

    protected $fillable = [
        'iso_policy_id',
        'employee_id',
        'user_id',
        'acknowledged_at',
        'ip_address',
        'signature_type',
        'notes',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function policy()
    {
        return $this->belongsTo(IsoPolicy::class, 'iso_policy_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

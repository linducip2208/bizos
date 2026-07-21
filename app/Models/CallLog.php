<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'employee_id',
        'lead_id',
        'client_id',
        'phone_number',
        'direction',
        'status',
        'duration_seconds',
        'notes',
        'scheduled_at',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'scheduled_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

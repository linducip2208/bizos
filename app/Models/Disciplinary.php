<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disciplinary extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'violation_type',
        'description',
        'action_taken',
        'issued_by',
        'issued_at',
        'effective_date',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'action_taken' => 'string',
        'issued_at' => 'date',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'status' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuer()
    {
        return $this->belongsTo(Employee::class, 'issued_by');
    }
}

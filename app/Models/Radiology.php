<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Radiology extends Model
{
    protected $fillable = [
        'company_id',
        'patient_id',
        'doctor_id',
        'order_date',
        'radiology_type',
        'body_part',
        'findings',
        'impression',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }
}

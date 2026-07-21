<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'visit_date',
        'subjective',
        'objective',
        'vital_signs',
        'assessment',
        'plan',
        'diagnosis_code',
        'diagnosis_name',
        'is_final',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'vital_signs' => 'array',
        'is_final' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function bpjsClaim()
    {
        return $this->hasOne(BpjsClaim::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }
}

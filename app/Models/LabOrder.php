<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'order_date',
        'lab_type',
        'notes',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date',
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

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function results()
    {
        return $this->hasMany(LabResult::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'patient_number',
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'birth_place',
        'religion',
        'blood_type',
        'nik',
        'bpjs_number',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'emergency_contact_name',
        'emergency_contact_phone',
        'allergies',
        'medical_history_notes',
        'is_active',
        'registered_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'registered_at' => 'date',
        'allergies' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            if (empty($patient->patient_number)) {
                $date = $patient->registered_at ? $patient->registered_at->format('Ymd') : now()->format('Ymd');
                $latest = static::where('patient_number', 'like', "RM-{$date}-%")
                    ->orderBy('patient_number', 'desc')
                    ->first();

                $sequence = $latest ? (int) substr($latest->patient_number, -4) + 1 : 1;
                $patient->patient_number = sprintf('RM-%s-%04d', $date, $sequence);
            }

            if (empty($patient->registered_at)) {
                $patient->registered_at = now();
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function bpjsClaims()
    {
        return $this->hasMany(BpjsClaim::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }
}

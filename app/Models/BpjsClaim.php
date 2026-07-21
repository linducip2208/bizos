<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BpjsClaim extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'patient_id',
        'medical_record_id',
        'claim_number',
        'sep_number',
        'ina_cbgs_code',
        'ina_cbgs_description',
        'claim_amount',
        'approved_amount',
        'status',
        'submitted_at',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'claim_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (BpjsClaim $claim) {
            if (empty($claim->claim_number)) {
                $date = now()->format('Ymd');
                $latest = static::where('claim_number', 'like', "BPJS-{$date}-%")
                    ->orderBy('claim_number', 'desc')
                    ->first();
                $sequence = $latest ? (int) substr($latest->claim_number, -4) + 1 : 1;
                $claim->claim_number = sprintf('BPJS-%s-%04d', $date, $sequence);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}

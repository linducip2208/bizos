<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureRequest extends Model
{
    protected $fillable = [
        'document_generation_id',
        'provider',
        'psre_provider_name',
        'psre_registered',
        'psre_certificate_number',
        'psre_certificate_valid_until',
        'external_id',
        'status',
        'signers',
        'completed_at',
        'audit_trail',
        'two_factor_required',
        'two_factor_verified_at',
        'legal_certificate_generated',
        'legal_certificate_path',
        'geo_location',
    ];

    protected $casts = [
        'signers' => 'array',
        'audit_trail' => 'array',
        'completed_at' => 'datetime',
        'psre_certificate_valid_until' => 'datetime',
        'two_factor_verified_at' => 'datetime',
        'psre_registered' => 'boolean',
        'two_factor_required' => 'boolean',
        'legal_certificate_generated' => 'boolean',
    ];

    public function documentGeneration()
    {
        return $this->belongsTo(DocumentGeneration::class);
    }
}

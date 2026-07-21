<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureProvider extends Model
{
    protected $table = 'signature_providers';

    protected $fillable = [
        'company_id',
        'name',
        'api_key_encrypted',
        'base_url',
        'api_format',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function signatureRequests()
    {
        return $this->hasMany(SignatureRequest::class, 'provider', 'name');
    }
}

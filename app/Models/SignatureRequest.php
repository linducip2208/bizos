<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureRequest extends Model
{
    protected $fillable = [
        'document_generation_id',
        'provider',
        'external_id',
        'status',
        'signers',
        'completed_at',
    ];

    protected $casts = [
        'signers' => 'array',
        'completed_at' => 'datetime',
    ];

    public function documentGeneration()
    {
        return $this->belongsTo(DocumentGeneration::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentGeneration extends Model
{
    protected $fillable = [
        'template_id',
        'module',
        'module_id',
        'generated_by',
        'file_path',
        'status',
        'signed_at',
        'signed_by',
        'signature_provider',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function signatureRequests()
    {
        return $this->hasMany(SignatureRequest::class);
    }

    public function moduleRecord()
    {
        return $this->morphTo('module', 'module', 'module_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'activity_type',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}

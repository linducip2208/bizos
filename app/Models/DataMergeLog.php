<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataMergeLog extends Model
{
    protected $fillable = [
        'company_id',
        'entity_type',
        'target_id',
        'source_ids',
        'merged_fields',
        'merged_by',
    ];

    protected $casts = [
        'source_ids' => 'array',
        'merged_fields' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function merger()
    {
        return $this->belongsTo(User::class, 'merged_by');
    }
}

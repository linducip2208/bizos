<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'company_id',
        'entity_type',
        'filename',
        'total_rows',
        'success_count',
        'error_count',
        'status',
        'errors',
        'imported_by',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'success_count' => 'integer',
        'error_count' => 'integer',
        'errors' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}

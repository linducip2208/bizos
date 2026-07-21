<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'report_template_id',
        'generated_by',
        'snapshot_data',
        'format',
        'file_path',
        'file_size',
        'created_at',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    public function reportTemplate()
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

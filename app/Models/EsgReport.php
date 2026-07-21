<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EsgReport extends Model
{
    protected $fillable = [
        'company_id', 'title', 'period', 'period_start', 'period_end',
        'framework', 'status', 'file_path', 'report_data', 'scores',
        'executive_summary', 'prepared_by', 'reviewed_by', 'published_at',
    ];

    protected $casts = [
        'report_data' => 'array',
        'scores' => 'array',
        'published_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

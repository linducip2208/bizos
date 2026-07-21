<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvancedReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'config',
        'data_source',
        'row_fields',
        'column_fields',
        'value_fields',
        'filters',
        'calculated_fields',
        'conditional_formats',
        'chart_config',
        'created_by',
        'is_public',
        'embed_token',
    ];

    protected $casts = [
        'config' => 'array',
        'data_source' => 'array',
        'row_fields' => 'array',
        'column_fields' => 'array',
        'value_fields' => 'array',
        'filters' => 'array',
        'calculated_fields' => 'array',
        'conditional_formats' => 'array',
        'chart_config' => 'array',
        'is_public' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::creating(function (AdvancedReport $report) {
            if (empty($report->embed_token)) {
                $report->embed_token = bin2hex(random_bytes(32));
            }
        });
    }
}

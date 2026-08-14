<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'printer_type',
        'connection_type',
        'ip_address',
        'port',
        'paper_width',
        'character_per_line',
        'is_default',
        'status',
        'created_by',
    ];

    protected $casts = [
        'port' => 'integer',
        'paper_width' => 'integer',
        'character_per_line' => 'integer',
        'is_default' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function booted(): void
    {
        static::saving(function (Printer $printer) {
            if ($printer->is_default) {
                static::where('company_id', $printer->company_id)
                    ->when($printer->branch_id, fn ($q) => $q->where('branch_id', $printer->branch_id))
                    ->where('id', '!=', $printer->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}

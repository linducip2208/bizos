<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptLayout extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'type',
        'header_text',
        'footer_text',
        'show_logo',
        'show_qr',
        'show_tax_summary',
        'show_payment_summary',
        'font_size',
        'layout_config',
        'is_default',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_qr' => 'boolean',
        'show_tax_summary' => 'boolean',
        'show_payment_summary' => 'boolean',
        'layout_config' => 'array',
        'is_default' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function booted(): void
    {
        static::saving(function (ReceiptLayout $layout) {
            if ($layout->is_default) {
                static::where('company_id', $layout->company_id)
                    ->where('type', $layout->type)
                    ->where('id', '!=', $layout->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}

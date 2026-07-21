<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTheme extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'primary_color',
        'secondary_color',
        'accent_color',
        'background_color',
        'text_color',
        'font_family',
        'border_radius',
        'button_style',
        'sidebar_style',
        'dark_mode_colors',
        'logo_path',
        'favicon_path',
        'custom_css',
        'is_active',
    ];

    protected $casts = [
        'dark_mode_colors' => 'array',
        'border_radius' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $table = 'company_themes';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function presets(): array
    {
        return [
            'classic_indigo' => [
                'name' => 'Classic Indigo',
                'primary_color' => '#4f46e5',
                'secondary_color' => '#7c3aed',
                'accent_color' => '#2dd4bf',
                'background_color' => '#f8fafc',
                'text_color' => '#1e293b',
                'font_family' => 'Inter',
                'border_radius' => 12,
                'button_style' => 'rounded',
                'sidebar_style' => 'default',
            ],
            'dark_modern' => [
                'name' => 'Dark Modern',
                'primary_color' => '#818cf8',
                'secondary_color' => '#a78bfa',
                'accent_color' => '#34d399',
                'background_color' => '#0f172a',
                'text_color' => '#e2e8f0',
                'font_family' => 'Inter',
                'border_radius' => 8,
                'button_style' => 'pill',
                'sidebar_style' => 'compact',
            ],
            'green_corporate' => [
                'name' => 'Green Corporate',
                'primary_color' => '#059669',
                'secondary_color' => '#10b981',
                'accent_color' => '#f59e0b',
                'background_color' => '#f0fdf4',
                'text_color' => '#064e3b',
                'font_family' => 'Inter',
                'border_radius' => 10,
                'button_style' => 'rounded',
                'sidebar_style' => 'colorful',
            ],
            'orange_energetic' => [
                'name' => 'Orange Energetic',
                'primary_color' => '#ea580c',
                'secondary_color' => '#f97316',
                'accent_color' => '#06b6d4',
                'background_color' => '#fff7ed',
                'text_color' => '#431407',
                'font_family' => 'Inter',
                'border_radius' => 14,
                'button_style' => 'rounded',
                'sidebar_style' => 'default',
            ],
            'minimal_grey' => [
                'name' => 'Minimal Grey',
                'primary_color' => '#64748b',
                'secondary_color' => '#475569',
                'accent_color' => '#0ea5e9',
                'background_color' => '#ffffff',
                'text_color' => '#334155',
                'font_family' => 'Inter',
                'border_radius' => 6,
                'button_style' => 'square',
                'sidebar_style' => 'compact',
            ],
            'blue_ocean' => [
                'name' => 'Blue Ocean',
                'primary_color' => '#2563eb',
                'secondary_color' => '#3b82f6',
                'accent_color' => '#f472b6',
                'background_color' => '#eff6ff',
                'text_color' => '#1e3a5f',
                'font_family' => 'Inter',
                'border_radius' => 16,
                'button_style' => 'pill',
                'sidebar_style' => 'default',
            ],
        ];
    }
}

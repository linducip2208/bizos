<?php

namespace App\Services;

use App\Models\CompanyTheme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeBuilderService
{
    public function getTheme(int $companyId): array
    {
        $theme = CompanyTheme::where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if (!$theme) {
            $theme = $this->createDefaultTheme($companyId);
        }

        return $theme->toArray();
    }

    public function saveTheme(int $companyId, array $config): CompanyTheme
    {
        $theme = CompanyTheme::where('company_id', $companyId)->first();

        if ($theme) {
            $theme->update($config);
        } else {
            $config['company_id'] = $companyId;
            $config['is_active'] = true;
            $theme = CompanyTheme::create($config);
        }

        Cache::forget("company_theme_{$companyId}");
        $this->generateCssFile($companyId, $theme);

        return $theme->refresh();
    }

    public function resetTheme(int $companyId): void
    {
        CompanyTheme::where('company_id', $companyId)->delete();
        $this->createDefaultTheme($companyId);
        $this->deleteCssFile($companyId);
        Cache::forget("company_theme_{$companyId}");
    }

    public function previewTheme(array $config): string
    {
        $bgColor = $config['background_color'] ?? '#f8fafc';
        $primaryColor = $config['primary_color'] ?? '#4f46e5';
        $secondaryColor = $config['secondary_color'] ?? '#7c3aed';
        $textColor = $config['text_color'] ?? '#1e293b';

        return "data:image/svg+xml," . rawurlencode(
            "<svg xmlns='http://www.w3.org/2000/svg' width='600' height='400' viewBox='0 0 600 400'>"
            . "<rect width='600' height='400' fill='{$bgColor}' rx='12'/>"
            . "<rect x='0' y='0' width='180' height='400' fill='{$primaryColor}' rx='12' opacity='0.9'/>"
            . "<rect x='15' y='20' width='150' height='30' fill='white' rx='6' opacity='0.2'/>"
            . "<rect x='15' y='65' width='120' height='12' fill='white' rx='4' opacity='0.15'/>"
            . "<rect x='15' y='90' width='140' height='12' fill='white' rx='4' opacity='0.15'/>"
            . "<rect x='15' y='115' width='100' height='12' fill='white' rx='4' opacity='0.15'/>"
            . "<rect x='15' y='150' width='150' height='12' fill='white' rx='4' opacity='0.15'/>"
            . "<rect x='200' y='20' width='380' height='40' fill='white' rx='8' opacity='0.8'/>"
            . "<rect x='200' y='80' width='180' height='150' fill='white' rx='8' opacity='0.6'/>"
            . "<rect x='400' y='80' width='180' height='150' fill='white' rx='8' opacity='0.6'/>"
            . "<rect x='200' y='250' width='380' height='130' fill='white' rx='8' opacity='0.6'/>"
            . "<circle cx='570' cy='50' r='15' fill='{$primaryColor}' opacity='0.3'/>"
            . "<circle cx='550' cy='370' r='30' fill='{$secondaryColor}' opacity='0.2'/>"
            . "</svg>"
        );
    }

    public function exportTheme(int $companyId): array
    {
        $theme = CompanyTheme::where('company_id', $companyId)->first();

        if (!$theme) {
            throw new \RuntimeException('Theme tidak ditemukan.');
        }

        return [
            'name' => $theme->name,
            'primary_color' => $theme->primary_color,
            'secondary_color' => $theme->secondary_color,
            'accent_color' => $theme->accent_color,
            'background_color' => $theme->background_color,
            'text_color' => $theme->text_color,
            'font_family' => $theme->font_family,
            'border_radius' => $theme->border_radius,
            'button_style' => $theme->button_style,
            'sidebar_style' => $theme->sidebar_style,
            'dark_mode_colors' => $theme->dark_mode_colors,
            'custom_css' => $theme->custom_css,
            'exported_at' => now()->toIso8601String(),
        ];
    }

    public function importTheme(int $companyId, array $themeData): CompanyTheme
    {
        $themeData['company_id'] = $companyId;
        $themeData['is_active'] = true;

        CompanyTheme::where('company_id', $companyId)->delete();

        $theme = CompanyTheme::create($themeData);
        Cache::forget("company_theme_{$companyId}");

        return $theme;
    }

    public function generateCss(array $config): string
    {
        $primary = $config['primary_color'] ?? '#4f46e5';
        $secondary = $config['secondary_color'] ?? '#7c3aed';
        $accent = $config['accent_color'] ?? '#2dd4bf';
        $bg = $config['background_color'] ?? '#f8fafc';
        $text = $config['text_color'] ?? '#1e293b';
        $font = $config['font_family'] ?? 'Inter';
        $borderRadius = (int) ($config['border_radius'] ?? 12);
        $buttonStyle = $config['button_style'] ?? 'rounded';
        $sidebarStyle = $config['sidebar_style'] ?? 'default';

        $buttonRadius = match ($buttonStyle) {
            'pill' => '9999px',
            'square' => '4px',
            default => $borderRadius . 'px',
        };

        $sidebarWidth = match ($sidebarStyle) {
            'compact' => '14rem',
            'colorful' => '16rem',
            default => '15.5rem',
        };

        $css = ":root {\n";
        $css .= "  --brand-primary: {$primary};\n";
        $css .= "  --brand-primary-rgb: {$this->hexToRgb($primary)};\n";
        $css .= "  --brand-secondary: {$secondary};\n";
        $css .= "  --brand-secondary-rgb: {$this->hexToRgb($secondary)};\n";
        $css .= "  --brand-accent: {$accent};\n";
        $css .= "  --brand-bg: {$bg};\n";
        $css .= "  --brand-text: {$text};\n";
        $css .= "  --brand-font: '{$font}', system-ui, -apple-system, sans-serif;\n";
        $css .= "  --brand-radius: {$borderRadius}px;\n";
        $css .= "  --brand-button-radius: {$buttonRadius};\n";
        $css .= "  --brand-sidebar-width: {$sidebarWidth};\n";
        $css .= "}\n\n";

        $css .= "body {\n";
        $css .= "  font-family: var(--brand-font);\n";
        $css .= "}\n\n";

        $css .= ".fi-sidebar {\n";
        $css .= "  background-color: var(--brand-primary);\n";
        $css .= "}\n\n";

        $css .= ".fi-btn.fi-btn-primary {\n";
        $css .= "  background-image: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));\n";
        $css .= "  border-radius: var(--brand-button-radius);\n";
        $css .= "}\n\n";

        $css .= ".fi-section {\n";
        $css .= "  border-radius: var(--brand-radius);\n";
        $css .= "}\n\n";

        $css .= ".fi-wi-stats-overview-stat {\n";
        $css .= "  border-radius: var(--brand-radius);\n";
        $css .= "}\n\n";

        if (!empty($config['custom_css'])) {
            $css .= "/* Custom CSS */\n{$config['custom_css']}\n";
        }

        return $css;
    }

    public function getPresets(): array
    {
        return CompanyTheme::presets();
    }

    public function getAvailableFonts(): array
    {
        return [
            'Inter' => 'Inter (Modern, Clean)',
            'Poppins' => 'Poppins (Geometric, Friendly)',
            'Plus Jakarta Sans' => 'Plus Jakarta Sans (Elegant)',
            'IBM Plex Sans' => 'IBM Plex Sans (Professional)',
            'DM Sans' => 'DM Sans (Minimalist)',
            'Space Grotesk' => 'Space Grotesk (Techy)',
            'Lora' => 'Lora (Serif, Classic)',
            'JetBrains Mono' => 'JetBrains Mono (Monospace)',
            'Nunito' => 'Nunito (Rounded)',
            'Rubik' => 'Rubik (Bold)',
            'Work Sans' => 'Work Sans (Clean)',
            'Manrope' => 'Manrope (Modern)',
        ];
    }

    public function applyToCompany(int $companyId): void
    {
        $theme = CompanyTheme::where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if ($theme) {
            $this->generateCssFile($companyId, $theme);
            Cache::forget("company_theme_{$companyId}");
        }
    }

    public function getCssForCompany(int $companyId): string
    {
        return Cache::remember("company_theme_css_{$companyId}", 3600, function () use ($companyId) {
            $theme = CompanyTheme::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();

            if (!$theme) {
                return '';
            }

            return $this->generateCss($theme->toArray());
        });
    }

    protected function createDefaultTheme(int $companyId): CompanyTheme
    {
        return CompanyTheme::create([
            'company_id' => $companyId,
            'name' => 'Default',
            'primary_color' => '#4f46e5',
            'secondary_color' => '#7c3aed',
            'accent_color' => '#2dd4bf',
            'background_color' => '#f8fafc',
            'text_color' => '#1e293b',
            'font_family' => 'Inter',
            'border_radius' => 12,
            'button_style' => 'rounded',
            'sidebar_style' => 'default',
            'is_active' => true,
        ]);
    }

    protected function generateCssFile(int $companyId, CompanyTheme $theme): void
    {
        $css = $this->generateCss($theme->toArray());
        $path = public_path("themes/company-{$companyId}.css");
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        File::put($path, $css);
    }

    protected function deleteCssFile(int $companyId): void
    {
        $path = public_path("themes/company-{$companyId}.css");
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    protected function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6) {
            return '79, 70, 229';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }
}

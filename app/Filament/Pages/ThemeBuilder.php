<?php

namespace App\Filament\Pages;

use BackedEnum;
use App\Models\CompanyTheme;
use App\Services\ThemeBuilderService;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ThemeBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Theme Builder';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 1114;

    protected static ?string $slug = 'theme-builder';

    protected static string $view = 'filament.pages.theme-builder';

    public ?array $themeConfig = [];

    public array $presets = [];
    public array $availableFonts = [];
    public string $previewImage = '';

    public static function getNavigationGroup(): ?string
    {
        return 'Platform';
    }

    public function mount(): void
    {
        $service = app(ThemeBuilderService::class);
        $companyId = auth()->user()->company_id;

        $theme = $service->getTheme($companyId);
        $this->themeConfig = $theme;
        $this->presets = $service->getPresets();
        $this->availableFonts = $service->getAvailableFonts();
        $this->generatePreview();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Warna')
                    ->columns(3)
                    ->schema([
                        ColorPicker::make('themeConfig.primary_color')
                            ->label('Primary Color')
                            ->required(),
                        ColorPicker::make('themeConfig.secondary_color')
                            ->label('Secondary Color')
                            ->required(),
                        ColorPicker::make('themeConfig.accent_color')
                            ->label('Accent Color')
                            ->required(),
                        ColorPicker::make('themeConfig.background_color')
                            ->label('Background Color')
                            ->required(),
                        ColorPicker::make('themeConfig.text_color')
                            ->label('Text Color')
                            ->required(),
                    ]),
                Section::make('Tipografi & Layout')
                    ->columns(3)
                    ->schema([
                        Select::make('themeConfig.font_family')
                            ->label('Font Family')
                            ->options($this->availableFonts)
                            ->searchable(),
                        TextInput::make('themeConfig.border_radius')
                            ->label('Border Radius (px)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(32),
                        Select::make('themeConfig.button_style')
                            ->label('Button Style')
                            ->options([
                                'rounded' => 'Rounded',
                                'pill' => 'Pill',
                                'square' => 'Square',
                            ]),
                        Select::make('themeConfig.sidebar_style')
                            ->label('Sidebar Style')
                            ->options([
                                'default' => 'Default',
                                'compact' => 'Compact',
                                'colorful' => 'Colorful',
                            ]),
                    ]),
                Section::make('Dark Mode')
                    ->columns(3)
                    ->schema([
                        ColorPicker::make('themeConfig.dark_mode_colors.primary_color')
                            ->label('Dark Primary'),
                        ColorPicker::make('themeConfig.dark_mode_colors.background_color')
                            ->label('Dark Background'),
                        ColorPicker::make('themeConfig.dark_mode_colors.text_color')
                            ->label('Dark Text'),
                    ]),
                Section::make('Logo & CSS Kustom')
                    ->columns(2)
                    ->schema([
                        TextInput::make('themeConfig.logo_path')
                            ->label('URL Logo')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('themeConfig.favicon_path')
                            ->label('URL Favicon')
                            ->url()
                            ->maxLength(255),
                        Textarea::make('themeConfig.custom_css')
                            ->label('Custom CSS')
                            ->rows(6)
                            ->maxLength(5000),
                    ]),
            ]);
    }

    public function applyPreset(string $presetKey): void
    {
        if (isset($this->presets[$presetKey])) {
            $preset = $this->presets[$presetKey];
            $this->themeConfig = array_merge($this->themeConfig ?? [], $preset);
            $this->generatePreview();

            Notification::make()
                ->title("Preset '{$preset['name']}' diterapkan")
                ->success()
                ->send();
        }
    }

    public function saveTheme(): void
    {
        try {
            $service = app(ThemeBuilderService::class);
            $companyId = auth()->user()->company_id;

            $service->saveTheme($companyId, $this->themeConfig);

            Notification::make()
                ->title('Theme berhasil disimpan dan diterapkan')
                ->success()
                ->send();

            $this->generatePreview();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal menyimpan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetTheme(): void
    {
        try {
            $service = app(ThemeBuilderService::class);
            $companyId = auth()->user()->company_id;

            $service->resetTheme($companyId);
            $this->themeConfig = $service->getTheme($companyId);

            Notification::make()
                ->title('Theme di-reset ke default')
                ->success()
                ->send();

            $this->generatePreview();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal reset: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportTheme(): void
    {
        try {
            $service = app(ThemeBuilderService::class);
            $companyId = auth()->user()->company_id;

            $data = $service->exportTheme($companyId);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            Notification::make()
                ->title('Theme JSON')
                ->body('<pre class="text-xs whitespace-pre-wrap">' . e($json) . '</pre>')
                ->duration(30000)
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal export: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function generatePreview(): void
    {
        $service = app(ThemeBuilderService::class);
        $this->previewImage = $service->previewTheme($this->themeConfig ?? [
            'primary_color' => '#4f46e5',
            'secondary_color' => '#7c3aed',
            'background_color' => '#f8fafc',
            'text_color' => '#1e293b',
        ]);
    }

    public function updatedThemeConfig(): void
    {
        $this->generatePreview();
    }

    public function getTitle(): string
    {
        return 'Theme Builder';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

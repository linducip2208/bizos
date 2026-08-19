<?php

namespace App\Filament\Resources\Forecasts;

use App\Filament\Resources\Forecasts\Pages\CreateForecast;
use App\Filament\Resources\Forecasts\Pages\EditForecast;
use App\Filament\Resources\Forecasts\Pages\ListForecasts;
use App\Filament\Resources\Forecasts\Schemas\ForecastForm;
use App\Filament\Resources\Forecasts\Tables\ForecastsTable;
use App\Models\Forecast;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class ForecastResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Forecast::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'Finance & Accounting';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance & Accounting';
    }

    protected static ?string $label = 'Forecasts';

    protected static ?string $pluralLabel = 'Forecast';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownCircle;

    protected static ?int $navigationSort = 308;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ForecastForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ForecastsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForecasts::route('/'),
            'create' => CreateForecast::route('/create'),
            'edit' => EditForecast::route('/{record}/edit'),
        ];
    }
}

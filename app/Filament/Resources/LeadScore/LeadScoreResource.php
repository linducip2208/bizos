<?php

namespace App\Filament\Resources\LeadScore;

use App\Filament\Resources\LeadScore\Pages\ListLeadScores;
use App\Filament\Resources\LeadScore\Tables\LeadScoresTable;
use App\Models\LeadScore;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class LeadScoreResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = LeadScore::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Marketing';
    }

    protected static ?string $label = 'Skor Lead';

    protected static ?string $pluralLabel = 'Skor Lead';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 1303;

    protected static ?string $recordTitleAttribute = 'id';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return LeadScoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeadScores::route('/'),
        ];
    }
}
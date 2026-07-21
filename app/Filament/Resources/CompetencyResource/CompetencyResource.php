<?php

namespace App\Filament\Resources\CompetencyResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\CompetencyResource\Pages\ListCompetencies;
use App\Filament\Resources\CompetencyResource\Pages\CreateCompetency;
use App\Filament\Resources\CompetencyResource\Pages\EditCompetency;
use App\Filament\Resources\CompetencyResource\Schemas\CompetencyForm;
use App\Filament\Resources\CompetencyResource\Tables\CompetencyTable;
use App\Models\Competency;

class CompetencyResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Competency::class;
    public static function getNavigationGroup(): string|null { return 'HRM'; }
    protected static ?string $label = 'Kompetensi';
    protected static ?string $pluralLabel = 'Kompetensi';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    protected static ?int $navigationSort = 131;
    protected static ?string $recordTitleAttribute = 'name';
    public static function form(Schema $schema): Schema { return CompetencyForm::configure($schema); }
    public static function table(Table $table): Table { return CompetencyTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListCompetencies::route('/'),
        'create' => CreateCompetency::route('/create'),
        'edit' => EditCompetency::route('/{record}/edit'),
    ];}
}

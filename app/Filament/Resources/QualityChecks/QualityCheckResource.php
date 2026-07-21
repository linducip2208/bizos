<?php

namespace App\Filament\Resources\QualityChecks;

use App\Filament\Resources\QualityChecks\Pages\CreateQualityCheck;
use App\Filament\Resources\QualityChecks\Pages\EditQualityCheck;
use App\Filament\Resources\QualityChecks\Pages\ListQualityChecks;
use App\Filament\Resources\QualityChecks\Schemas\QualityCheckForm;
use App\Filament\Resources\QualityChecks\Tables\QualityCheckTable;
use App\Models\QualityCheck;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class QualityCheckResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = QualityCheck::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Procurement & Inventory';
    }

    protected static ?string $label = 'Pemeriksaan Kualitas';

    protected static ?string $pluralLabel = 'Pemeriksaan Kualitas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 113;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return QualityCheckForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QualityCheckTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQualityChecks::route('/'),
            'create' => CreateQualityCheck::route('/create'),
            'edit' => EditQualityCheck::route('/{record}/edit'),
        ];
    }
}
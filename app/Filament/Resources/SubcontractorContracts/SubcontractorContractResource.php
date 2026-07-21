<?php

namespace App\Filament\Resources\SubcontractorContracts;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\SubcontractorContracts\Pages\CreateSubcontractorContract;
use App\Filament\Resources\SubcontractorContracts\Pages\EditSubcontractorContract;
use App\Filament\Resources\SubcontractorContracts\Pages\ListSubcontractorContracts;
use App\Filament\Resources\SubcontractorContracts\Schemas\SubcontractorContractForm;
use App\Filament\Resources\SubcontractorContracts\Tables\SubcontractorContractTable;
use App\Models\SubcontractorContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubcontractorContractResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = SubcontractorContract::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏗️ Konstruksi';
    }

    protected static ?string $label = 'Kontrak Subkon';
    protected static ?string $pluralLabel = 'Kontrak Subkon';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 605;

    protected static ?string $recordTitleAttribute = 'contract_number';

    public static function form(Schema $schema): Schema
    {
        return SubcontractorContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubcontractorContractTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubcontractorContracts::route('/'),
            'create' => CreateSubcontractorContract::route('/create'),
            'edit' => EditSubcontractorContract::route('/{record}/edit'),
        ];
    }
}
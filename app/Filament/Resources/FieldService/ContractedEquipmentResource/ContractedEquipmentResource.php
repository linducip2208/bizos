<?php

namespace App\Filament\Resources\FieldService\ContractedEquipmentResource;

use App\Filament\Resources\FieldService\ContractedEquipmentResource\Pages\ListContractedEquipment;
use App\Filament\Resources\FieldService\ContractedEquipmentResource\Pages\CreateContractedEquipment;
use App\Filament\Resources\FieldService\ContractedEquipmentResource\Pages\EditContractedEquipment;
use App\Filament\Resources\FieldService\ContractedEquipmentResource\Schemas\ContractedEquipmentForm;
use App\Filament\Resources\FieldService\ContractedEquipmentResource\Tables\ContractedEquipmentTable;
use App\Models\ContractedEquipment;
use App\Filament\Concerns\HasPermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ContractedEquipmentResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ContractedEquipment::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Field Service';
    }

    protected static ?string $label = 'Peralatan Kontrak';

    protected static ?string $pluralLabel = 'Peralatan Kontrak';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ContractedEquipmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractedEquipmentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractedEquipment::route('/'),
            'create' => CreateContractedEquipment::route('/create'),
            'edit' => EditContractedEquipment::route('/{record}/edit'),
        ];
    }
}
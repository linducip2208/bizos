<?php

namespace App\Filament\Resources\FieldService\ServiceContractResource;

use App\Filament\Resources\FieldService\ServiceContractResource\Pages\ListServiceContracts;
use App\Filament\Resources\FieldService\ServiceContractResource\Pages\CreateServiceContract;
use App\Filament\Resources\FieldService\ServiceContractResource\Pages\EditServiceContract;
use App\Filament\Resources\FieldService\ServiceContractResource\Schemas\ServiceContractForm;
use App\Filament\Resources\FieldService\ServiceContractResource\Tables\ServiceContractTable;
use App\Models\ServiceContract;
use App\Filament\Concerns\HasPermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServiceContractResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ServiceContract::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Field Service';
    }

    protected static ?string $label = 'Kontrak Layanan';

    protected static ?string $pluralLabel = 'Kontrak Layanan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ServiceContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceContractTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceContracts::route('/'),
            'create' => CreateServiceContract::route('/create'),
            'edit' => EditServiceContract::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources\SalaryComponent;

use App\Filament\Resources\SalaryComponent\Pages\CreateSalaryComponent;
use App\Filament\Resources\SalaryComponent\Pages\EditSalaryComponent;
use App\Filament\Resources\SalaryComponent\Pages\ListSalaryComponents;
use App\Filament\Resources\SalaryComponent\Schemas\SalaryComponentForm;
use App\Filament\Resources\SalaryComponent\Tables\SalaryComponentsTable;
use App\Models\SalaryComponent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class SalaryComponentResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = SalaryComponent::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'salary-components';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💰 Payroll';
    }

    protected static ?string $label = 'Komponen Gaji';

    protected static ?string $pluralLabel = 'Komponen Gaji';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 201;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SalaryComponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalaryComponentsTable::configure($table);
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
            'index' => ListSalaryComponents::route('/'),
            'create' => CreateSalaryComponent::route('/create'),
            'edit' => EditSalaryComponent::route('/{record}/edit'),
        ];
    }
}
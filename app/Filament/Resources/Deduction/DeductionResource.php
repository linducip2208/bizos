<?php

namespace App\Filament\Resources\Deduction;

use App\Filament\Resources\Deduction\Pages\CreateDeduction;
use App\Filament\Resources\Deduction\Pages\EditDeduction;
use App\Filament\Resources\Deduction\Pages\ListDeductions;
use App\Filament\Resources\Deduction\Schemas\DeductionForm;
use App\Filament\Resources\Deduction\Tables\DeductionsTable;
use App\Models\Deduction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class DeductionResource extends Resource
{
    use HasPermissionAccess;

    // Gunakan SalaryComponent dengan component_category = 'deduction'.
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Deduction::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'deductions';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💰 Payroll';
    }

    protected static ?string $label = 'Potongan';

    protected static ?string $pluralLabel = 'Potongan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMinusCircle;

    protected static ?int $navigationSort = 211;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DeductionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeductionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeductions::route('/'),
            'create' => CreateDeduction::route('/create'),
            'edit' => EditDeduction::route('/{record}/edit'),
        ];
    }
}

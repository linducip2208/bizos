<?php

namespace App\Filament\Resources\Investments;

use App\Filament\Resources\Investments\Pages\CreateInvestment;
use App\Filament\Resources\Investments\Pages\EditInvestment;
use App\Filament\Resources\Investments\Pages\ListInvestments;
use App\Filament\Resources\Investments\Schemas\InvestmentForm;
use App\Filament\Resources\Investments\Tables\InvestmentsTable;
use App\Filament\Concerns\HasPermissionAccess;
use App\Models\Investment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Panel;

class InvestmentResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Investment::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'investments';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'Investasi';

    protected static ?string $pluralLabel = 'Investasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static ?int $navigationSort = 1701;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return InvestmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvestmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvestments::route('/'),
            'create' => CreateInvestment::route('/create'),
            'edit' => EditInvestment::route('/{record}/edit'),
        ];
    }
}
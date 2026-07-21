<?php

namespace App\Filament\Resources\ProgressBillings;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ProgressBillings\Pages\CreateProgressBilling;
use App\Filament\Resources\ProgressBillings\Pages\EditProgressBilling;
use App\Filament\Resources\ProgressBillings\Pages\ListProgressBillings;
use App\Filament\Resources\ProgressBillings\Schemas\ProgressBillingForm;
use App\Filament\Resources\ProgressBillings\Tables\ProgressBillingTable;
use App\Models\ProgressBilling;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProgressBillingResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ProgressBilling::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Industry';
    }

    protected static ?string $label = 'Tagihan Progres';
    protected static ?string $pluralLabel = 'Tagihan Progres';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 602;

    protected static ?string $recordTitleAttribute = 'billing_number';

    public static function form(Schema $schema): Schema
    {
        return ProgressBillingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgressBillingTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProgressBillings::route('/'),
            'create' => CreateProgressBilling::route('/create'),
            'edit' => EditProgressBilling::route('/{record}/edit'),
        ];
    }
}
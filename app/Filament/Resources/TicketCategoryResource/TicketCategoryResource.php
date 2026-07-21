<?php

namespace App\Filament\Resources\TicketCategoryResource;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\TicketCategoryResource\Pages\CreateTicketCategory;
use App\Filament\Resources\TicketCategoryResource\Pages\EditTicketCategory;
use App\Filament\Resources\TicketCategoryResource\Pages\ListTicketCategories;
use App\Filament\Resources\TicketCategoryResource\Schemas\TicketCategoryForm;
use App\Filament\Resources\TicketCategoryResource\Tables\TicketCategoriesTable;
use App\Models\TicketCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TicketCategoryResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = TicketCategory::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Helpdesk';
    }

    protected static ?string $label = 'Kategori Tiket';

    protected static ?string $pluralLabel = 'Kategori Tiket';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TicketCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTicketCategories::route('/'),
            'create' => CreateTicketCategory::route('/create'),
            'edit' => EditTicketCategory::route('/{record}/edit'),
        ];
    }
}

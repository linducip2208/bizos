<?php

namespace App\Filament\Resources\TicketTagResource;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\TicketTagResource\Pages\CreateTicketTag;
use App\Filament\Resources\TicketTagResource\Pages\EditTicketTag;
use App\Filament\Resources\TicketTagResource\Pages\ListTicketTags;
use App\Filament\Resources\TicketTagResource\Schemas\TicketTagForm;
use App\Filament\Resources\TicketTagResource\Tables\TicketTagsTable;
use App\Models\TicketTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TicketTagResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = TicketTag::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Helpdesk';
    }

    protected static ?string $label = 'Label Tiket';

    protected static ?string $pluralLabel = 'Label Tiket';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TicketTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketTagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTicketTags::route('/'),
            'create' => CreateTicketTag::route('/create'),
            'edit' => EditTicketTag::route('/{record}/edit'),
        ];
    }
}

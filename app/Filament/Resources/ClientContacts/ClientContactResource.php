<?php

namespace App\Filament\Resources\ClientContacts;

use App\Filament\Resources\ClientContacts\Pages\CreateClientContact;
use App\Filament\Resources\ClientContacts\Pages\EditClientContact;
use App\Filament\Resources\ClientContacts\Pages\ListClientContacts;
use App\Filament\Resources\ClientContacts\Schemas\ClientContactForm;
use App\Filament\Resources\ClientContacts\Tables\ClientContactsTable;
use App\Models\ClientContact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class ClientContactResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = ClientContact::class;

    public static function getNavigationGroup(): string|null
    {
        return '📈 Sales & CRM';
    }

    protected static ?string $label = 'Kontak Klien';

    protected static ?string $pluralLabel = 'Kontak Klien';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?int $navigationSort = 410;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Schema $schema): Schema
    {
        return ClientContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientContactsTable::configure($table);
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
            'index' => ListClientContacts::route('/'),
            'create' => CreateClientContact::route('/create'),
            'edit' => EditClientContact::route('/{record}/edit'),
        ];
    }
}
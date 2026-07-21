<?php

namespace App\Filament\Resources\PosMembers;

use App\Filament\Resources\PosMembers\Pages\CreatePosMember;
use App\Filament\Resources\PosMembers\Pages\EditPosMember;
use App\Filament\Resources\PosMembers\Pages\ListPosMembers;
use App\Filament\Resources\PosMembers\Schemas\PosMemberForm;
use App\Filament\Resources\PosMembers\Tables\PosMemberTable;
use App\Models\PosMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PosMemberResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PosMember::class;

    public static function getNavigationGroup(): string|null
    {
        return 'POS';
    }

    protected static ?string $label = 'Member';

    protected static ?string $pluralLabel = 'Member';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?int $navigationSort = 603;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PosMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosMemberTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosMembers::route('/'),
            'create' => CreatePosMember::route('/create'),
            'edit' => EditPosMember::route('/{record}/edit'),
        ];
    }
}

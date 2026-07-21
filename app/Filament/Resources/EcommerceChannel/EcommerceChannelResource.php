<?php

namespace App\Filament\Resources\EcommerceChannel;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\EcommerceChannel\Pages\CreateEcommerceChannel;
use App\Filament\Resources\EcommerceChannel\Pages\EditEcommerceChannel;
use App\Filament\Resources\EcommerceChannel\Pages\ListEcommerceChannels;
use App\Filament\Resources\EcommerceChannel\Schemas\EcommerceChannelForm;
use App\Filament\Resources\EcommerceChannel\Tables\EcommerceChannelTable;
use App\Models\EcommerceChannel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EcommerceChannelResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = EcommerceChannel::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Industry';
    }

    protected static ?string $label = 'Channel';

    protected static ?string $pluralLabel = 'Channel E-Commerce';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'channel_name';

    public static function form(Schema $schema): Schema
    {
        return EcommerceChannelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EcommerceChannelTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEcommerceChannels::route('/'),
            'create' => CreateEcommerceChannel::route('/create'),
            'edit' => EditEcommerceChannel::route('/{record}/edit'),
        ];
    }
}
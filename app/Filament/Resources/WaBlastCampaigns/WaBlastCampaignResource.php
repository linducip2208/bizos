<?php

namespace App\Filament\Resources\WaBlastCampaigns;

use App\Filament\Resources\WaBlastCampaigns\Pages\CreateWaBlastCampaign;
use App\Filament\Resources\WaBlastCampaigns\Pages\EditWaBlastCampaign;
use App\Filament\Resources\WaBlastCampaigns\Pages\ListWaBlastCampaigns;
use App\Filament\Resources\WaBlastCampaigns\Schemas\WaBlastCampaignForm;
use App\Filament\Resources\WaBlastCampaigns\Tables\WaBlastCampaignsTable;
use App\Models\WaBlastCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class WaBlastCampaignResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = WaBlastCampaign::class;

    public static function getNavigationGroup(): string|null
    {
        return 'CRM';
    }

    protected static ?string $label = 'Kampanye Blast WA';

    protected static ?string $pluralLabel = 'Kampanye Blast WA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?int $navigationSort = 407;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WaBlastCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaBlastCampaignsTable::configure($table);
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
            'index' => ListWaBlastCampaigns::route('/'),
            'create' => CreateWaBlastCampaign::route('/create'),
            'edit' => EditWaBlastCampaign::route('/{record}/edit'),
        ];
    }
}

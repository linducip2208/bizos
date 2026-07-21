<?php

namespace App\Filament\Resources\EmailCampaign;

use App\Filament\Resources\EmailCampaign\Pages\CreateEmailCampaign;
use App\Filament\Resources\EmailCampaign\Pages\EditEmailCampaign;
use App\Filament\Resources\EmailCampaign\Pages\ListEmailCampaigns;
use App\Filament\Resources\EmailCampaign\Schemas\EmailCampaignForm;
use App\Filament\Resources\EmailCampaign\Tables\EmailCampaignsTable;
use App\Models\EmailCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class EmailCampaignResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = EmailCampaign::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Sales & CRM';
    }

    protected static ?string $label = 'Kampanye Email';

    protected static ?string $pluralLabel = 'Kampanye Email';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 1301;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmailCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailCampaignsTable::configure($table);
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
            'index' => ListEmailCampaigns::route('/'),
            'create' => CreateEmailCampaign::route('/create'),
            'edit' => EditEmailCampaign::route('/{record}/edit'),
        ];
    }
}
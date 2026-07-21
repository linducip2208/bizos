<?php

namespace App\Filament\Resources\SmsGateway;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\SmsGateway\Pages\ListSmsGateways;
use App\Filament\Resources\SmsGateway\Pages\CreateSmsGateway;
use App\Filament\Resources\SmsGateway\Pages\EditSmsGateway;
use App\Filament\Resources\SmsGateway\Schemas\SmsGatewayForm;
use App\Filament\Resources\SmsGateway\Tables\SmsGatewayTable;
use App\Models\SmsGateway;

class SmsGatewayResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = SmsGateway::class;
    public static function getNavigationGroup(): string|null { return '🔗 Integrations'; }
    protected static ?string $label = 'SMS Gateway';
    protected static ?string $pluralLabel = 'SMS Gateway';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'name';
    public static function form(Schema $schema): Schema { return SmsGatewayForm::configure($schema); }
    public static function table(Table $table): Table { return SmsGatewayTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListSmsGateways::route('/'),
        'create' => CreateSmsGateway::route('/create'),
        'edit' => EditSmsGateway::route('/{record}/edit'),
    ];}
}
<?php

namespace App\Filament\Resources\Rfqs;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Rfqs\Pages\CreateRfq;
use App\Filament\Resources\Rfqs\Pages\EditRfq;
use App\Filament\Resources\Rfqs\Pages\ListRfqs;
use App\Filament\Resources\Rfqs\Schemas\RfqForm;
use App\Filament\Resources\Rfqs\Tables\RfqTable;
use App\Models\Rfq;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RfqResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Rfq::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::PROCUREMENT->value;
    }

    protected static ?string $label = 'RFQs';

    protected static ?string $pluralLabel = 'RFQs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 110;

    protected static ?string $recordTitleAttribute = 'rfq_number';

    public static function form(Schema $schema): Schema
    {
        return RfqForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RfqTable::configure($table);
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
            'index' => ListRfqs::route('/'),
            'create' => CreateRfq::route('/create'),
            'edit' => EditRfq::route('/{record}/edit'),
        ];
    }
}

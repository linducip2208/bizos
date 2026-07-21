<?php

namespace App\Filament\Resources\BpjsClaims;

use App\Filament\Resources\BpjsClaims\Pages\CreateBpjsClaim;
use App\Filament\Resources\BpjsClaims\Pages\EditBpjsClaim;
use App\Filament\Resources\BpjsClaims\Pages\ListBpjsClaims;
use App\Filament\Resources\BpjsClaims\Schemas\BpjsClaimForm;
use App\Filament\Resources\BpjsClaims\Tables\BpjsClaimTable;
use App\Models\BpjsClaim;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Concerns\HasPermissionAccess;

class BpjsClaimResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BpjsClaim::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Kesehatan';
    }

    protected static ?string $label = 'Klaim BPJS';

    protected static ?string $pluralLabel = 'Klaim BPJS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 1005;

    protected static ?string $recordTitleAttribute = 'claim_number';

    public static function form(Schema $schema): Schema
    {
        return BpjsClaimForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BpjsClaimTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBpjsClaims::route('/'),
            'create' => CreateBpjsClaim::route('/create'),
            'edit' => EditBpjsClaim::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
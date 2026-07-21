<?php

namespace App\Filament\Resources\SignatureProviders;

use App\Filament\Resources\SignatureProviders\Pages\CreateSignatureProvider;
use App\Filament\Resources\SignatureProviders\Pages\EditSignatureProvider;
use App\Filament\Resources\SignatureProviders\Pages\ListSignatureProviders;
use App\Filament\Resources\SignatureProviders\Schemas\SignatureProviderForm;
use App\Filament\Resources\SignatureProviders\Tables\SignatureProviderTable;
use App\Models\SignatureProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SignatureProviderResource extends Resource
{
    protected static ?string $model = SignatureProvider::class;

    protected static ?string $label = 'Provider Tanda Tangan';

    protected static ?string $pluralLabel = 'Provider Tanda Tangan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string|null
    {
        return '?? Integrasi';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSignatureProviders::route('/'),
            'create' => CreateSignatureProvider::route('/create'),
            'edit' => EditSignatureProvider::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return SignatureProviderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SignatureProviderTable::configure($table);
    }
}
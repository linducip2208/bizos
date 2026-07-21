<?php

namespace App\Filament\Resources\ProductBlockchainEvent;

use App\Filament\Resources\ProductBlockchainEvent\Pages\ListProductBlockchainEvents;
use App\Filament\Resources\ProductBlockchainEvent\Pages\ViewProductBlockchainEvent;
use App\Models\ProductBlockchainEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use App\Filament\Concerns\HasPermissionAccess;

class ProductBlockchainEventResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ProductBlockchainEvent::class;

    protected static ?string $label = 'Event Supply Chain';

    protected static ?string $pluralLabel = 'Event Supply Chain';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string|null
    {
        return '?? Extras';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.code')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('event_type')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'manufactured' => 'Diproduksi',
                        'qc_passed' => 'QC Lulus',
                        'shipped' => 'Dikirim',
                        'received' => 'Diterima',
                        'sold' => 'Terjual',
                        'returned' => 'Retur',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'manufactured',
                        'success' => 'qc_passed',
                        'warning' => 'shipped',
                        'primary' => 'received',
                        'danger' => fn($state) => $state === 'sold',
                        'gray' => 'returned',
                    ]),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),
                TextColumn::make('actor_name')
                    ->label('Oleh'),
                TextColumn::make('block_number')
                    ->label('Block #')
                    ->sortable(),
                TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductBlockchainEvents::route('/'),
            'view' => ViewProductBlockchainEvent::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
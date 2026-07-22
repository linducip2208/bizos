<?php

namespace App\Filament\Resources\BlockchainTransaction;

use App\Filament\Resources\BlockchainTransaction\Pages\ListBlockchainTransactions;
use App\Filament\Resources\BlockchainTransaction\Pages\ViewBlockchainTransaction;
use App\Models\BlockchainTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use App\Filament\Concerns\HasPermissionAccess;

class BlockchainTransactionResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
{
    use HasPermissionAccess;

    protected static ?string $model = BlockchainTransaction::class;

    protected static ?string $label = 'Transaksi Blockchain';

    protected static ?string $pluralLabel = 'Transaksi Blockchain';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|null
    {
        return '🔷 Blockchain';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_hash')
                    ->label('Transaction Hash')
                    ->copyable()
                    ->searchable()
                    ->limit(20),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'document_notarization' => 'Notarisasi Dokumen',
                        'certificate_issuance' => 'Sertifikat',
                        'smart_contract' => 'Smart Contract',
                        'supply_chain_event' => 'Supply Chain',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'document_notarization',
                        'success' => 'certificate_issuance',
                        'warning' => 'smart_contract',
                        'info' => 'supply_chain_event',
                    ]),
                TextColumn::make('file_name')
                    ->label('Dokumen')
                    ->searchable(),
                TextColumn::make('block.block_number')
                    ->label('Block #')
                    ->sortable(),
                TextColumn::make('timestamped_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('timestamped_at', 'desc')
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
            'index' => ListBlockchainTransactions::route('/'),
            'view' => ViewBlockchainTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
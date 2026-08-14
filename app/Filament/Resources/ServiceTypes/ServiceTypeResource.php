<?php

namespace App\Filament\Resources\ServiceTypes;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ServiceTypes\Pages\CreateServiceType;
use App\Filament\Resources\ServiceTypes\Pages\EditServiceType;
use App\Filament\Resources\ServiceTypes\Pages\ListServiceTypes;
use App\Models\ServiceType;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceTypeResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ServiceType::class;

    public static function getNavigationGroup(): string|null
    {
        return '🛒 POS & Retail';
    }

    protected static ?string $label = 'Tipe Layanan';

    protected static ?string $pluralLabel = 'Tipe Layanan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 602;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tipe Layanan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Layanan')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('short_code')
                            ->label('Kode Singkat')
                            ->maxLength(20)
                            ->nullable(),
                        Select::make('type')
                            ->label('Tipe')
                            ->required()
                            ->default('dine_in')
                            ->options([
                                'dine_in' => 'Makan di Tempat (Dine-in)',
                                'takeaway' => 'Bawa Pulang (Takeaway)',
                                'delivery' => 'Antar (Delivery)',
                                'other' => 'Lainnya',
                            ]),
                        TextInput::make('pack_price')
                            ->label('Biaya Kemasan')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable()
                            ->helperText('Biaya packing khusus delivery. Kosongkan jika tidak ada.'),
                        Select::make('pack_charge_type')
                            ->label('Tipe Biaya Kemasan')
                            ->nullable()
                            ->options([
                                'percentage' => 'Persentase (%)',
                                'fixed' => 'Nominal Tetap',
                            ]),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('Nama Layanan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dine_in' => 'Dine-in',
                        'takeaway' => 'Takeaway',
                        'delivery' => 'Delivery',
                        'other' => 'Lainnya',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'dine_in' => 'info',
                        'takeaway' => 'warning',
                        'delivery' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('pack_price')
                    ->label('Biaya Kemasan')
                    ->sortable()
                    ->state(fn (ServiceType $record): string => self::formatPackCharge($record)),
                TextColumn::make('is_active')
                    ->label('Aktif')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function formatPackCharge(ServiceType $record): string
    {
        if (!$record->pack_price || (float) $record->pack_price <= 0) {
            return '-';
        }

        $amount = (float) $record->pack_price;

        return $record->pack_charge_type === 'percentage'
            ? number_format($amount, 0, ',', '.') . '%'
            : 'Rp ' . number_format($amount, 0, ',', '.');
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
            'index' => ListServiceTypes::route('/'),
            'create' => CreateServiceType::route('/create'),
            'edit' => EditServiceType::route('/{record}/edit'),
        ];
    }
}

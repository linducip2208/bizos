<?php

namespace App\Filament\Resources\WarrantyClaims;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\WarrantyClaims\Pages\CreateWarrantyClaim;
use App\Filament\Resources\WarrantyClaims\Pages\EditWarrantyClaim;
use App\Filament\Resources\WarrantyClaims\Pages\ListWarrantyClaims;
use App\Models\WarrantyClaim;
use App\Models\WarrantyRegistration;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WarrantyClaimResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = WarrantyClaim::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::COLLABORATION->value;
    }

    protected static ?string $label = 'Warranty Claims';

    protected static ?string $pluralLabel = 'Warranty Claims';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'claim_number';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Klaim')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('warranty_registration_id')
                            ->label('Registrasi Garansi')
                            ->options(fn () => WarrantyRegistration::query()
                                ->with(['product', 'serialNumber'])
                                ->orderByDesc('id')
                                ->limit(2000)
                                ->get()
                                ->mapWithKeys(fn (WarrantyRegistration $r) => [
                                    $r->id => ($r->product?->name ?? 'Produk #' . $r->product_id)
                                        . ($r->serialNumber ? ' — SN: ' . $r->serialNumber->serial_number : '')
                                        . ' (' . ($r->end_date?->format('d/m/Y') ?? '-') . ')',
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('claim_number')
                            ->label('Nomor Klaim')
                            ->required()
                            ->maxLength(50)
                            ->default(fn () => app(\App\Services\WarrantyService::class)->generateClaimNumber())
                            ->disabled(fn (?WarrantyClaim $record) => $record !== null),
                        TextInput::make('claim_date')
                            ->label('Tanggal Klaim')
                            ->type('date')
                            ->required()
                            ->default(now()->toDateString()),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('submitted')
                            ->options(self::statusOptions()),
                        Select::make('resolution_type')
                            ->label('Tipe Penyelesaian')
                            ->nullable()
                            ->options([
                                'repair' => 'Perbaikan',
                                'replace' => 'Penggantian',
                                'refund' => 'Pengembalian Dana',
                            ]),
                        TextInput::make('cost')
                            ->label('Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        Textarea::make('issue_description')
                            ->label('Deskripsi Masalah')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('diagnosis')
                            ->label('Diagnosa')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('resolution')
                            ->label('Penyelesaian')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('claim_number')
                    ->label('Nomor Klaim')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('registration.product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'in_progress' => 'info',
                        'resolved' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('issue_description')
                    ->label('Masalah')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (WarrantyClaim $record): ?string => $record->issue_description),
                TextColumn::make('resolution_type')
                    ->label('Penyelesaian')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'repair' => 'Perbaikan',
                        'replace' => 'Penggantian',
                        'refund' => 'Refund',
                        default => '-',
                    }),
                TextColumn::make('claim_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('claim_date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function statusOptions(): array
    {
        return [
            'submitted' => 'Diajukan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'in_progress' => 'Diproses',
            'resolved' => 'Selesai',
        ];
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
            'index' => ListWarrantyClaims::route('/'),
            'create' => CreateWarrantyClaim::route('/create'),
            'edit' => EditWarrantyClaim::route('/{record}/edit'),
        ];
    }
}

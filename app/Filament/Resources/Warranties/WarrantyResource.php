<?php

namespace App\Filament\Resources\Warranties;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Warranties\Pages\CreateWarranty;
use App\Filament\Resources\Warranties\Pages\EditWarranty;
use App\Filament\Resources\Warranties\Pages\ListWarranties;
use App\Models\Warranty;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WarrantyResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Warranty::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Inventory & Warehouse';
    }

    protected static ?string $label = 'Warranties';

    protected static ?string $pluralLabel = 'Warranties';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 610;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Garansi')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Garansi')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Garansi Resmi 1 Tahun'),
                        TextInput::make('duration_value')
                            ->label('Durasi')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        Select::make('duration_type')
                            ->label('Satuan Durasi')
                            ->required()
                            ->default('months')
                            ->options([
                                'days' => 'Hari',
                                'months' => 'Bulan',
                                'years' => 'Tahun',
                            ]),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('terms')
                            ->label('Syarat & Ketentuan')
                            ->rows(4)
                            ->columnSpanFull(),
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
                    ->label('Nama Garansi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('duration_label')
                    ->label('Durasi')
                    ->state(fn (Warranty $record): string => $record->getDurationLabel())
                    ->sortable(false),
                TextColumn::make('is_active')
                    ->label('Aktif')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->defaultSort('name', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListWarranties::route('/'),
            'create' => CreateWarranty::route('/create'),
            'edit' => EditWarranty::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ResTables;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ResTables\Pages\CreateResTable;
use App\Filament\Resources\ResTables\Pages\EditResTable;
use App\Filament\Resources\ResTables\Pages\ListResTables;
use App\Models\ResTable;
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

class ResTableResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ResTable::class;

    public static function getNavigationGroup(): string|null
    {
        return '🛒 POS & Retail';
    }

    protected static ?string $label = 'Meja';

    protected static ?string $pluralLabel = 'Meja';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 604;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Meja')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('name')
                            ->label('Nama Meja')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Contoh: Meja 1, Meja VIP A'),
                        TextInput::make('table_number')
                            ->label('Nomor Meja')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('capacity')
                            ->label('Kapasitas (kursi)')
                            ->numeric()
                            ->default(2)
                            ->required(),
                        TextInput::make('section')
                            ->label('Zona / Area')
                            ->maxLength(100)
                            ->nullable()
                            ->helperText('Contoh: Indoor, Outdoor, VIP'),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('available')
                            ->options([
                                'available' => 'Tersedia',
                                'occupied' => 'Terisi',
                                'reserved' => 'Dipesan',
                            ]),
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
                TextColumn::make('name')
                    ->label('Meja')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn (int $state): string => $state . ' kursi'),
                TextColumn::make('section')
                    ->label('Zona')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'reserved' => 'Dipesan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('is_active')
                    ->label('Aktif')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('table_number', 'asc')
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
            'index' => ListResTables::route('/'),
            'create' => CreateResTable::route('/create'),
            'edit' => EditResTable::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $permissionOptions = Permission::all()
            ->groupBy('group')
            ->map(fn ($perms, $group) => $perms->pluck('name', 'id')->toArray())
            ->toArray();

        return $schema
            ->components([
                Section::make('Informasi Role')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_system')
                            ->label('System Role')
                            ->disabled()
                            ->default(false),
                    ]),
                Section::make('Hak Akses')
                    ->description('Pilih menu yang bisa diakses role ini')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('')
                            ->relationship('permissions', 'name')
                            ->options(
                                Permission::all()
                                    ->groupBy('group')
                                    ->mapWithKeys(fn ($perms, $group) => [$group => $perms->pluck('name', 'id')->toArray()])
                                    ->toArray()
                            )
                            ->columns(3)
                            ->columnSpanFull()
                            ->searchable()
                            ->bulkToggleable(),
                    ]),
            ]);
    }
}

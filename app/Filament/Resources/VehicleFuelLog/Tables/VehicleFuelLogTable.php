<?php

namespace App\Filament\Resources\VehicleFuelLog\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleFuelLogTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('date', 'desc')->columns([
            TextColumn::make('vehicle.plate_number')->label('Kendaraan')->searchable()->sortable(),
            TextColumn::make('date')->label('Tanggal')->date('d M Y')->sortable(),
            TextColumn::make('odometer')->label('Odometer')->numeric()->sortable(),
            TextColumn::make('liters')->label('Liter')->numeric(),
            TextColumn::make('cost')->label('Biaya')->money('IDR')->sortable(),
            TextColumn::make('fuel_efficiency')->label('Efisiensi (km/L)')->numeric()->sortable(),
            TextColumn::make('station')->label('SPBU'),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
<?php

namespace App\Filament\Resources\VehicleMaintenanceLogResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleMaintenanceLogTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('date', 'desc')->columns([
            TextColumn::make('vehicle.plate_number')->label('Kendaraan')->searchable()->sortable(),
            TextColumn::make('type')->label('Tipe')->badge()->color(fn($s)=>match($s){'routine'=>'primary','repair'=>'warning','inspection'=>'success',default=>'gray'})->formatStateUsing(fn($s)=>match($s){'routine'=>'Rutin','repair'=>'Perbaikan','inspection'=>'Inspeksi',default=>$s}),
            TextColumn::make('description')->label('Deskripsi')->searchable()->limit(50),
            TextColumn::make('date')->label('Tanggal')->date('d M Y')->sortable(),
            TextColumn::make('cost')->label('Biaya')->money('IDR')->sortable(),
            TextColumn::make('vendor')->label('Vendor'),
            TextColumn::make('next_due_date')->label('Perawatan Berikutnya')->date('d M Y'),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}

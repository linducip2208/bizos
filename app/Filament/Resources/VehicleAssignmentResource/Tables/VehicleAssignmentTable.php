<?php

namespace App\Filament\Resources\VehicleAssignmentResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleAssignmentTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('assigned_at', 'desc')->columns([
            TextColumn::make('vehicle.plate_number')->label('Kendaraan')->searchable()->sortable(),
            TextColumn::make('employee.first_name')->label('Karyawan')->searchable()->sortable(),
            TextColumn::make('assigned_at')->label('Ditugaskan')->dateTime('d M Y H:i')->sortable(),
            TextColumn::make('returned_at')->label('Kembali')->dateTime('d M Y H:i'),
            TextColumn::make('odometer_start')->label('Odo Awal')->numeric(),
            TextColumn::make('odometer_end')->label('Odo Akhir')->numeric(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}

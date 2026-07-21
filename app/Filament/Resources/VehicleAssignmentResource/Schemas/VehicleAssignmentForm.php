<?php

namespace App\Filament\Resources\VehicleAssignmentResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VehicleAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Penugasan Kendaraan')->columns(2)->schema([
                Select::make('vehicle_id')->label('Kendaraan')->relationship('vehicle', 'plate_number')->searchable()->preload()->required(),
                Select::make('employee_id')->label('Karyawan')->relationship('employee', 'first_name')->searchable()->preload()->required(),
                DateTimePicker::make('assigned_at')->label('Waktu Penugasan')->default(now())->required(),
                DateTimePicker::make('returned_at')->label('Waktu Kembali'),
                TextInput::make('odometer_start')->label('Odometer Awal')->numeric(),
                TextInput::make('odometer_end')->label('Odometer Akhir')->numeric(),
                Select::make('assigned_by')->label('Ditugaskan Oleh')->relationship('assignedBy', 'name')->searchable()->preload(),
                Textarea::make('notes')->label('Catatan')->columnSpanFull(),
            ]),
        ]);
    }
}

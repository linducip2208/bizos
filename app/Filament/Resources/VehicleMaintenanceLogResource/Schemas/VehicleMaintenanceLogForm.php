<?php

namespace App\Filament\Resources\VehicleMaintenanceLogResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class VehicleMaintenanceLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Perawatan Kendaraan')->columns(2)->schema([
                Select::make('vehicle_id')->label('Kendaraan')->relationship('vehicle', 'plate_number')->searchable()->preload()->required(),
                Select::make('type')->label('Tipe')->options(['routine'=>'Rutin','repair'=>'Perbaikan','inspection'=>'Inspeksi'])->default('routine')->required(),
                TextInput::make('description')->label('Deskripsi')->required()->maxLength(255)->columnSpanFull(),
                TextInput::make('cost')->label('Biaya (Rp)')->numeric()->prefix('Rp')->default(0),
                TextInput::make('vendor')->label('Vendor/Bengkel')->maxLength(100),
                TextInput::make('odometer_at')->label('Odometer Saat Ini')->numeric()->default(0),
                TextInput::make('next_odometer_due')->label('Odometer Perawatan Berikutnya')->numeric(),
                DatePicker::make('date')->label('Tanggal')->default(now())->required(),
                DatePicker::make('next_due_date')->label('Tanggal Perawatan Berikutnya'),
                FileUpload::make('attachment')->label('Lampiran')->directory('maintenance-attachments'),
                Textarea::make('notes')->label('Catatan')->columnSpanFull(),
            ]),
        ]);
    }
}

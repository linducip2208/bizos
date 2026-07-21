<?php

namespace App\Filament\Resources\VehicleFuelLogResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class VehicleFuelLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Isi BBM')->columns(2)->schema([
                Select::make('vehicle_id')->label('Kendaraan')->relationship('vehicle', 'plate_number')->searchable()->preload()->required(),
                Select::make('driver_id')->label('Pengemudi')->relationship('driver', 'first_name')->searchable()->preload(),
                TextInput::make('odometer')->label('Odometer')->numeric()->required(),
                TextInput::make('liters')->label('Liter')->numeric()->required(),
                TextInput::make('cost')->label('Biaya (Rp)')->numeric()->prefix('Rp')->required(),
                Select::make('fuel_type')->label('Jenis BBM')->options(['gasoline'=>'Bensin','diesel'=>'Solar','electric'=>'Listrik'])->default('gasoline'),
                TextInput::make('station')->label('SPBU')->maxLength(100),
                DatePicker::make('date')->label('Tanggal')->default(now())->required(),
                FileUpload::make('receipt_photo')->label('Foto Struk')->image()->directory('fuel-receipts'),
                Textarea::make('notes')->label('Catatan')->columnSpanFull(),
            ]),
        ]);
    }
}

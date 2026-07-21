<?php

namespace App\Filament\Resources\Prescriptions\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrescriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Resep')
                    ->columns(3)
                    ->schema([
                        Select::make('medical_record_id')
                            ->label('Rekam Medis')
                            ->relationship('medicalRecord', 'visit_date')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->visit_date->format('d M Y')} — {$record->patient?->full_name}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('patient_id')
                            ->label('Pasien')
                            ->relationship('patient', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->patient_number} — {$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('doctor_id')
                            ->label('Dokter')
                            ->options(function () {
                                return Employee::where('is_doctor', true)
                                    ->get()
                                    ->mapWithKeys(fn ($e) => [$e->id => "{$e->first_name} {$e->last_name}"]);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('prescription_date')
                            ->label('Tanggal Resep')
                            ->required()
                            ->default(now()),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'dispensed' => 'Sudah Diserahkan',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('draft'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                Section::make('Daftar Obat')
                    ->schema([
                        Repeater::make('items')
                            ->label('Obat')
                            ->relationship('items')
                            ->columns(6)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Obat')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('dosage')
                                    ->label('Dosis')
                                    ->maxLength(100)
                                    ->placeholder('3x1, 2x1, dll'),
                                TextInput::make('frequency')
                                    ->label('Frekuensi')
                                    ->maxLength(100)
                                    ->placeholder('Sebelum makan, Sesudah makan'),
                                TextInput::make('duration_days')
                                    ->label('Hari')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('7'),
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
                                Textarea::make('instructions')
                                    ->label('Instruksi')
                                    ->rows(2)
                                    ->placeholder('Minum setelah makan, habiskan...'),
                            ])
                            ->addActionLabel('Tambah Obat')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => \App\Models\Product::find($state['product_id'] ?? 0)?->name ?? 'Obat Baru'),
                    ]),
            ]);
    }
}

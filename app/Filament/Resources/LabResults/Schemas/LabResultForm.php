<?php

namespace App\Filament\Resources\LabResults\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LabResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Hasil Lab')
                    ->columns(2)
                    ->schema([
                        Select::make('lab_order_id')
                            ->label('Order Lab')
                            ->relationship('labOrder', 'order_date')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->order_date->format('d M Y')} — {$record->patient?->full_name} — " . match ($record->lab_type) {
                                'hematology' => 'Hematologi',
                                'chemistry' => 'Kimia',
                                'microbiology' => 'Mikrobiologi',
                                'radiology' => 'Radiologi',
                                'urine' => 'Urinalisis',
                                default => $record->lab_type,
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('test_name')
                            ->label('Nama Tes')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Hemoglobin, Leukosit, GDP'),
                        TextInput::make('result_value')
                            ->label('Nilai Hasil')
                            ->maxLength(255)
                            ->placeholder('12.5'),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->maxLength(50)
                            ->placeholder('g/dL, mg/dL, mm/jam'),
                        TextInput::make('normal_range')
                            ->label('Nilai Normal')
                            ->maxLength(255)
                            ->placeholder('13.5-18.0'),
                        TextInput::make('performed_by')
                            ->label('Dilakukan Oleh')
                            ->maxLength(255),
                        DateTimePicker::make('performed_at')
                            ->label('Waktu Pemeriksaan'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
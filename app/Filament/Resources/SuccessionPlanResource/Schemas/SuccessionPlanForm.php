<?php

namespace App\Filament\Resources\SuccessionPlanResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Schema;

class SuccessionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rencana Suksesi')->columns(2)->schema([
                Select::make('company_id')->label('Perusahaan')->relationship('company','name')->searchable()->preload()->required(),
                Select::make('position_id')->label('Posisi Kritis')->relationship('position','name')->searchable()->preload()->required(),
                Select::make('current_incumbent_id')->label('Pejabat Saat Ini')->relationship('currentIncumbent','first_name')->searchable()->preload(),
                Select::make('successor_employee_id')->label('Kandidat Suksesor')->relationship('successor','first_name')->searchable()->preload(),
                Select::make('readiness')->label('Kesiapan')->options(['ready_now'=>'Siap Sekarang','1_year'=>'1 Tahun','2_years'=>'2 Tahun','3_plus_years'=>'3+ Tahun'])->default('2_years')->required(),
                Select::make('risk_level')->label('Tingkat Risiko')->options(['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'])->default('medium')->required(),
                Select::make('created_by')->label('Dibuat Oleh')->relationship('creator','name')->searchable()->preload(),
                Textarea::make('notes')->label('Catatan')->columnSpanFull(),
                KeyValue::make('development_plan')->label('Rencana Pengembangan')->keyLabel('Aktivitas')->valueLabel('Timeline')->columnSpanFull(),
            ]),
        ]);
    }
}

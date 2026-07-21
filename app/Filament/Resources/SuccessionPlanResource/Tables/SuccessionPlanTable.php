<?php

namespace App\Filament\Resources\SuccessionPlanResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuccessionPlanTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('position.name')->label('Posisi')->searchable()->sortable(),
            TextColumn::make('currentIncumbent.first_name')->label('Pejabat Saat Ini')->searchable(),
            TextColumn::make('successor.first_name')->label('Suksesor')->searchable(),
            TextColumn::make('readiness')->label('Kesiapan')->badge()->color(fn($s)=>match($s){'ready_now'=>'success','1_year'=>'primary','2_years'=>'warning','3_plus_years'=>'danger',default=>'gray'})->formatStateUsing(fn($s)=>match($s){'ready_now'=>'Siap Sekarang','1_year'=>'1 Tahun','2_years'=>'2 Tahun','3_plus_years'=>'3+ Tahun',default=>$s}),
            TextColumn::make('risk_level')->label('Risiko')->badge()->color(fn($s)=>match($s){'high'=>'danger','medium'=>'warning','low'=>'success',default=>'gray'})->formatStateUsing(fn($s)=>match($s){'high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah',default=>$s}),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}

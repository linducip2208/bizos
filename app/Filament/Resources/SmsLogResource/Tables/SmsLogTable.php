<?php

namespace App\Filament\Resources\SmsLogResource\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SmsLogTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('created_at','desc')->columns([
            TextColumn::make('gateway.name')->label('Gateway')->sortable(),
            TextColumn::make('recipient')->label('Penerima')->searchable()->sortable(),
            TextColumn::make('message')->label('Pesan')->limit(60)->searchable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn($s)=>match($s){'delivered'=>'success','sent'=>'primary','queued'=>'warning','failed'=>'danger',default=>'gray'})->formatStateUsing(fn($s)=>match($s){'queued'=>'Antrian','sent'=>'Terkirim','delivered'=>'Tersampai','failed'=>'Gagal',default=>$s}),
            TextColumn::make('cost')->label('Biaya')->money('IDR'),
            TextColumn::make('error_message')->label('Error')->limit(40),
            TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s')->sortable(),
        ]);
    }
}

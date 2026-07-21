<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Tugas')
                    ->columns(3)
                    ->schema([
                        Select::make('project_id')
                            ->label('Proyek')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('phase_id')
                            ->label('Fase Proyek')
                            ->relationship('phase', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('milestone_id')
                            ->label('Milestone')
                            ->relationship('milestone', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('parent_id')
                            ->label('Tugas Induk')
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('title')
                            ->label('Judul Tugas')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'backlog' => 'Backlog',
                                'todo' => 'To Do',
                                'in_progress' => 'Sedang Dikerjakan',
                                'review' => 'Review',
                                'done' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('todo')
                            ->required(),
                        Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'urgent' => 'Mendesak',
                            ])
                            ->default('medium')
                            ->required(),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'task' => 'Tugas',
                                'bug' => 'Bug',
                                'feature' => 'Fitur',
                                'improvement' => 'Peningkatan',
                                'research' => 'Riset',
                            ])
                            ->default('task')
                            ->required(),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('creator', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('estimated_hours')
                            ->label('Estimasi (Jam)')
                            ->numeric()
                            ->suffix('jam'),
                        TextInput::make('actual_hours')
                            ->label('Aktual (Jam)')
                            ->numeric()
                            ->suffix('jam'),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai'),
                        DatePicker::make('due_date')
                            ->label('Tenggat Waktu')
                            ->afterOrEqual('start_date'),
                        DateTimePicker::make('completed_at')
                            ->label('Selesai Pada'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
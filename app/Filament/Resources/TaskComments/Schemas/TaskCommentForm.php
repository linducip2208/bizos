<?php

namespace App\Filament\Resources\TaskComments\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TaskCommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Komentar Tugas')
                    ->columns(1)
                    ->schema([
                        Select::make('task_id')
                            ->label('Tugas')
                            ->relationship('task', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('comment')
                            ->label('Komentar')
                            ->required()
                            ->rows(5),
                    ]),
            ]);
    }
}

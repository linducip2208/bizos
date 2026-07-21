<?php

namespace App\Filament\Resources\TaskAttachments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskAttachmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lampiran Tugas')
                    ->columns(2)
                    ->schema([
                        Select::make('task_id')
                            ->label('Tugas')
                            ->relationship('task', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('file_name')
                            ->label('Nama File')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('file_path')
                            ->label('File')
                            ->required()
                            ->directory('task-attachments')
                            ->maxSize(10240)
                            ->columnSpanFull(),
                        TextInput::make('file_size')
                            ->label('Ukuran File (bytes)')
                            ->numeric()
                            ->suffix('B'),
                        TextInput::make('file_type')
                            ->label('Tipe File')
                            ->maxLength(100),
                        Select::make('uploaded_by')
                            ->label('Diunggah Oleh')
                            ->relationship('uploader', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\Workflows\Schemas;

use App\Services\WorkflowAutomationService;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Schema;

class WorkflowForm
{
    public static function configure(Schema $schema): Schema
    {
        $workflowService = app(WorkflowAutomationService::class);

        $triggerOptions = collect($workflowService->getAvailableTriggers())
            ->mapWithKeys(fn ($t) => [$t['event'] => "[{$t['category']}] {$t['label']}"])
            ->toArray();

        $actionOptions = collect($workflowService->getAvailableActions())
            ->mapWithKeys(fn ($a) => [$a['type'] => "[{$a['category']}] {$a['label']}"])
            ->toArray();

        return $schema
            ->components([
                Section::make('Informasi Workflow')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Workflow')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('cth: Notifikasi Lead Baru'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->nullable()
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),

                Section::make('Trigger')
                    ->description('Pilih event yang memicu workflow ini')
                    ->schema([
                        Select::make('trigger_event')
                            ->label('Trigger Event')
                            ->options($triggerOptions)
                            ->searchable()
                            ->required()
                            ->reactive(),
                    ]),

                Section::make('Kondisi Tambahan')
                    ->description('Tambahkan kondisi filter (opsional). Semua kondisi harus terpenuhi (AND).')
                    ->schema([
                        Repeater::make('trigger_conditions')
                            ->label('Kondisi')
                            ->schema([
                                TextInput::make('field')
                                    ->label('Field')
                                    ->required()
                                    ->placeholder('cth: amount'),
                                Select::make('operator')
                                    ->label('Operator')
                                    ->options([
                                        '=' => 'Sama dengan (=)',
                                        '!=' => 'Tidak sama (!=)',
                                        '>' => 'Lebih besar (>)',
                                        '<' => 'Lebih kecil (<)',
                                        '>=' => 'Lebih besar/sama (>=)',
                                        '<=' => 'Lebih kecil/sama (<=)',
                                        'contains' => 'Mengandung',
                                        'not_contains' => 'Tidak mengandung',
                                        'in' => 'Termasuk dalam',
                                        'not_in' => 'Tidak termasuk',
                                        'between' => 'Di antara',
                                    ])
                                    ->required(),
                                TextInput::make('value')
                                    ->label('Nilai')
                                    ->required()
                                    ->placeholder('cth: 10000000'),
                            ])
                            ->columns(3)
                            ->default([])
                            ->addActionLabel('Tambah Kondisi'),
                    ]),

                Section::make('Aksi')
                    ->description('Tentukan aksi yang dijalankan saat trigger dan kondisi terpenuhi')
                    ->schema([
                        Repeater::make('actions')
                            ->label('Daftar Aksi')
                            ->schema([
                                Select::make('type')
                                    ->label('Tipe Aksi')
                                    ->options($actionOptions)
                                    ->required()
                                    ->reactive(),
                                KeyValue::make('config')
                                    ->label('Konfigurasi')
                                    ->hint('Gunakan {{field}} untuk template dari konteks')
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->addActionLabel('Tambah Konfigurasi')
                                    ->helperText('Contoh: {"template_id": "1", "to": "{{phone}}", "message": "Halo {{name}}!"}'),
                            ])
                            ->columns(1)
                            ->required()
                            ->minItems(1)
                            ->addActionLabel('Tambah Aksi'),
                    ]),
            ]);
    }
}

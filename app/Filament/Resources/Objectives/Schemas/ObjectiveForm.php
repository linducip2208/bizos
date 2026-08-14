<?php

namespace App\Filament\Resources\Objectives\Schemas;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Objective;
use App\Models\KeyResult;
use App\Models\PerformanceCycle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Objective')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Objective')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('objective_type')
                            ->label('Tipe Objective')
                            ->options([
                                'company' => 'Perusahaan',
                                'department' => 'Departemen',
                                'team' => 'Tim',
                                'individual' => 'Individu',
                            ])
                            ->required()
                            ->live()
                            ->default('individual'),

                        Select::make('owner_type')
                            ->label('Tipe Pemilik')
                            ->options([
                                'App\\Models\\Department' => 'Departemen',
                                'App\\Models\\Employee' => 'Karyawan',
                            ])
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('owner_id', null)),

                        Select::make('owner_id')
                            ->label('Pemilik')
                            ->options(function ($get) {
                                $type = $get('owner_type');
                                if ($type === 'App\\Models\\Department') {
                                    return Department::orderBy('name')->pluck('name', 'id');
                                }
                                if ($type === 'App\\Models\\Employee') {
                                    return Employee::orderBy('first_name')
                                        ->get()
                                        ->mapWithKeys(fn ($e) => [
                                            $e->id => trim($e->first_name . ' ' . $e->last_name)
                                        ]);
                                }
                                return [];
                            })
                            ->searchable()
                            ->nullable(),

                        Select::make('parent_objective_id')
                            ->label('Objective Induk')
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->nullable()
                            ->columnSpanFull(),

                        Select::make('cycle_id')
                            ->label('Siklus Performa')
                            ->relationship('cycle', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->native(false),

                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->native(false),

                        Select::make('status')
                            ->label('Status')
                            ->options(Objective::statusOptions())
                            ->default('draft')
                            ->required(),

                        TextInput::make('weight')
                            ->label('Bobot')
                            ->numeric()
                            ->default(100)
                            ->minValue(0)
                            ->maxValue(1000),

                        Placeholder::make('progress_info')
                            ->label('Progress')
                            ->content(function ($record) {
                                if (!$record?->id) return new HtmlString('<span class="text-gray-400">Tersimpan setelah simpan</span>');
                                $color = $record->progress_percent >= 70 ? '#10b981' : ($record->progress_percent >= 40 ? '#f59e0b' : '#ef4444');
                                return new HtmlString(
                                    '<div class="w-full bg-gray-200 rounded-full h-4"><div class="h-4 rounded-full" style="width:' . $record->progress_percent . '%;background:' . $color . '"></div></div>' .
                                    '<span class="text-sm mt-1">' . $record->progress_percent . '%</span>'
                                );
                            }),
                    ]),

                Section::make('Key Results')
                    ->description('Tetapkan key results yang terukur untuk mencapai objective')
                    ->schema([
                        Repeater::make('keyResults')
                            ->relationship('keyResults')
                            ->label('Key Results')
                            ->addActionLabel('Tambah Key Result')
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Key Result')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Select::make('metric_type')
                                    ->label('Tipe Metrik')
                                    ->options(KeyResult::metricTypeOptions())
                                    ->required()
                                    ->default('number'),

                                TextInput::make('start_value')
                                    ->label('Nilai Awal')
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('target_value')
                                    ->label('Nilai Target')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('unit')
                                    ->label('Satuan')
                                    ->placeholder('% / Rp / unit')
                                    ->maxLength(100),

                                TextInput::make('weight')
                                    ->label('Bobot')
                                    ->numeric()
                                    ->default(100)
                                    ->minValue(0)
                                    ->maxValue(1000),

                                DatePicker::make('due_date')
                                    ->label('Tenggat')
                                    ->native(false),

                                Select::make('check_in_frequency')
                                    ->label('Frekuensi Check-in')
                                    ->options(KeyResult::frequencyOptions())
                                    ->default('monthly'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options(KeyResult::statusOptions())
                                    ->default('draft'),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['created_by'] = auth()->id();
                                return $data;
                            }),
                    ]),
            ]);
    }
}

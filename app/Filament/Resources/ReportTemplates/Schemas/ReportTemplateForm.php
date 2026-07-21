<?php

namespace App\Filament\Resources\ReportTemplates\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReportTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FormSection::make('Informasi Template')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Template')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique('report_templates', 'slug', ignoreRecord: true),
                        Select::make('category')
                            ->label('Kategori')
                            ->required()
                            ->default('custom')
                            ->options([
                                'sales' => 'Penjualan',
                                'finance' => 'Keuangan',
                                'hrm' => 'HRM',
                                'inventory' => 'Inventaris',
                                'custom' => 'Kustom',
                            ]),
                        Select::make('query_type')
                            ->label('Tipe Query')
                            ->required()
                            ->default('table')
                            ->options([
                                'table' => 'Tabel',
                                'chart' => 'Grafik',
                                'summary' => 'Ringkasan',
                            ]),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_public')
                            ->label('Publik')
                            ->helperText('Tampilkan ke semua user')
                            ->default(false),
                        Toggle::make('is_system')
                            ->label('Sistem')
                            ->helperText('Template bawaan sistem')
                            ->default(false)
                            ->visible(fn () => auth()->user()?->role?->slug === 'super-admin'),
                    ]),

                FormSection::make('Konfigurasi Query')
                    ->schema([
                        TextInput::make('query_config.table_name')
                            ->label('Nama Tabel Utama')
                            ->required()
                            ->helperText('Contoh: invoices, attendances')
                            ->columnSpanFull(),
                        TagsInput::make('query_config.select')
                            ->label('Kolom SELECT')
                            ->helperText('Kosongkan untuk SELECT *')
                            ->columnSpanFull()
                            ->splitKeys([',', 'Tab'])
                            ->separator(','),
                        Repeater::make('query_config.joins')
                            ->label('JOIN Tables')
                            ->schema([
                                TextInput::make('table')->label('Tabel Join')->required(),
                                TextInput::make('first')->label('Kolom Kiri (local)')->required(),
                                TextInput::make('operator')->label('Operator')->default('='),
                                TextInput::make('second')->label('Kolom Kanan (foreign)')->required(),
                                Select::make('type')
                                    ->label('Tipe JOIN')
                                    ->options([
                                        'inner' => 'INNER JOIN',
                                        'left' => 'LEFT JOIN',
                                        'right' => 'RIGHT JOIN',
                                    ])
                                    ->default('inner'),
                            ])
                            ->columns(5)
                            ->collapsible()
                            ->default([]),
                        Repeater::make('query_config.filters')
                            ->label('Filter (WHERE)')
                            ->schema([
                                TextInput::make('column')->label('Kolom')->required(),
                                Select::make('operator')
                                    ->label('Operator')
                                    ->options([
                                        '=' => '=',
                                        '!=' => '!=',
                                        '>' => '>',
                                        '<' => '<',
                                        '>=' => '>=',
                                        '<=' => '<=',
                                        'LIKE' => 'LIKE',
                                    ])
                                    ->default('='),
                                TextInput::make('value')->label('Nilai'),
                                Select::make('type')
                                    ->label('Tipe Filter')
                                    ->options([
                                        'where' => 'WHERE',
                                        'orWhere' => 'OR WHERE',
                                        'whereIn' => 'WHERE IN',
                                        'whereBetween' => 'WHERE BETWEEN',
                                        'whereNull' => 'WHERE NULL',
                                        'whereNotNull' => 'WHERE NOT NULL',
                                        'whereDate' => 'WHERE DATE',
                                    ])
                                    ->default('where'),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->default([]),
                        Repeater::make('query_config.group_by')
                            ->label('GROUP BY')
                            ->schema([
                                TextInput::make('column')->label('Kolom')->required(),
                            ])
                            ->simple(
                                TextInput::make('column')->label('Kolom')->required()
                            )
                            ->collapsible()
                            ->default([]),
                        Repeater::make('query_config.sort')
                            ->label('Sort (ORDER BY)')
                            ->schema([
                                TextInput::make('column')->label('Kolom')->required(),
                                Select::make('direction')
                                    ->label('Arah')
                                    ->options(['asc' => 'ASC', 'desc' => 'DESC'])
                                    ->default('asc'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->default([]),
                        TextInput::make('query_config.limit')
                            ->label('LIMIT')
                            ->numeric()
                            ->helperText('Kosongkan untuk tidak ada limit'),
                    ]),

                FormSection::make('Konfigurasi Grafik')
                    ->visible(fn ($get) => in_array($get('query_type'), ['chart', 'summary']))
                    ->schema([
                        Select::make('chart_config.type')
                            ->label('Tipe Grafik')
                            ->options([
                                'bar' => 'Bar Chart',
                                'line' => 'Line Chart',
                                'pie' => 'Pie Chart',
                                'doughnut' => 'Doughnut Chart',
                                'area' => 'Area Chart',
                            ])
                            ->default('bar'),
                        TextInput::make('chart_config.x_axis')
                            ->label('Sumbu X (Label)')
                            ->helperText('Nama kolom untuk label'),
                        TextInput::make('chart_config.y_axis')
                            ->label('Sumbu Y (Nilai)')
                            ->helperText('Nama kolom untuk nilai'),
                        TagsInput::make('chart_config.colors')
                            ->label('Warna Grafik')
                            ->helperText('Hex colors, pisahkan dengan koma')
                            ->splitKeys([',', 'Tab'])
                            ->separator(','),
                    ]),
            ]);
    }
}

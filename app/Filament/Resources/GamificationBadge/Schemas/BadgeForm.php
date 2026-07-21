<?php

namespace App\Filament\Resources\GamificationBadge\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BadgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Badge')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Badge')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Digunakan sebagai identifier unik.'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->rows(2)
                            ->maxLength(500),
                        Select::make('icon')
                            ->label('Ikon')
                            ->options([
                                'heroicon-o-star' => 'Bintang',
                                'heroicon-o-trophy' => 'Trofi',
                                'heroicon-o-fire' => 'Api',
                                'heroicon-o-bolt' => 'Petir',
                                'heroicon-o-heart' => 'Hati',
                                'heroicon-o-academic-cap' => 'Topi Akademik',
                                'heroicon-o-shield-check' => 'Shield',
                                'heroicon-o-hand-thumb-up' => 'Jempol',
                                'heroicon-o-sun' => 'Matahari',
                                'heroicon-o-moon' => 'Bulan',
                                'heroicon-o-calendar-days' => 'Kalender',
                                'heroicon-o-banknotes' => 'Uang',
                                'heroicon-o-chart-bar' => 'Chart',
                                'heroicon-o-rocket-launch' => 'Roket',
                            ])
                            ->searchable()
                            ->default('heroicon-o-star'),
                        Select::make('color')
                            ->label('Warna')
                            ->options([
                                'indigo' => 'Indigo',
                                'emerald' => 'Emerald',
                                'amber' => 'Amber',
                                'rose' => 'Rose',
                                'blue' => 'Blue',
                                'green' => 'Green',
                                'orange' => 'Orange',
                                'pink' => 'Pink',
                                'purple' => 'Purple',
                                'gold' => 'Gold',
                            ])
                            ->default('indigo'),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'Kehadiran' => 'Kehadiran',
                                'Produktivitas' => 'Produktivitas',
                                'Penjualan' => 'Penjualan',
                                'Pembelajaran' => 'Pembelajaran',
                                'Layanan' => 'Layanan',
                                'Dedikasi' => 'Dedikasi',
                                'Kolaborasi' => 'Kolaborasi',
                                'Prestasi' => 'Prestasi',
                            ])
                            ->searchable(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Kriteria Pencapaian')
                    ->columns(3)
                    ->schema([
                        Select::make('trigger_action')
                            ->label('Trigger Aksi')
                            ->options([
                                'clock_in_on_time' => 'Clock-in Tepat Waktu',
                                'clock_in_early' => 'Clock-in Lebih Awal',
                                'task_completed_before_deadline' => 'Tugas Sebelum Deadline',
                                'task_completed_on_time' => 'Tugas Tepat Waktu',
                                'ticket_resolved_under_sla' => 'Tiket Di Bawah SLA',
                                'ticket_resolved' => 'Tiket Selesai',
                                'deal_won' => 'Deal Won',
                                'lead_converted' => 'Lead Terkonversi',
                                'course_completed' => 'Kursus Selesai',
                                'quiz_passed' => 'Quiz Lulus',
                                'attendance_perfect_week' => 'Kehadiran Mingguan',
                                'attendance_perfect_month' => 'Kehadiran Bulanan',
                                'overtime_volunteer' => 'Relawan Lembur',
                                'peer_recognition' => 'Pengakuan Rekan',
                            ])
                            ->searchable()
                            ->nullable(),
                        TextInput::make('trigger_count')
                            ->label('Jumlah Trigger')
                            ->numeric()
                            ->integer()
                            ->nullable()
                            ->helperText('Berapa kali aksi harus dilakukan.'),
                        TextInput::make('threshold_value')
                            ->label('Nilai Ambang')
                            ->numeric()
                            ->nullable()
                            ->helperText('Nilai threshold (misal: Rp 1M).'),
                        Select::make('threshold_unit')
                            ->label('Periode Threshold')
                            ->options([
                                'consecutive_days' => 'Hari Berturut-turut',
                                'month' => 'Per Bulan',
                                '3_months' => '3 Bulan',
                                'all_time' => 'Sepanjang Waktu',
                                'categories' => 'Kategori',
                            ])
                            ->nullable(),
                        TextInput::make('points_reward')
                            ->label('Bonus Poin')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->helperText('Poin tambahan saat badge diperoleh.'),
                    ]),
            ]);
    }
}
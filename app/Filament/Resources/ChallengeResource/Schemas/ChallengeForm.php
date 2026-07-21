<?php

namespace App\Filament\Resources\ChallengeResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChallengeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Tantangan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Tantangan')
                            ->required()
                            ->maxLength(255),
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
                            ])
                            ->searchable()
                            ->nullable(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->rows(3)
                            ->maxLength(1000),
                        Select::make('target_action')
                            ->label('Target Aksi')
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
                            ->required()
                            ->searchable(),
                        TextInput::make('target_count')
                            ->label('Target Jumlah')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->minValue(1)
                            ->helperText('Berapa kali aksi harus dilakukan.'),
                        TextInput::make('points_reward')
                            ->label('Reward Poin')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->helperText('Poin yang diberikan jika tantangan diselesaikan.'),
                        DateTimePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->nullable(),
                        DateTimePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->nullable()
                            ->afterOrEqual('start_date'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}

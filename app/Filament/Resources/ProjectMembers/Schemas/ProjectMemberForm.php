<?php

namespace App\Filament\Resources\ProjectMembers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ProjectMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Anggota Project')
                    ->columns(2)
                    ->schema([
                        Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('role')
                            ->label('Peran')
                            ->required()
                            ->options([
                                'project_manager' => 'Project Manager',
                                'team_lead' => 'Team Lead',
                                'developer' => 'Developer',
                                'designer' => 'Designer',
                                'tester' => 'Tester',
                                'analyst' => 'Analyst',
                                'pm' => 'Project Manager',
                                'anggota' => 'Anggota',
                                'lainnya' => 'Lainnya',
                            ]),
                        DateTimePicker::make('joined_at')
                            ->label('Bergabung Pada')
                            ->default(now()),
                    ]),
            ]);
    }
}
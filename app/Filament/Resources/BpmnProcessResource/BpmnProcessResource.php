<?php

namespace App\Filament\Resources\BpmnProcessResource;

use App\Filament\Resources\BpmnProcessResource\Pages\ListBpmnProcesses;
use App\Filament\Resources\BpmnProcessResource\Pages\CreateBpmnProcess;
use App\Filament\Resources\BpmnProcessResource\Pages\EditBpmnProcess;
use App\Models\BpmnProcess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\Placeholder;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use App\Filament\Concerns\HasPermissionAccess;

class BpmnProcessResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BpmnProcess::class;

    protected static ?string $label = 'Proses BPMN';

    protected static ?string $pluralLabel = 'Proses BPMN';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|null
    {
        return 'BPMN';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Proses')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Proses')
                        ->required()
                        ->maxLength(500),
                    Select::make('category')
                        ->label('Kategori')
                        ->options([
                            'HR' => 'HR',
                            'Finance' => 'Finance',
                            'Procurement' => 'Procurement',
                            'Sales' => 'Sales',
                            'Helpdesk' => 'Helpdesk',
                            'Project' => 'Project',
                            'Manufacturing' => 'Manufacturing',
                            'Logistik' => 'Logistik',
                        ])
                        ->searchable(),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3),
                    TextInput::make('sla_hours')
                        ->label('SLA (jam)')
                        ->numeric()
                        ->suffix('jam')
                        ->helperText('Overall SLA untuk seluruh proses'),
                ])->columns(2),
            Section::make('Status')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                    Toggle::make('is_prebuilt')
                        ->label('Template Bawaan')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Proses')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('instances_count')
                    ->label('Instances')
                    ->counts('instances')
                    ->sortable(),
                TextColumn::make('version')
                    ->label('Versi')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('designer')
                    ->label('Designer')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn(BpmnProcess $record): string => route('filament.admin.pages.bpmn-designer', ['process' => $record]))
                    ->color('primary'),
                Action::make('start')
                    ->label('Jalankan')
                    ->icon(Heroicon::OutlinedPlay)
                    ->action(function (BpmnProcess $record) {
                        $bpmnService = app(\App\Services\BpmnService::class);
                        $bpmnService->startProcess($record->id);
                    })
                    ->color('success')
                    ->requiresConfirmation(),
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBpmnProcesses::route('/'),
            'create' => CreateBpmnProcess::route('/create'),
            'edit' => EditBpmnProcess::route('/{record}/edit'),
        ];
    }
}

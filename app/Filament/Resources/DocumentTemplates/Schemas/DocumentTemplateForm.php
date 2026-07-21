<?php

namespace App\Filament\Resources\DocumentTemplates\Schemas;

use App\Services\DocumentTemplateService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Template')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Template')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'contract' => 'Kontrak',
                                'offer_letter' => 'Surat Penawaran',
                                'warning_letter' => 'Surat Peringatan',
                                'certificate' => 'Sertifikat',
                                'invoice_custom' => 'Invoice',
                                'custom' => 'Custom',
                            ])
                            ->required()
                            ->default('custom'),
                        Select::make('module')
                            ->label('Module')
                            ->options([
                                'employee' => 'Karyawan',
                                'invoice' => 'Invoice',
                                'project' => 'Proyek',
                                'deal' => 'Deal',
                                'course' => 'Pelatihan',
                                'warning' => 'Peringatan',
                                'custom' => 'Custom',
                            ])
                            ->required()
                            ->default('custom')
                            ->live(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Konten Template')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Konten (HTML)')
                            ->required()
                            ->hint('Gunakan {{variable}} untuk placeholder data')
                            ->columnSpanFull(),
                        Placeholder::make('variables_help')
                            ->label('')
                            ->content(function ($get) {
                                $module = $get('module') ?? 'custom';
                                $service = app(DocumentTemplateService::class);
                                $vars = $service->getVariablesForModule($module);
                                $html = '<div class="text-xs text-gray-500"><p class="font-semibold mb-1">Placeholder yang tersedia untuk module ini:</p><div class="grid grid-cols-2 gap-1">';
                                foreach ($vars as $key => $desc) {
                                    $html .= '<div><code style="background:#f3f4f6;padding:1px 4px;border-radius:3px;">{{' . $key . '}}</code> <span class="text-gray-400">' . $desc . '</span></div>';
                                }
                                $html .= '</div></div>';
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
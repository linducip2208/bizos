<?php

namespace App\Filament\Resources\ChatbotFlows\Schemas;

use App\Services\ChatbotFlowService;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChatbotFlowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Flow')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Flow')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(2)
                            ->maxLength(500),
                        TagsInput::make('trigger_keywords')
                            ->label('Kata Kunci Pemicu')
                            ->helperText('Flow ini aktif saat pesan mengandung kata kunci ini')
                            ->placeholder('contoh: daftar, registrasi, promo')
                            ->separator(','),
                        RichEditor::make('welcome_message')
                            ->label('Pesan Selamat Datang')
                            ->helperText('Pesan pertama saat flow dimulai'),
                        RichEditor::make('fallback_message')
                            ->label('Pesan Fallback')
                            ->helperText('Pesan saat bot tidak mengerti maksud user'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(false)
                            ->helperText('Aktifkan untuk memproses pesan masuk'),
                    ]),
                Section::make('Flow Builder')
                    ->description('Atur node dan edge untuk alur percakapan chatbot')
                    ->schema([
                        Select::make('node_types_reference')
                            ->label('Tipe Node Tersedia')
                            ->options((new ChatbotFlowService())->getNodeTypes())
                            ->disabled()
                            ->helperText('Gunakan tipe node ini saat membuat flow'),
                        KeyValue::make('flow_editor_note')
                            ->label('Catatan Editor Flow')
                            ->default([
                                'send_message' => 'Kirim pesan ke user lalu lanjut ke node berikutnya',
                                'wait_for_reply' => 'Tunggu balasan user, simpan sebagai variabel',
                                'check_keyword' => 'Cek kata kunci dalam pesan, arahkan ke path yang sesuai',
                                'check_intent' => 'Deteksi maksud pesan dengan NLP',
                                'update_lead' => 'Simpan data ke CRM lead',
                                'create_ticket' => 'Buat tiket helpdesk otomatis',
                                'send_template' => 'Kirim template WhatsApp yang sudah disetujui',
                                'transfer_to_agent' => 'Alihkan percakapan ke agen manusia',
                                'end_conversation' => 'Akhiri percakapan',
                            ])
                            ->disabled()
                            ->helperText('Untuk builder visual, gunakan halaman edit flow'),
                    ]),
            ]);
    }
}
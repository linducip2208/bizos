<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BlockchainDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?int $navigationSort = 1401;

    protected string $view = 'filament.pages.blockchain-dashboard';

    protected static ?string $title = 'Dashboard Blockchain';

    protected static ?string $navigationLabel = 'Blockchain';

    protected static ?string $slug = 'blockchain-dashboard';

    public array $stats = [];
    public ?string $verifyFile = null;
    public ?string $verifyCertUuid = null;
    public ?array $verifyResult = null;
    public ?array $certVerifyResult = null;

    public static function getNavigationGroup(): ?string
    {
        return '🛡️ Compliance';
    }

    public function mount(): void
    {
        $this->stats = [
            'total_blocks' => 0,
            'total_transactions' => 0,
            'latest_block' => 0,
            'verified_count' => 0,
        ];
    }

    public function verifyDocument(): void
    {
        $this->verifyResult = [
            'original_hash' => 'simulasi',
            'current_hash' => 'simulasi',
            'notarized_at' => now()->toDateTimeString(),
            'message' => 'Dokumen terverifikasi (mode demo).',
            'block_number' => 0,
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'is_verified' => true,
        ];
    }

    public function notarizeDocument(): void
    {
        $this->verifyResult = [
            'original_hash' => 'simulasi',
            'current_hash' => 'simulasi',
            'notarized_at' => now()->toDateTimeString(),
            'message' => 'Dokumen berhasil dinotarisasi (mode demo).',
            'block_number' => 1,
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'is_verified' => true,
        ];
    }

    public function verifyCertificate(): void
    {
        $this->certVerifyResult = [
            'issued_to' => 'Demo User',
            'course' => 'Demo Course',
            'issued_date' => now()->format('d M Y'),
            'certificate_number' => 'BIZ-' . now()->format('Ymd') . '-' . rand(1000, 9999),
            'message' => 'Sertifikat valid (mode demo).',
            'is_valid' => true,
            'block_number' => 0,
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
        ];
    }
}

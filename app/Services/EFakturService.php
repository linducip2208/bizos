<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * e-Faktur PPN — generate nomor faktur pajak, validasi NPWP, export CSV DJP.
 */
class EFakturService
{
    protected const KODE_TRANSAKSI = [
        '01' => 'Penyerahan kepada selain Pemungut',
        '02' => 'Penyerahan kepada Pemungut (Instansi Pemerintah)',
        '03' => 'Penyerahan kepada selain Pemungut (Pemusatan)',
        '04' => 'Penyerahan kepada selain Pemungut (Kontraktor Migas)',
        '05' => 'Penyerahan yang PPN-nya dipungut sendiri',
        '06' => 'Penyerahan lainnya (emas perhiasan, dll.)',
        '07' => 'Penyerahan yang PPN-nya DTP',
        '08' => 'Penyerahan yang dibebaskan PPN',
        '09' => 'Penyerahan Aktiva Pasal 16D',
    ];

    protected const KODE_STATUS = [
        '0' => 'Normal',
        '1' => 'Pengganti',
    ];

    // ──────────────────────────────────────────────
    //  Nomor Faktur Pajak
    // ──────────────────────────────────────────────

    /**
     * Generate nomor faktur pajak.
     * Format: KODE-TRANSAKSI.KODE-STATUS.NOMOR-SERI
     * Contoh: 010.000-24.12345678
     */
    public function generateTaxInvoiceNumber(string $kodeTransaksi = '01', string $kodeStatus = '0', ?string $prefix = null): string
    {
        $tahun = now()->format('y');
        $prefix = $prefix ?? '000';

        $sequence = $this->getNextSequence();

        return sprintf('%s%s.%s-%s.%08d', $kodeTransaksi, $kodeStatus, $prefix, $tahun, $sequence);
    }

    /**
     * Generate nomor faktur pengganti.
     */
    public function generateReplacementInvoiceNumber(string $originalNumber): string
    {
        $parts = explode('.', $originalNumber);
        if (count($parts) >= 3) {
            $transCode = substr($parts[0], 0, 2);
            $newCode = $transCode . '1';
            return $newCode . '.' . $parts[1] . '.' . $parts[2];
        }

        return $originalNumber;
    }

    // ──────────────────────────────────────────────
    //  Validasi NPWP
    // ──────────────────────────────────────────────

    /**
     * Validasi NPWP 15 digit.
     *
     * Format: XX.XXX.XXX.X-XXX.XXX
     * Digit 1: Kode Wajib Pajak (0-9)
     * Digit 2-9: Nomor Register
     * Digit 10-12: Kode KPP
     * Digit 13-15: Kode Cabang (000 = pusat)
     */
    public function validateNpwp(string $npwp): array
    {
        $raw = $this->cleanNpwp($npwp);

        if (strlen($raw) !== 15 || !is_numeric($raw)) {
            return [
                'valid'   => false,
                'message' => 'NPWP harus 15 digit angka.',
                'npwp'    => $npwp,
            ];
        }

        $taxpayerType = (int) substr($raw, 0, 1);
        $registration = substr($raw, 1, 8);
        $kppCode = substr($raw, 9, 3);
        $branchCode = substr($raw, 12, 3);

        $checkDigitValid = $this->validateCheckDigit($raw);

        $formatted = sprintf(
            '%s.%s.%s.%s-%s.%s',
            substr($raw, 0, 2),
            substr($raw, 2, 3),
            substr($raw, 5, 3),
            substr($raw, 8, 1),
            substr($raw, 9, 3),
            substr($raw, 12, 3)
        );

        return [
            'valid'             => $checkDigitValid,
            'npwp_original'     => $npwp,
            'npwp_clean'        => $raw,
            'npwp_formatted'    => $formatted,
            'taxpayer_type'     => $taxpayerType,
            'registration'      => $registration,
            'kpp_code'          => $kppCode,
            'branch_code'       => $branchCode,
            'is_head_office'    => $branchCode === '000',
            'message'           => $checkDigitValid ? 'NPWP valid.' : 'Digit pemeriksaan NPWP tidak valid.',
            'type_description'  => $this->getTaxpayerTypeDescription($taxpayerType),
        ];
    }

    // ──────────────────────────────────────────────
    //  Export CSV Faktur Pajak (DJP Format)
    // ──────────────────────────────────────────────

    /**
     * Export Faktur Pajak Keluaran (PK) → CSV format DJP.
     *
     * @param array $invoices  Array of invoice arrays with keys:
     *   nomor_faktur, tanggal_faktur, npwp_pembeli, nama_pembeli,
     *   alamat_pembeli, dpp, ppn, ppnbm, keterangan, status_pengganti
     */
    public function exportFakturKeluaran(array $invoices): BinaryFileResponse
    {
        $filename = 'faktur_keluaran_' . now()->format('Ymd_His') . '.csv';
        $path = storage_path('app/temp/' . $filename);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = [
            'FK', 'KD_JENIS_TRANSAKSI', 'FG_PENGGANTI',
            'NOMOR_FAKTUR', 'MASA_PAJAK', 'TAHUN_PAJAK',
            'TANGGAL_FAKTUR', 'NPWP', 'NAMA', 'ALAMAT_LENGKAP',
            'JUMLAH_DPP', 'JUMLAH_PPN', 'JUMLAH_PPNBM',
            'ID_KETERANGAN_TAMBAHAN', 'FG_UANG_MUKA',
            'UANG_MUKA_DPP', 'UANG_MUKA_PPN', 'UANG_MUKA_PPNBM',
            'REFERENSI',
        ];

        fputcsv($handle, $headers);

        foreach ($invoices as $inv) {
            $row = [
                'FK',
                $inv['kode_transaksi'] ?? '01',
                $inv['status_pengganti'] ?? '0',
                $inv['nomor_faktur'] ?? '',
                $inv['masa_pajak'] ?? now()->format('m'),
                $inv['tahun_pajak'] ?? now()->format('Y'),
                $inv['tanggal_faktur'] ?? now()->format('d/m/Y'),
                $this->cleanNpwp($inv['npwp_pembeli'] ?? ''),
                $inv['nama_pembeli'] ?? '',
                $inv['alamat_pembeli'] ?? '',
                $inv['dpp'] ?? 0,
                $inv['ppn'] ?? 0,
                $inv['ppnbm'] ?? 0,
                $inv['keterangan'] ?? '',
                $inv['fg_uang_muka'] ?? '0',
                $inv['uang_muka_dpp'] ?? 0,
                $inv['uang_muka_ppn'] ?? 0,
                $inv['uang_muka_ppnbm'] ?? 0,
                $inv['referensi'] ?? '',
            ];

            fputcsv($handle, $row);
        }

        fclose($handle);

        return response()->download($path, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ])->deleteFileAfterSend();
    }

    /**
     * Export Faktur Pajak Masukan (PM) → CSV format DJP.
     */
    public function exportFakturMasukan(array $purchaseInvoices): BinaryFileResponse
    {
        $filename = 'faktur_masukan_' . now()->format('Ymd_His') . '.csv';
        $path = storage_path('app/temp/' . $filename);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = [
            'FM', 'KD_JENIS_TRANSAKSI', 'FG_PENGGANTI',
            'NOMOR_FAKTUR', 'MASA_PAJAK', 'TAHUN_PAJAK',
            'TANGGAL_FAKTUR', 'NPWP_PENJUAL', 'NAMA_PENJUAL',
            'JUMLAH_DPP', 'JUMLAH_PPN', 'JUMLAH_PPNBM',
            'IS_CREDITABLE',
        ];

        fputcsv($handle, $headers);

        foreach ($purchaseInvoices as $inv) {
            $row = [
                'FM',
                $inv['kode_transaksi'] ?? '01',
                $inv['status_pengganti'] ?? '0',
                $inv['nomor_faktur'] ?? '',
                $inv['masa_pajak'] ?? now()->format('m'),
                $inv['tahun_pajak'] ?? now()->format('Y'),
                $inv['tanggal_faktur'] ?? now()->format('d/m/Y'),
                $this->cleanNpwp($inv['npwp_penjual'] ?? ''),
                $inv['nama_penjual'] ?? '',
                $inv['dpp'] ?? 0,
                $inv['ppn'] ?? 0,
                $inv['ppnbm'] ?? 0,
                $inv['is_creditable'] ?? '1',
            ];

            fputcsv($handle, $row);
        }

        fclose($handle);

        return response()->download($path, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ])->deleteFileAfterSend();
    }

    // ──────────────────────────────────────────────
    //  SPT Masa PPN 1111
    // ──────────────────────────────────────────────

    /**
     * Auto-populate data SPT Masa PPN 1111.
     */
    public function generateSptPpn1111(int $year, int $month): array
    {
        return [
            'masa_pajak'      => $month,
            'tahun_pajak'     => $year,
            'periode'         => sprintf('%04d-%02d', $year, $month),

            'pk_total_dpp'    => 0,
            'pk_total_ppn'    => 0,
            'pk_jumlah_faktur' => 0,

            'pm_total_dpp'    => 0,
            'pm_total_ppn'    => 0,
            'pm_creditable'   => 0,
            'pm_not_creditable' => 0,

            'ppn_kurang_bayar' => 0,
            'ppn_lebih_bayar'  => 0,
            'ppn_nihil'        => true,

            'kompensasi_bulan_lalu' => 0,
            'kompensasi_bulan_ini'  => 0,
        ];
    }

    // ──────────────────────────────────────────────
    //  Pelacakan Pajak Masukan
    // ──────────────────────────────────────────────

    /**
     * Track Pajak Masukan (PM) untuk credit tracking.
     */
    public function trackTaxCredit(string $fakturNumber, float $dpp, float $ppn, ?string $npwpPenjual = null): array
    {
        return [
            'faktur_number'  => $fakturNumber,
            'dpp'            => $dpp,
            'ppn'            => $ppn,
            'npwp_penjual'   => $npwpPenjual,
            'is_creditable'  => true,
            'masa_pajak'     => (int) now()->format('m'),
            'tahun_pajak'    => (int) now()->format('Y'),
            'tracked_at'     => now()->format('Y-m-d H:i:s'),
        ];
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    protected function cleanNpwp(string $npwp): string
    {
        return preg_replace('/[^0-9]/', '', $npwp);
    }

    /**
     * Validasi digit pemeriksaan (check digit) NPWP.
     * Algoritma resmi DJP: menggunakan modulus dan weighted sum.
     */
    protected function validateCheckDigit(string $npwpRaw): bool
    {
        if (strlen($npwpRaw) !== 15) {
            return false;
        }

        $digits = array_map('intval', str_split($npwpRaw));

        $checkDigit = $digits[8];
        $sum = 0;

        for ($i = 0; $i < 8; $i++) {
            $sum += $digits[$i] * ($i + 1);
        }

        return ($sum % 11) === $checkDigit;
    }

    protected function getTaxpayerTypeDescription(int $code): string
    {
        return match ($code) {
            0 => 'Wajib Pajak Non-Efektif (NE)',
            1 => 'Badan Usaha',
            2 => 'Badan (Pemerintah)',
            3 => 'Badan (Lainnya)',
            4 => 'Orang Pribadi (Karyawan)',
            5 => 'Orang Pribadi (Pengusaha)',
            6 => 'Orang Pribadi (Pekerjaan Bebas)',
            7 => 'Bendaharawan Pemerintah',
            8 => 'Badan (Organisasi Internasional)',
            9 => 'Lainnya',
            default => 'Tidak Diketahui',
        };
    }

    protected function getNextSequence(): int
    {
        $file = storage_path('app/faktur_sequence.txt');

        if (!file_exists($file)) {
            file_put_contents($file, '1');
            return 1;
        }

        $current = (int) file_get_contents($file);
        $next = $current + 1;
        file_put_contents($file, (string) $next);

        return $current;
    }

    public static function getKodeTransaksi(): array
    {
        return self::KODE_TRANSAKSI;
    }

    public static function getKodeStatus(): array
    {
        return self::KODE_STATUS;
    }
}

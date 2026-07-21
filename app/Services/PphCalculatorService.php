<?php

namespace App\Services;

/**
 * Auto-kalkulasi PPh 23, PPh 4(2), PPh 15, PPh 26.
 */
class PphCalculatorService
{
    // ──────────────────────────────────────────────
    //  PPh 23
    // ──────────────────────────────────────────────

    /**
     * Hitung PPh Pasal 23.
     *
     * @param string      $transactionType  jasa|sewa|royalti|hadiah|bunga|dividen
     * @param float       $dpp              Dasar Pengenaan Pajak (bruto)
     * @param string|null $npwp             NPWP pemotong (jika tidak ada → rate 2×)
     */
    public function calculatePph23(string $transactionType, float $dpp, ?string $npwp = null): array
    {
        $hasNpwp = !empty($npwp);

        $config = match (strtolower($transactionType)) {
            'jasa' => [
                'article'    => 'PPh 23',
                'label'      => 'Jasa / Imbalan',
                'rate_normal' => 0.02,
                'rate_no_npwp' => 0.04,
            ],
            'sewa' => [
                'article'    => 'PPh 23',
                'label'      => 'Sewa (selain tanah/bangunan)',
                'rate_normal' => 0.02,
                'rate_no_npwp' => 0.04,
            ],
            'royalti' => [
                'article'    => 'PPh 23',
                'label'      => 'Royalti',
                'rate_normal' => 0.15,
                'rate_no_npwp' => 0.30,
            ],
            'hadiah' => [
                'article'    => 'PPh 23',
                'label'      => 'Hadiah / Penghargaan',
                'rate_normal' => 0.15,
                'rate_no_npwp' => 0.30,
            ],
            'bunga' => [
                'article'    => 'PPh 23',
                'label'      => 'Bunga Simpanan/Deposito',
                'rate_normal' => 0.15,
                'rate_no_npwp' => 0.30,
            ],
            'dividen' => [
                'article'    => 'PPh 23',
                'label'      => 'Dividen',
                'rate_normal' => 0.15,
                'rate_no_npwp' => 0.30,
            ],
            default => [
                'article'    => 'PPh 23',
                'label'      => 'Lainnya',
                'rate_normal' => 0.02,
                'rate_no_npwp' => 0.04,
            ],
        };

        $rate = $hasNpwp ? $config['rate_normal'] : $config['rate_no_npwp'];
        $pphAmount = round($dpp * $rate);

        return [
            'pph_article'       => $config['article'],
            'transaction_type'  => $transactionType,
            'label'             => $config['label'],
            'dpp_value'         => $dpp,
            'rate'              => $rate,
            'rate_percent'      => round($rate * 100, 1) . '%',
            'pph_amount'        => $pphAmount,
            'has_npwp'          => $hasNpwp,
            'has_npwp_penalty'  => !$hasNpwp,
            'npwp_penalty_note' => $hasNpwp ? null : 'Tarif 2× lipat karena tidak ada NPWP',
        ];
    }

    // ──────────────────────────────────────────────
    //  PPh 4(2) Final
    // ──────────────────────────────────────────────

    /**
     * Hitung PPh Pasal 4 ayat 2 (Final).
     *
     * @param string $transactionType  sewa_bangunan|konstruksi_kecil|konstruksi_tanpa|pengalihan_tanah
     */
    public function calculatePph4ayat2(string $transactionType, float $dpp): array
    {
        $config = match (strtolower($transactionType)) {
            'sewa_bangunan' => [
                'label' => 'Sewa Tanah dan/atau Bangunan',
                'rate'  => 0.10,
            ],
            'konstruksi_kecil' => [
                'label' => 'Jasa Konstruksi (Kualifikasi Kecil) — PP 9/2022',
                'rate'  => 0.0175,
            ],
            'konstruksi_menengah' => [
                'label' => 'Jasa Konstruksi (Kualifikasi Menengah) — PP 9/2022',
                'rate'  => 0.04,
            ],
            'konstruksi_besar' => [
                'label' => 'Jasa Konstruksi (Kualifikasi Besar) — PP 9/2022',
                'rate'  => 0.0265,
            ],
            'konstruksi_tanpa' => [
                'label' => 'Jasa Konstruksi (Tanpa Kualifikasi)',
                'rate'  => 0.04,
            ],
            'pengalihan_tanah' => [
                'label' => 'Pengalihan Hak atas Tanah dan/atau Bangunan',
                'rate'  => 0.025,
            ],
            'pph_final_umkm' => [
                'label' => 'PP 23/2018 — UMKM Final',
                'rate'  => 0.005,
            ],
            default => [
                'label' => 'PPh Final Lainnya',
                'rate'  => 0.10,
            ],
        };

        $pphAmount = round($dpp * $config['rate']);

        return [
            'pph_article'       => 'PPh 4(2) Final',
            'transaction_type'  => $transactionType,
            'label'             => $config['label'],
            'dpp_value'         => $dpp,
            'rate'              => $config['rate'],
            'rate_percent'      => round($config['rate'] * 100, 2) . '%',
            'pph_amount'        => $pphAmount,
            'is_final'          => true,
        ];
    }

    // ──────────────────────────────────────────────
    //  PPh 15
    // ──────────────────────────────────────────────

    /**
     * Hitung PPh Pasal 15 (Pelayaran/Penerbangan).
     *
     * @param string $transactionType  pelayaran_dalam|pelayaran_luar|penerbangan_dalam|penerbangan_luar
     */
    public function calculatePph15(string $transactionType, float $dpp): array
    {
        $config = match (strtolower($transactionType)) {
            'pelayaran_dalam' => [
                'label' => 'Pelayaran Dalam Negeri',
                'rate'  => 0.012,
            ],
            'pelayaran_luar' => [
                'label' => 'Pelayaran Luar Negeri',
                'rate'  => 0.0264,
            ],
            'penerbangan_dalam' => [
                'label' => 'Penerbangan Dalam Negeri',
                'rate'  => 0.018,
            ],
            'penerbangan_luar' => [
                'label' => 'Penerbangan Luar Negeri',
                'rate'  => 0.0264,
            ],
            default => [
                'label' => 'PPh 15 Lainnya',
                'rate'  => 0.012,
            ],
        };

        $pphAmount = round($dpp * $config['rate']);

        return [
            'pph_article'       => 'PPh 15',
            'transaction_type'  => $transactionType,
            'label'             => $config['label'],
            'dpp_value'         => $dpp,
            'rate'              => $config['rate'],
            'rate_percent'      => round($config['rate'] * 100, 2) . '%',
            'pph_amount'        => $pphAmount,
        ];
    }

    // ──────────────────────────────────────────────
    //  PPh 26
    // ──────────────────────────────────────────────

    /**
     * Hitung PPh Pasal 26 (Wajib Pajak Luar Negeri).
     * Default: 20% (bruto). Bisa lebih rendah jika ada tax treaty + DGT form.
     *
     * @param float       $dpp
     * @param string|null $taxTreatyCountry  Kode negara tax treaty (dari DGT)
     */
    public function calculatePph26(float $dpp, ?string $taxTreatyCountry = null): array
    {
        $defaultRate = 0.20;
        $treatyRate = null;
        $effectiveRate = $defaultRate;

        if ($taxTreatyCountry) {
            $treatyRate = $this->getTaxTreatyRate($taxTreatyCountry);
            if ($treatyRate !== null) {
                $effectiveRate = $treatyRate;
            }
        }

        $pphAmount = round($dpp * $effectiveRate);

        return [
            'pph_article'          => 'PPh 26',
            'label'                => 'Wajib Pajak Luar Negeri',
            'dpp_value'            => $dpp,
            'default_rate'         => $defaultRate,
            'default_rate_percent' => '20%',
            'tax_treaty_country'   => $taxTreatyCountry,
            'treaty_rate'          => $treatyRate,
            'effective_rate'       => $effectiveRate,
            'effective_rate_percent' => round($effectiveRate * 100, 1) . '%',
            'pph_amount'           => $pphAmount,
            'has_treaty_benefit'   => $treatyRate !== null && $treatyRate < $defaultRate,
            'note'                 => $taxTreatyCountry
                ? ($treatyRate ? "Menggunakan tax treaty rate" : "Tax treaty tidak ditemukan, default 20%")
                : "Tidak ada DGT form, default 20%",
        ];
    }

    // ──────────────────────────────────────────────
    //  Tax Treaty Rates (P3B)
    // ──────────────────────────────────────────────

    /**
     * Tarif PPh 26 berdasarkan tax treaty (P3B) untuk dividen/bunga/royalti.
     * Rate umum; bisa berbeda per jenis penghasilan.
     */
    protected function getTaxTreatyRate(string $countryCode): ?float
    {
        $treatyMap = [
            'SG' => 0.10,
            'MY' => 0.10,
            'JP' => 0.10,
            'KR' => 0.10,
            'CN' => 0.10,
            'AU' => 0.15,
            'GB' => 0.10,
            'US' => 0.10,
            'DE' => 0.10,
            'FR' => 0.10,
            'NL' => 0.10,
            'HK' => 0.05,
            'AE' => 0.05,
            'QA' => 0.05,
            'KW' => 0.05,
        ];

        return $treatyMap[strtoupper($countryCode)] ?? null;
    }

    public static function getTransactionTypes(): array
    {
        return [
            'jasa'             => 'Jasa / Imbalan (2% / 4%)',
            'sewa'             => 'Sewa selain tanah/bangunan (2% / 4%)',
            'royalti'          => 'Royalti (15% / 30%)',
            'hadiah'           => 'Hadiah / Penghargaan (15% / 30%)',
            'bunga'            => 'Bunga Simpanan (15% / 30%)',
            'dividen'          => 'Dividen (15% / 30%)',
        ];
    }

    public static function getPph4ayat2Types(): array
    {
        return [
            'sewa_bangunan'         => 'Sewa Tanah/Bangunan — 10%',
            'konstruksi_kecil'      => 'Jasa Konstruksi Kualifikasi Kecil — 1,75%',
            'konstruksi_menengah'   => 'Jasa Konstruksi Kualifikasi Menengah — 4%',
            'konstruksi_besar'      => 'Jasa Konstruksi Kualifikasi Besar — 2,65%',
            'konstruksi_tanpa'      => 'Jasa Konstruksi Tanpa Kualifikasi — 4%',
            'pengalihan_tanah'      => 'Pengalihan Hak Tanah/Bangunan — 2,5%',
            'pph_final_umkm'        => 'PP 23 UMKM Final — 0,5%',
        ];
    }
}

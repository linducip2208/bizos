<?php

namespace App\Services;

/**
 * Generate file batch transfer bank Indonesia.
 * Format: BCA KlikPay, Mandiri Cash Management, BNI Direct.
 */
class BankTransferService
{
    protected const BANK_PREFIXES = [
        'BCA' => [
            'prefixes' => ['0'],
            'min_length' => 10,
            'max_length' => 10,
        ],
        'Mandiri' => [
            'prefixes' => ['00'],
            'min_length' => 13,
            'max_length' => 13,
        ],
        'BNI' => [
            'prefixes' => ['0'],
            'min_length' => 10,
            'max_length' => 10,
        ],
        'BRI' => [
            'prefixes' => ['0'],
            'min_length' => 15,
            'max_length' => 15,
        ],
        'CIMB Niaga' => [
            'prefixes' => ['7', '8'],
            'min_length' => 10,
            'max_length' => 13,
        ],
        'Danamon' => [
            'prefixes' => ['0'],
            'min_length' => 10,
            'max_length' => 10,
        ],
        'Permata' => [
            'prefixes' => ['0', '1', '2', '3'],
            'min_length' => 10,
            'max_length' => 12,
        ],
        'BTN' => [
            'prefixes' => ['0'],
            'min_length' => 10,
            'max_length' => 15,
        ],
        'BSI' => [
            'prefixes' => ['7', '0'],
            'min_length' => 10,
            'max_length' => 13,
        ],
    ];

    // ──────────────────────────────────────────────
    //  BCA KlikPay CSV
    // ──────────────────────────────────────────────

    /**
     * Generate file CSV format BCA KlikPay.
     *
     * @param array $payments  Array dengan key:
     *   company_code, account_number, beneficiary_name, amount, description, email
     * @return string
     */
    public function generateBcaBatch(array $payments): string
    {
        $lines = [];

        $headers = [
            'Company Code',
            'Beneficiary Account Number',
            'Beneficiary Name',
            'Amount',
            'Description',
            'Email Address',
        ];

        $lines[] = implode(',', array_map(fn($h) => '"' . $h . '"', $headers));

        foreach ($payments as $p) {
            $row = [
                $p['company_code'] ?? '',
                $p['account_number'] ?? '',
                $this->sanitizeName($p['beneficiary_name'] ?? ''),
                number_format($p['amount'] ?? 0, 2, '.', ''),
                $this->sanitizeDescription($p['description'] ?? ''),
                $p['email'] ?? '',
            ];

            $lines[] = implode(',', array_map(fn($f) => '"' . $f . '"', $row));
        }

        $trailer = [
            'TRAILER',
            '"' . count($payments) . '"',
            '"' . number_format(array_sum(array_column($payments, 'amount')), 2, '.', '') . '"',
        ];

        $lines[] = implode(',', $trailer);

        return implode("\r\n", $lines);
    }

    // ──────────────────────────────────────────────
    //  Mandiri Cash Management TXT
    // ──────────────────────────────────────────────

    /**
     * Generate file TXT format Mandiri Cash Management.
     * Format: fixed-width dengan header + detail + trailer.
     *
     * @return string
     */
    public function generateMandiriBatch(array $payments): string
    {
        $lines = [];

        $header = sprintf(
            'H%1s%8s%8s%8s%15s%15s',
            '0',
            date('Ymd'),
            str_pad(date('His'), 8, '0', STR_PAD_LEFT),
            sprintf('%08d', count($payments)),
            str_pad('', 15),
            str_pad('', 15)
        );

        $lines[] = $header;

        $totalAmount = 0;
        $seq = 1;

        foreach ($payments as $p) {
            $amount = (float) ($p['amount'] ?? 0);
            $totalAmount += $amount;

            $line = sprintf(
                'D%6s%15s%25s%15s%20s',
                str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
                str_pad($p['account_number'] ?? '', 15),
                mb_substr($this->sanitizeName($p['beneficiary_name'] ?? ''), 0, 25),
                str_pad(number_format($amount, 2, '', ''), 15, '0', STR_PAD_LEFT),
                mb_substr($this->sanitizeDescription($p['description'] ?? ''), 0, 20),
            );

            $lines[] = $line;
            $seq++;
        }

        $trailer = sprintf(
            'T%6s%15s',
            str_pad((string) count($payments), 6, '0', STR_PAD_LEFT),
            str_pad(number_format($totalAmount, 2, '', ''), 15, '0', STR_PAD_LEFT),
        );

        $lines[] = $trailer;

        return implode("\r\n", $lines);
    }

    // ──────────────────────────────────────────────
    //  BNI Direct Format
    // ──────────────────────────────────────────────

    /**
     * Generate file TXT format BNI Direct.
     * Format: pipe-delimited dengan header.
     *
     * @return string
     */
    public function generateBniBatch(array $payments): string
    {
        $lines = [];

        $headerFields = [
            'HEADER',
            date('Ymd'),
            date('His'),
            'BIZOS',
            '',
            '',
        ];

        $lines[] = implode('|', $headerFields);

        $totalAmount = 0;

        foreach ($payments as $p) {
            $amount = (float) ($p['amount'] ?? 0);
            $totalAmount += $amount;

            $detailFields = [
                'DETAIL',
                $p['account_number'] ?? '',
                $this->sanitizeName($p['beneficiary_name'] ?? ''),
                number_format($amount, 2, '.', ''),
                mb_substr($this->sanitizeDescription($p['description'] ?? ''), 0, 30),
                $p['bank_code'] ?? '',
                $p['email'] ?? '',
            ];

            $lines[] = implode('|', $detailFields);
        }

        $trailerFields = [
            'TRAILER',
            (string) count($payments),
            number_format($totalAmount, 2, '.', ''),
            '',
        ];

        $lines[] = implode('|', $trailerFields);

        return implode("\r\n", $lines);
    }

    // ──────────────────────────────────────────────
    //  Deteksi Bank dari Nomor Rekening
    // ──────────────────────────────────────────────

    /**
     * Deteksi nama bank dari prefix nomor rekening.
     */
    public function detectBankFromAccount(string $accountNumber): string
    {
        $accountNumber = trim($accountNumber);
        $length = strlen($accountNumber);

        $scores = [];

        foreach (self::BANK_PREFIXES as $bank => $info) {
            $score = 0;

            foreach ($info['prefixes'] as $prefix) {
                if (str_starts_with($accountNumber, $prefix)) {
                    $score += 3;
                }
            }

            if ($length >= $info['min_length'] && $length <= $info['max_length']) {
                $score += 2;
            }

            if ($score > 0) {
                $scores[$bank] = $score;
            }
        }

        if (empty($scores)) {
            return 'Tidak Diketahui';
        }

        arsort($scores);

        return array_key_first($scores);
    }

    /**
     * Validasi panjang + format nomor rekening.
     */
    public function validateAccountNumber(string $bank, string $accountNumber): array
    {
        $accountNumber = trim($accountNumber);
        $length = strlen($accountNumber);

        $info = self::BANK_PREFIXES[$bank] ?? null;

        if (!$info) {
            return [
                'valid'   => true,
                'message' => 'Bank tidak tersedia dalam database validasi.',
                'bank'    => $bank,
                'account' => $accountNumber,
            ];
        }

        $prefixMatch = false;
        foreach ($info['prefixes'] as $prefix) {
            if (str_starts_with($accountNumber, $prefix)) {
                $prefixMatch = true;
                break;
            }
        }

        $lengthOk = $length >= $info['min_length'] && $length <= $info['max_length'];

        $messages = [];

        if (!$prefixMatch) {
            $messages[] = "Prefix nomor rekening tidak sesuai dengan {$bank}.";
        }

        if (!$lengthOk) {
            $messages[] = "Panjang nomor rekening {$length} digit — {$bank} seharusnya {$info['min_length']}-{$info['max_length']} digit.";
        }

        $valid = $prefixMatch && $lengthOk;

        return [
            'valid'           => $valid,
            'bank'            => $bank,
            'account'         => $accountNumber,
            'length'          => $length,
            'expected_length' => "{$info['min_length']}-{$info['max_length']}",
            'prefix_match'    => $prefixMatch,
            'messages'        => $messages,
            'message'         => $valid ? 'Nomor rekening valid.' : implode(' ', $messages),
        ];
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    protected function sanitizeName(string $name): string
    {
        $name = mb_strtoupper(trim($name));
        $name = preg_replace('/[^A-Za-z0-9\s\.\,\-\']/', '', $name);

        return mb_substr($name, 0, 50);
    }

    protected function sanitizeDescription(string $desc): string
    {
        $desc = trim($desc);
        $desc = preg_replace('/[^A-Za-z0-9\s\.\,\-\/\(\)]/', '', $desc);

        return mb_substr($desc, 0, 35);
    }

    public static function getSupportedBanks(): array
    {
        return array_keys(self::BANK_PREFIXES);
    }
}

<?php

namespace App\Services;

use App\Models\LoyaltyConfig;
use App\Models\LoyaltyTransaction;
use App\Models\PosMember;
use App\Models\PosTransaction;
use Carbon\Carbon;

class LoyaltyService
{
    public function earnPoints(PosTransaction $transaction, PosMember $member): int
    {
        $config = $this->getConfig($transaction->company_id);

        $baseRate = $config->earn_rate ?? 1;
        $amount = (float) $transaction->grand_total;
        $pointsBase = (int) floor($amount / $baseRate);

        $multiplier = $this->getPointMultiplier($member, $transaction);
        $points = (int) floor($pointsBase * $multiplier);

        if ($points <= 0) return 0;

        $member->update([
            'points_balance' => ($member->points_balance ?? 0) + $points,
            'total_points_earned' => ($member->total_points_earned ?? 0) + $points,
        ]);

        LoyaltyTransaction::create([
            'member_id' => $member->id,
            'transaction_id' => $transaction->id,
            'type' => 'earn',
            'points' => $points,
            'description' => "Poin dari transaksi #{$transaction->receipt_number} (multiplier: {$multiplier}x)",
        ]);

        $this->checkTierUpgrade($member);

        return $points;
    }

    public function redeemPoints(PosMember $member, int $points): float
    {
        if (($member->points_balance ?? 0) < $points) {
            throw new \InvalidArgumentException('Poin tidak mencukupi untuk ditukarkan');
        }

        $config = $this->getConfig($member->company_id);
        $redeemRate = $config->redeem_rate ?? 100;
        $amount = round(($points / 1000) * $redeemRate, 2);

        $member->update([
            'points_balance' => max(0, ($member->points_balance ?? 0) - $points),
        ]);

        LoyaltyTransaction::create([
            'member_id' => $member->id,
            'type' => 'redeem',
            'points' => -$points,
            'description' => "Tukar {$points} poin = Rp " . number_format($amount, 0, ',', '.'),
        ]);

        return $amount;
    }

    public function calculateTier(PosMember $member): string
    {
        $totalPoints = $member->total_points_earned ?? 0;
        $config = $this->getConfig($member->company_id);

        if ($totalPoints >= ($config->platinum_threshold ?? 20000)) {
            return 'platinum';
        }
        if ($totalPoints >= ($config->gold_threshold ?? 5000)) {
            return 'gold';
        }
        return 'silver';
    }

    public function checkTierUpgrade(PosMember $member): ?string
    {
        $newTier = $this->calculateTier($member);
        $currentTier = $member->tier ?? 'silver';

        $tierOrder = ['silver' => 0, 'gold' => 1, 'platinum' => 2];

        if (($tierOrder[$newTier] ?? 0) > ($tierOrder[$currentTier] ?? 0)) {
            $member->update(['tier' => $newTier]);

            LoyaltyTransaction::create([
                'member_id' => $member->id,
                'type' => 'adjustment',
                'points' => 0,
                'description' => "Upgrade tier: {$currentTier} → {$newTier}",
            ]);

            return $newTier;
        }

        return null;
    }

    public function expirePoints(int $companyId): int
    {
        $config = $this->getConfig($companyId);
        $expiryMonths = $config->points_expiry_months ?? 12;
        $expired = 0;

        $cutOffDate = Carbon::now()->subMonths($expiryMonths);

        $allMembers = PosMember::where('company_id', $companyId)
            ->where('points_balance', '>', 0)
            ->get();

        foreach ($allMembers as $member) {
            $oldTransactions = LoyaltyTransaction::where('member_id', $member->id)
                ->where('type', 'earn')
                ->where('created_at', '<', $cutOffDate)
                ->sum('points');

            if ($oldTransactions > 0) {
                $expireAmount = min($oldTransactions, $member->points_balance);
                if ($expireAmount > 0) {
                    $member->update([
                        'points_balance' => $member->points_balance - $expireAmount,
                    ]);

                    LoyaltyTransaction::create([
                        'member_id' => $member->id,
                        'type' => 'expire',
                        'points' => -$expireAmount,
                        'description' => "{$expireAmount} poin kadaluarsa (>{$expiryMonths} bulan)",
                    ]);

                    $expired += $expireAmount;
                }
            }
        }

        return $expired;
    }

    public function getMemberSummary(PosMember $member): array
    {
        $transactions = LoyaltyTransaction::where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $totalEarned = LoyaltyTransaction::where('member_id', $member->id)
            ->where('type', 'earn')
            ->sum('points');

        $totalRedeemed = LoyaltyTransaction::where('member_id', $member->id)
            ->where('type', 'redeem')
            ->sum('points');

        return [
            'member_name' => $member->name,
            'member_code' => $member->member_code,
            'points_balance' => $member->points_balance ?? 0,
            'tier' => $member->tier ?? 'silver',
            'total_points_earned' => $totalEarned,
            'total_points_redeemed' => abs($totalRedeemed),
            'next_tier' => $this->getNextTier($member),
            'recent_transactions' => $transactions->toArray(),
        ];
    }

    public function adjustPoints(PosMember $member, int $points, string $reason, string $type = 'adjustment'): void
    {
        $member->update([
            'points_balance' => ($member->points_balance ?? 0) + $points,
            'total_points_earned' => max(0, ($member->total_points_earned ?? 0) + ($points > 0 ? $points : 0)),
        ]);

        LoyaltyTransaction::create([
            'member_id' => $member->id,
            'type' => $type,
            'points' => $points,
            'description' => $reason,
        ]);

        $this->checkTierUpgrade($member);
    }

    protected function getConfig(int $companyId): LoyaltyConfig
    {
        $config = LoyaltyConfig::where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            $config = LoyaltyConfig::firstOrCreate(
                ['company_id' => $companyId],
                [
                    'earn_rate' => 1,
                    'redeem_rate' => 100,
                    'points_expiry_months' => 12,
                    'silver_threshold' => 0,
                    'gold_threshold' => 5000,
                    'platinum_threshold' => 20000,
                    'is_active' => true,
                ]
            );
        }

        return $config;
    }

    protected function getPointMultiplier(PosMember $member, PosTransaction $transaction): float
    {
        $multiplier = 1.0;

        if (!empty($member->birthday)) {
            $birthday = Carbon::parse($member->birthday);
            $today = Carbon::now();

            if ($birthday->month === $today->month && $birthday->day === $today->day) {
                $multiplier = 2.0;
            }
        }

        $tierMultiplier = match ($member->tier ?? 'silver') {
            'gold' => 1.25,
            'platinum' => 1.5,
            default => 1.0,
        };

        return $multiplier * $tierMultiplier;
    }

    protected function getNextTier(PosMember $member): ?array
    {
        $config = $this->getConfig($member->company_id);
        $totalPoints = $member->total_points_earned ?? 0;
        $currentTier = $member->tier ?? 'silver';

        if ($currentTier === 'platinum') return null;

        if ($currentTier === 'silver') {
            $needed = ($config->gold_threshold ?? 5000) - $totalPoints;
            return [
                'tier' => 'gold',
                'points_needed' => max(0, $needed),
                'threshold' => $config->gold_threshold ?? 5000,
            ];
        }

        if ($currentTier === 'gold') {
            $needed = ($config->platinum_threshold ?? 20000) - $totalPoints;
            return [
                'tier' => 'platinum',
                'points_needed' => max(0, $needed),
                'threshold' => $config->platinum_threshold ?? 20000,
            ];
        }

        return null;
    }

    protected function getTierBenefits(string $tier): array
    {
        return match ($tier) {
            'silver' => [
                'earn_multiplier' => 1.0,
                'discount_percent' => 0,
                'free_shipping' => false,
                'priority_support' => false,
            ],
            'gold' => [
                'earn_multiplier' => 1.25,
                'discount_percent' => 3,
                'free_shipping' => true,
                'priority_support' => false,
            ],
            'platinum' => [
                'earn_multiplier' => 1.5,
                'discount_percent' => 5,
                'free_shipping' => true,
                'priority_support' => true,
            ],
            default => [],
        };
    }
}

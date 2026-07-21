<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\GamificationAction;
use App\Models\GamificationBadge;
use App\Models\GamificationPoint;
use App\Models\PeerRecognition;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserBadge;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    protected array $actionConfig = [
        'clock_in_on_time' => ['points' => 5, 'name' => 'Clock-in Tepat Waktu', 'category' => 'Kehadiran'],
        'clock_in_early' => ['points' => 3, 'name' => 'Clock-in Lebih Awal', 'category' => 'Kehadiran'],
        'task_completed_before_deadline' => ['points' => 15, 'name' => 'Tugas Selesai Sebelum Deadline', 'category' => 'Produktivitas'],
        'task_completed_on_time' => ['points' => 10, 'name' => 'Tugas Selesai Tepat Waktu', 'category' => 'Produktivitas'],
        'ticket_resolved_under_sla' => ['points' => 20, 'name' => 'Tiket Selesai Di Bawah SLA', 'category' => 'Layanan'],
        'ticket_resolved' => ['points' => 10, 'name' => 'Tiket Selesai', 'category' => 'Layanan'],
        'deal_won' => ['points' => 50, 'name' => 'Deal Dimenangkan', 'category' => 'Penjualan'],
        'lead_converted' => ['points' => 25, 'name' => 'Lead Terkonversi', 'category' => 'Penjualan'],
        'course_completed' => ['points' => 30, 'name' => 'Kursus Diselesaikan', 'category' => 'Pembelajaran'],
        'quiz_passed' => ['points' => 20, 'name' => 'Quiz Lulus', 'category' => 'Pembelajaran'],
        'attendance_perfect_week' => ['points' => 25, 'name' => 'Kehadiran Sempurna Mingguan', 'category' => 'Kehadiran'],
        'attendance_perfect_month' => ['points' => 100, 'name' => 'Kehadiran Sempurna Bulanan', 'category' => 'Kehadiran'],
        'overtime_volunteer' => ['points' => 10, 'name' => 'Relawan Lembur', 'category' => 'Dedikasi'],
        'peer_recognition' => ['points' => 5, 'name' => 'Pengakuan Rekan', 'category' => 'Kolaborasi'],
    ];

    public function awardPoints(int $userId, string $action, array $context = []): int
    {
        $config = $this->actionConfig[$action] ?? null;
        if (!$config) return 0;

        $gamificationAction = GamificationAction::where('key', $action)->first();
        $points = $gamificationAction ? $gamificationAction->base_points : $config['points'];
        $maxPerDay = $gamificationAction?->max_per_day;

        if ($maxPerDay) {
            $todayCount = GamificationPoint::where('user_id', $userId)
                ->where('action_key', $action)
                ->whereDate('created_at', today())
                ->count();
            if ($todayCount >= $maxPerDay) return 0;
        }

        $user = User::find($userId);
        if (!$user) return 0;

        $point = GamificationPoint::create([
            'company_id' => $user->company_id,
            'user_id' => $userId,
            'branch_id' => $user->employee?->branch_id,
            'department_id' => $user->employee?->department_id,
            'action_id' => $gamificationAction?->id,
            'action_key' => $action,
            'points' => $points,
            'context' => $context,
            'period_date' => today(),
        ]);

        $this->updateChallengeProgress($userId, $action);
        $this->checkAndAwardBadges($userId);

        return $points;
    }

    public function checkAndAwardBadges(int $userId): array
    {
        $newlyAwarded = [];
        $user = User::with('employee')->find($userId);
        if (!$user) return $newlyAwarded;

        $badges = GamificationBadge::where('is_active', true)->get();
        $existingBadgeIds = UserBadge::where('user_id', $userId)->pluck('badge_id')->toArray();

        foreach ($badges as $badge) {
            if (in_array($badge->id, $existingBadgeIds)) continue;

            $earned = $this->evaluateBadge($user, $badge);
            if ($earned) {
                UserBadge::create([
                    'user_id' => $userId,
                    'badge_id' => $badge->id,
                    'awarded_at' => now(),
                    'context' => ['auto_awarded' => true],
                ]);
                $newlyAwarded[] = $badge->toArray();
            }
        }

        return $newlyAwarded;
    }

    protected function evaluateBadge(User $user, GamificationBadge $badge): bool
    {
        $action = $badge->trigger_action;
        $count = $badge->trigger_count;
        $threshold = $badge->threshold_value;

        $query = GamificationPoint::where('user_id', $user->id);

        if ($action) {
            $query->where('action_key', $action);
        }

        if ($threshold) {
            $period = $badge->threshold_unit;
            if ($period === 'consecutive_days') {
                return $this->checkConsecutiveDays($user->id, $action, $count ?? 20);
            }
            if ($period === 'month') {
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
            if ($period === '3_months') {
                $query->where('created_at', '>=', now()->subMonths(3));
            }
            if ($period === 'all_time') {
            }

            return $query->count() >= ($count ?? $threshold);
        }

        if ($count) {
            return $query->count() >= $count;
        }

        return false;
    }

    protected function checkConsecutiveDays(int $userId, string $action, int $days): bool
    {
        $records = GamificationPoint::where('user_id', $userId)
            ->where('action_key', $action)
            ->whereDate('created_at', '<=', today())
            ->orderBy('created_at', 'desc')
            ->pluck('created_at')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        if (count($records) < $days) return false;

        $consecutive = 1;
        $maxConsecutive = 1;

        for ($i = 1; $i < count($records); $i++) {
            $prev = Carbon::parse($records[$i - 1]);
            $curr = Carbon::parse($records[$i]);
            if ($prev->diffInDays($curr) === 1) {
                $consecutive++;
                $maxConsecutive = max($maxConsecutive, $consecutive);
            } else {
                $consecutive = 1;
            }
        }

        return $maxConsecutive >= $days;
    }

    public function getLeaderboard(string $scope = 'company', string $period = 'weekly', int $limit = 20): array
    {
        $query = User::query()
            ->select('users.id', 'users.name', 'users.avatar', 'users.company_id')
            ->selectRaw('COALESCE(SUM(gp.points), 0) as total_points')
            ->selectRaw('COUNT(DISTINCT ub.badge_id) as badges_count')
            ->leftJoin('gamification_points as gp', function ($join) {
                $join->on('users.id', '=', 'gp.user_id');
            })
            ->leftJoin('user_badges as ub', function ($join) {
                $join->on('users.id', '=', 'ub.user_id');
            })
            ->where('users.is_active', true)
            ->groupBy('users.id', 'users.name', 'users.avatar', 'users.company_id')
            ->orderBy('total_points', 'desc');

        if ($period === 'weekly') {
            $query->where('gp.created_at', '>=', now()->startOfWeek());
            $query->orWhereNull('gp.id');
            $query->where('users.is_active', true)->groupBy('users.id', 'users.name', 'users.avatar', 'users.company_id');
        } elseif ($period === 'monthly') {
            $query->where('gp.created_at', '>=', now()->startOfMonth());
        } elseif ($period !== 'all_time') {
            $query->where('gp.created_at', '>=', now()->subMonths(3));
        }

        $query->limit($limit);

        $results = $query->get();

        $employeeQuery = User::query()->where('users.is_active', true);
        if ($scope === 'department' && auth()->check()) {
            $user = auth()->user();
            if ($user->employee?->department_id) {
                $employeeQuery->whereHas('employee', fn($q) => $q->where('department_id', $user->employee->department_id));
            }
        } elseif ($scope === 'branch' && auth()->check()) {
            $user = auth()->user();
            if ($user->employee?->branch_id) {
                $employeeQuery->whereHas('employee', fn($q) => $q->where('branch_id', $user->employee->branch_id));
            }
        } elseif ($scope === 'company' && auth()->check()) {
            $employeeQuery->where('company_id', auth()->user()->company_id);
        }

        $rankedUsers = $employeeQuery->get()->sortByDesc(function ($u) use ($period) {
            $q = GamificationPoint::where('user_id', $u->id);
            if ($period === 'weekly') $q->where('created_at', '>=', now()->startOfWeek());
            elseif ($period === 'monthly') $q->where('created_at', '>=', now()->startOfMonth());
            return $q->sum('points');
        })->values()->take($limit);

        $leaderboard = [];
        foreach ($rankedUsers as $index => $u) {
            $pts = GamificationPoint::where('user_id', $u->id);
            if ($period === 'weekly') $pts->where('created_at', '>=', now()->startOfWeek());
            elseif ($period === 'monthly') $pts->where('created_at', '>=', now()->startOfMonth());
            $totalPoints = $pts->sum('points');
            $badgeCount = UserBadge::where('user_id', $u->id)->count();

            $leaderboard[] = [
                'rank' => $index + 1,
                'user_id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->avatar,
                'points' => $totalPoints,
                'badges_count' => $badgeCount,
                'department' => $u->employee?->department?->name ?? '-',
            ];
        }

        return $leaderboard;
    }

    public function getAvailableRewards(int $userId): Collection
    {
        $totalPoints = $this->getTotalPoints($userId);
        return Reward::where('is_active', true)
            ->where('points_cost', '<=', $totalPoints)
            ->where(function ($q) {
                $q->where('stock', '>', 0)
                  ->orWhere('stock', -1);
            })
            ->get()
            ->filter(fn($r) => $r->remaining_stock > 0 || $r->stock === -1);
    }

    public function getTotalPoints(int $userId): int
    {
        return (int) GamificationPoint::where('user_id', $userId)->sum('points');
    }

    public function getSpentPoints(int $userId): int
    {
        return (int) RewardRedemption::where('user_id', $userId)
            ->where('status', '!=', 'rejected')
            ->sum('points_spent');
    }

    public function redeemReward(int $userId, int $rewardId): RewardRedemption
    {
        $reward = Reward::findOrFail($rewardId);
        $availablePoints = $this->getTotalPoints($userId) - $this->getSpentPoints($userId);

        if ($availablePoints < $reward->points_cost) {
            throw new \RuntimeException('Poin tidak mencukupi untuk menukarkan reward ini.');
        }

        if ($reward->stock > 0 && $reward->remaining_stock <= 0) {
            throw new \RuntimeException('Stok reward sudah habis.');
        }

        $redemption = RewardRedemption::create([
            'company_id' => auth()->user()?->company_id,
            'user_id' => $userId,
            'reward_id' => $rewardId,
            'points_spent' => $reward->points_cost,
            'status' => 'pending',
            'redeemed_at' => now(),
        ]);

        return $redemption;
    }

    public function getUserLevel(int $userId): array
    {
        $points = $this->getTotalPoints($userId);
        $levels = [
            ['level' => 0, 'title' => 'Bronze', 'threshold' => 0],
            ['level' => 1, 'title' => 'Silver', 'threshold' => 500],
            ['level' => 2, 'title' => 'Gold', 'threshold' => 2000],
            ['level' => 3, 'title' => 'Platinum', 'threshold' => 5000],
            ['level' => 4, 'title' => 'Diamond', 'threshold' => 10000],
            ['level' => 5, 'title' => 'Legend', 'threshold' => 25000],
        ];

        $currentLevel = $levels[0];
        $nextLevel = $levels[1] ?? null;

        foreach ($levels as $i => $level) {
            if ($points >= $level['threshold']) {
                $currentLevel = $level;
                $nextLevel = $levels[$i + 1] ?? null;
            }
        }

        $progressPercent = 100;
        if ($nextLevel) {
            $range = $nextLevel['threshold'] - $currentLevel['threshold'];
            $progress = $points - $currentLevel['threshold'];
            $progressPercent = $range > 0 ? round(($progress / $range) * 100, 1) : 100;
        }

        return [
            'level' => $currentLevel['level'],
            'title' => $currentLevel['title'],
            'points' => $points,
            'next_level_points' => $nextLevel ? $nextLevel['threshold'] : null,
            'next_level_title' => $nextLevel ? $nextLevel['title'] : null,
            'progress_percent' => min(100, $progressPercent),
        ];
    }

    public function createChallenge(array $data): Challenge
    {
        return Challenge::create($data);
    }

    public function getActiveChallenges(int $userId): Collection
    {
        return Challenge::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->with(['participants' => fn($q) => $q->where('user_id', $userId)])
            ->get();
    }

    public function getChallengeProgress(int $userId, int $challengeId): array
    {
        $challenge = Challenge::findOrFail($challengeId);
        $participant = ChallengeParticipant::where('challenge_id', $challengeId)
            ->where('user_id', $userId)
            ->first();

        $progress = $participant?->current_count ?? 0;

        return [
            'progress' => $progress,
            'target' => $challenge->target_count,
            'percent' => $challenge->target_count > 0 ? round(($progress / $challenge->target_count) * 100, 1) : 0,
            'completed' => $participant?->completed ?? false,
            'points_awarded' => $participant?->points_awarded ?? 0,
        ];
    }

    protected function updateChallengeProgress(int $userId, string $action): void
    {
        $activeChallenges = Challenge::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->where('target_action', $action)
            ->get();

        foreach ($activeChallenges as $challenge) {
            $participant = ChallengeParticipant::firstOrCreate(
                ['challenge_id' => $challenge->id, 'user_id' => $userId],
                ['current_count' => 0, 'completed' => false]
            );

            if (!$participant->completed) {
                $participant->current_count += 1;
                if ($participant->current_count >= $challenge->target_count) {
                    $participant->completed = true;
                    $participant->completed_at = now();
                    $participant->points_awarded = $challenge->points_reward;

                    GamificationPoint::create([
                        'company_id' => auth()->user()?->company_id,
                        'user_id' => $userId,
                        'action_key' => 'challenge_completed',
                        'points' => $challenge->points_reward,
                        'context' => ['challenge_id' => $challenge->id, 'challenge_title' => $challenge->title],
                        'period_date' => today(),
                    ]);
                }
                $participant->save();
            }
        }
    }

    public function giveRecognition(int $fromUserId, int $toUserId, string $message, string $badge): PeerRecognition
    {
        $recognition = PeerRecognition::create([
            'company_id' => auth()->user()?->company_id,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'badge' => $badge,
            'message' => $message,
        ]);

        $this->awardPoints($toUserId, 'peer_recognition', [
            'from_user_id' => $fromUserId,
            'badge' => $badge,
        ]);

        return $recognition;
    }

    public function getUserStats(int $userId): array
    {
        return [
            'total_points' => $this->getTotalPoints($userId),
            'spent_points' => $this->getSpentPoints($userId),
            'available_points' => $this->getTotalPoints($userId) - $this->getSpentPoints($userId),
            'level' => $this->getUserLevel($userId),
            'badges' => UserBadge::where('user_id', $userId)->with('badge')->get()->toArray(),
            'recognitions_received' => PeerRecognition::where('to_user_id', $userId)->count(),
            'recognitions_given' => PeerRecognition::where('from_user_id', $userId)->count(),
            'weekly_points' => (int) GamificationPoint::where('user_id', $userId)->where('created_at', '>=', now()->startOfWeek())->sum('points'),
            'monthly_points' => (int) GamificationPoint::where('user_id', $userId)->where('created_at', '>=', now()->startOfMonth())->sum('points'),
            'ranking' => $this->getUserRanking($userId, 'monthly'),
        ];
    }

    public function getUserRanking(int $userId, string $period): int
    {
        $leaderboard = $this->getLeaderboard('company', $period, 999);
        foreach ($leaderboard as $entry) {
            if ($entry['user_id'] === $userId) {
                return $entry['rank'];
            }
        }
        return count($leaderboard) + 1;
    }

    public function seedDefaultActions(): void
    {
        foreach ($this->actionConfig as $key => $config) {
            GamificationAction::firstOrCreate(
                ['key' => $key],
                [
                    'name' => $config['name'],
                    'description' => "Poin otomatis untuk aksi: {$config['name']}",
                    'base_points' => $config['points'],
                    'category' => $config['category'],
                    'is_active' => true,
                ]
            );
        }
    }

    public function seedDefaultBadges(): void
    {
        $badges = [
            ['name' => 'Early Bird', 'slug' => 'early-bird', 'description' => 'Clock-in tepat waktu 20 hari berturut-turut', 'icon' => 'heroicon-o-sun', 'category' => 'Kehadiran', 'trigger_action' => 'clock_in_on_time', 'trigger_count' => 20, 'threshold_value' => 20, 'threshold_unit' => 'consecutive_days', 'points_reward' => 50, 'color' => 'amber'],
            ['name' => 'Night Owl', 'slug' => 'night-owl', 'description' => 'Lembur 10x dalam sebulan', 'icon' => 'heroicon-o-moon', 'category' => 'Dedikasi', 'trigger_action' => 'overtime_volunteer', 'trigger_count' => 10, 'threshold_value' => 10, 'threshold_unit' => 'month', 'points_reward' => 40, 'color' => 'indigo'],
            ['name' => 'Closer', 'slug' => 'closer', 'description' => 'Berhasil menutup 10 deal', 'icon' => 'heroicon-o-hand-thumb-up', 'category' => 'Penjualan', 'trigger_action' => 'deal_won', 'trigger_count' => 10, 'threshold_value' => 10, 'threshold_unit' => 'all_time', 'points_reward' => 100, 'color' => 'emerald'],
            ['name' => 'Knowledge Seeker', 'slug' => 'knowledge-seeker', 'description' => 'Menyelesaikan 5 kursus', 'icon' => 'heroicon-o-academic-cap', 'category' => 'Pembelajaran', 'trigger_action' => 'course_completed', 'trigger_count' => 5, 'threshold_value' => 5, 'threshold_unit' => 'all_time', 'points_reward' => 75, 'color' => 'blue'],
            ['name' => 'Customer Hero', 'slug' => 'customer-hero', 'description' => '50 tiket diselesaikan dengan rating rata-rata 4.5+', 'icon' => 'heroicon-o-shield-check', 'category' => 'Layanan', 'trigger_action' => 'ticket_resolved_under_sla', 'trigger_count' => 50, 'threshold_value' => 50, 'threshold_unit' => 'all_time', 'points_reward' => 150, 'color' => 'rose'],
            ['name' => 'Perfect Attendance', 'slug' => 'perfect-attendance', 'description' => 'Nol absen selama 3 bulan', 'icon' => 'heroicon-o-calendar-days', 'category' => 'Kehadiran', 'trigger_action' => 'attendance_perfect_month', 'trigger_count' => 3, 'threshold_value' => 3, 'threshold_unit' => '3_months', 'points_reward' => 200, 'color' => 'green'],
            ['name' => 'Mentor', 'slug' => 'mentor', 'description' => 'Menerima 5 pengakuan dari rekan', 'icon' => 'heroicon-o-heart', 'category' => 'Kolaborasi', 'trigger_action' => 'peer_recognition', 'trigger_count' => 5, 'threshold_value' => 5, 'threshold_unit' => 'all_time', 'points_reward' => 60, 'color' => 'pink'],
            ['name' => 'Speed Demon', 'slug' => 'speed-demon', 'description' => 'Rata-rata penyelesaian tiket di bawah SLA', 'icon' => 'heroicon-o-bolt', 'category' => 'Layanan', 'trigger_action' => 'ticket_resolved_under_sla', 'trigger_count' => 25, 'threshold_value' => 25, 'threshold_unit' => 'all_time', 'points_reward' => 80, 'color' => 'orange'],
            ['name' => 'Revenue Generator', 'slug' => 'revenue-generator', 'description' => 'Total nilai deal > Rp 1 Miliar', 'icon' => 'heroicon-o-banknotes', 'category' => 'Penjualan', 'trigger_action' => 'deal_won', 'trigger_count' => null, 'threshold_value' => 1_000_000_000, 'threshold_unit' => 'all_time', 'points_reward' => 200, 'color' => 'gold'],
            ['name' => 'All-Rounder', 'slug' => 'all-rounder', 'description' => 'Mendapatkan badge dari 3+ kategori berbeda', 'icon' => 'heroicon-o-trophy', 'category' => 'Prestasi', 'trigger_action' => null, 'trigger_count' => null, 'threshold_value' => 3, 'threshold_unit' => 'categories', 'points_reward' => 250, 'color' => 'purple'],
        ];

        foreach ($badges as $badge) {
            GamificationBadge::firstOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }
    }

    public function getPointsDistribution(int $companyId): array
    {
        $points = GamificationPoint::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subMonth())
            ->select('action_key', DB::raw('SUM(points) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('action_key')
            ->orderBy('total', 'desc')
            ->get();

        return $points->map(fn($p) => [
            'action' => $this->actionConfig[$p->action_key]['name'] ?? $p->action_key,
            'points' => (int) $p->total,
            'count' => (int) $p->count,
        ])->toArray();
    }

    public function getMostRecognized(int $companyId, int $limit = 10): array
    {
        return PeerRecognition::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subMonth())
            ->select('to_user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('to_user_id')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->with('toUser')
            ->get()
            ->map(fn($r) => [
                'user_id' => $r->to_user_id,
                'name' => $r->toUser->name ?? 'Unknown',
                'avatar' => $r->toUser->avatar,
                'count' => $r->count,
            ])
            ->toArray();
    }

    public function getCompanyGamificationStats(int $companyId): array
    {
        $totalUsers = User::where('company_id', $companyId)->where('is_active', true)->count();
        $totalPoints = (int) GamificationPoint::where('company_id', $companyId)->sum('points');
        $totalBadges = UserBadge::whereHas('user', fn($q) => $q->where('company_id', $companyId))->count();
        $totalRecognitions = PeerRecognition::where('company_id', $companyId)->count();
        $activeChallenges = Challenge::where('company_id', $companyId)->where('is_active', true)->count();
        $totalRewardsRedeemed = RewardRedemption::where('company_id', $companyId)->count();

        return [
            'total_users' => $totalUsers,
            'total_points' => $totalPoints,
            'total_badges' => $totalBadges,
            'total_recognitions' => $totalRecognitions,
            'active_challenges' => $activeChallenges,
            'total_rewards_redeemed' => $totalRewardsRedeemed,
            'avg_points_per_user' => $totalUsers > 0 ? round($totalPoints / $totalUsers) : 0,
        ];
    }
}

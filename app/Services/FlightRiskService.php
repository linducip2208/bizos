<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\PerformanceReview;
use App\Models\PerformanceCycle;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlightRiskService
{
    protected ?AiProvider $provider = null;

    protected array $riskWeights = [
        'attendance' => 25,
        'performance' => 20,
        'tenure' => 15,
        'compensation' => 15,
        'promotion_gap' => 10,
        'overtime_burnout' => 10,
        'leave_pattern' => 5,
    ];

    public function getProvider(): AiProvider
    {
        if ($this->provider) {
            return $this->provider;
        }

        $this->provider = AiProvider::where('is_active', true)
            ->where('api_format', 'openai_compatible')
            ->first();

        if (!$this->provider) {
            throw new \RuntimeException('Tidak ada AI Provider aktif dengan format openai_compatible.');
        }

        return $this->provider;
    }

    public function setProvider(AiProvider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function calculateRisk(Employee $employee): array
    {
        $factors = [];
        $score = 0;
        $now = Carbon::now();

        $attendanceScore = $this->analyzeAttendance($employee);
        $factors['attendance'] = $attendanceScore;
        $score += $attendanceScore['weighted'];

        $performanceScore = $this->analyzePerformance($employee);
        $factors['performance'] = $performanceScore;
        $score += $performanceScore['weighted'];

        $tenureScore = $this->analyzeTenure($employee);
        $factors['tenure'] = $tenureScore;
        $score += $tenureScore['weighted'];

        $compensationScore = $this->analyzeCompensation($employee);
        $factors['compensation'] = $compensationScore;
        $score += $compensationScore['weighted'];

        $promotionScore = $this->analyzePromotionGap($employee);
        $factors['promotion_gap'] = $promotionScore;
        $score += $promotionScore['weighted'];

        $burnoutScore = $this->analyzeOvertimeBurnout($employee);
        $factors['overtime_burnout'] = $burnoutScore;
        $score += $burnoutScore['weighted'];

        $leaveScore = $this->analyzeLeavePattern($employee);
        $factors['leave_pattern'] = $leaveScore;
        $score += $leaveScore['weighted'];

        $score = min(100, max(0, round($score)));

        $riskLevel = match (true) {
            $score >= 70 => 'critical',
            $score >= 50 => 'high',
            $score >= 30 => 'medium',
            default => 'low',
        };

        $recommendations = $this->generateRecommendations($employee, $factors, $score);

        return [
            'risk_score' => $score,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommendations' => $recommendations,
            'employee_name' => $employee->first_name . ' ' . ($employee->last_name ?? ''),
            'department' => $employee->department?->name ?? 'N/A',
            'position' => $employee->position?->name ?? 'N/A',
        ];
    }

    public function getTopRisks(int $companyId, int $limit = 10): array
    {
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->with(['department', 'position'])
            ->get();

        $risks = [];
        foreach ($employees as $employee) {
            $result = $this->calculateRisk($employee);
            $risks[] = $result;
        }

        usort($risks, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        return array_slice($risks, 0, $limit);
    }

    public function getDepartmentRiskSummary(int $departmentId): array
    {
        $employees = Employee::where('department_id', $departmentId)
            ->where('status', 'active')
            ->get();

        $risks = [];
        foreach ($employees as $emp) {
            $risks[] = $this->calculateRisk($emp);
        }

        $total = count($risks);
        $critical = count(array_filter($risks, fn($r) => $r['risk_level'] === 'critical'));
        $high = count(array_filter($risks, fn($r) => $r['risk_level'] === 'high'));
        $medium = count(array_filter($risks, fn($r) => $r['risk_level'] === 'medium'));
        $low = count(array_filter($risks, fn($r) => $r['risk_level'] === 'low'));
        $avgScore = $total > 0 ? round(array_sum(array_column($risks, 'risk_score')) / $total, 1) : 0;

        $topRiskFactors = [];
        $factorCounts = [];
        foreach ($risks as $r) {
            foreach ($r['factors'] as $fKey => $fData) {
                if ($fData['risk'] >= 60) {
                    $factorCounts[$fKey] = ($factorCounts[$fKey] ?? 0) + 1;
                }
            }
        }
        arsort($factorCounts);
        $topRiskFactors = array_slice(array_keys($factorCounts), 0, 3);

        return [
            'department_id' => $departmentId,
            'total_employees' => $total,
            'avg_risk_score' => $avgScore,
            'critical' => $critical,
            'high' => $high,
            'medium' => $medium,
            'low' => $low,
            'top_risk_factors' => $topRiskFactors,
            'employees' => $risks,
        ];
    }

    public function generateRetentionPlan(Employee $employee): array
    {
        $risk = $this->calculateRisk($employee);
        $provider = $this->getProvider();

        $factorsSummary = '';
        foreach ($risk['factors'] as $key => $data) {
            $pct = $data['raw'] ?? 0;
            $factorsSummary .= "- {$data['label']}: skor {$pct}%\n";
        }

        $systemPrompt = "Anda adalah konsultan retensi SDM profesional. Berikan rekomendasi retensi spesifik untuk karyawan dalam Bahasa Indonesia. Format sebagai 3-5 rekomendasi actionable dengan poin jelas. Setiap rekomendasi harus punya: (1) tindakan spesifik, (2) alasan, (3) target dampak. Jangan gunakan pembukaan/penutup panjang.";

        $userMessage = "Karyawan: {$risk['employee_name']}\n";
        $userMessage .= "Departemen: {$risk['department']}\n";
        $userMessage .= "Posisi: {$risk['position']}\n";
        $userMessage .= "Skor Risiko: {$risk['risk_score']}/100 ({$risk['risk_level']})\n\n";
        $userMessage .= "Faktor Risiko:\n{$factorsSummary}";

        $narrative = $this->callLlm($provider, $systemPrompt, $userMessage);

        $recommendations = [];
        $lines = explode("\n", trim($narrative));
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);
            $line = preg_replace('/^[-*]\s*/', '', $line);
            if (!empty($line)) {
                $recommendations[] = $line;
            }
        }

        if (empty($recommendations)) {
            $recommendations = $risk['recommendations'];
        }

        return [
            'risk' => $risk,
            'ai_recommendations' => $recommendations,
        ];
    }

    protected function analyzeAttendance(Employee $employee): array
    {
        $now = Carbon::now();
        $threeMonthsAgo = $now->copy()->subMonths(3);

        $totalDays = max(1, $now->diffInWeekdays($threeMonthsAgo));
        $attendances = Attendance::where('employee_id', $employee->id)
            ->where('date', '>=', $threeMonthsAgo)
            ->get();

        $lateCount = $attendances->where('status', 'late')->count();
        $absentCount = max(0, $totalDays - $attendances->count());

        $lateRatePct = min(100, ($lateCount / $totalDays) * 100);
        $absentRatePct = min(100, ($absentCount / $totalDays) * 100);

        $lateTrend = $this->calculateAttendanceTrend($employee);
        $worsening = $lateTrend > 10;

        $raw = $lateRatePct * 0.5 + $absentRatePct * 0.35 + ($worsening ? 15 : 0);
        $raw = min(100, $raw);

        return [
            'raw' => round($raw, 1),
            'weighted' => round($raw * $this->riskWeights['attendance'] / 100, 1),
            'risk' => $raw >= 60 ? 'high' : ($raw >= 30 ? 'medium' : 'low'),
            'label' => 'Pola Kehadiran',
            'details' => [
                'late_count' => $lateCount,
                'late_rate' => round($lateRatePct, 1) . '%',
                'absent_days' => $absentCount,
                'absent_rate' => round($absentRatePct, 1) . '%',
                'worsening_trend' => $worsening,
            ],
        ];
    }

    protected function calculateAttendanceTrend(Employee $employee): float
    {
        $now = Carbon::now();

        $thisMonth = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$now->copy()->startOfMonth(), $now])
            ->where('status', 'late')
            ->count();

        $lastMonth = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])
            ->where('status', 'late')
            ->count();

        if ($lastMonth == 0) return $thisMonth > 0 ? 50 : 0;
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    protected function analyzePerformance(Employee $employee): array
    {
        $reviews = PerformanceReview::where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->orderBy('review_submitted_at', 'desc')
            ->limit(3)
            ->get();

        if ($reviews->isEmpty()) {
            return [
                'raw' => 25,
                'weighted' => round(25 * $this->riskWeights['performance'] / 100, 1),
                'risk' => 'medium',
                'label' => 'Kinerja',
                'details' => ['review_count' => 0, 'trend' => 'Tidak ada data'],
            ];
        }

        $scores = $reviews->pluck('final_score')->toArray();
        $avgScore = array_sum($scores) / count($scores);
        $declining = count($scores) >= 2 && $scores[0] < $scores[1];

        $raw = 0;
        if ($avgScore < 50) $raw = 85;
        elseif ($avgScore < 60) $raw = 65;
        elseif ($avgScore < 70) $raw = 45;
        elseif ($avgScore < 80) $raw = 25;
        else $raw = 10;

        if ($declining) $raw = min(100, $raw + 20);

        $trend = count($scores) >= 2
            ? ($scores[0] < $scores[1] ? 'Menurun' : 'Stabil/Meningkat')
            : 'Stabil';

        return [
            'raw' => round($raw, 1),
            'weighted' => round($raw * $this->riskWeights['performance'] / 100, 1),
            'risk' => $raw >= 60 ? 'high' : ($raw >= 30 ? 'medium' : 'low'),
            'label' => 'Kinerja',
            'details' => [
                'avg_score' => round($avgScore, 1),
                'review_count' => count($scores),
                'trend' => $trend,
            ],
        ];
    }

    protected function analyzeTenure(Employee $employee): array
    {
        $joinDate = $employee->join_date;
        if (!$joinDate) {
            return [
                'raw' => 50,
                'weighted' => round(50 * $this->riskWeights['tenure'] / 100, 1),
                'risk' => 'medium',
                'label' => 'Masa Kerja',
                'details' => ['years' => 0, 'risk_milestone' => 'Tidak diketahui'],
            ];
        }

        $now = Carbon::now();
        $yearsOfService = $joinDate->diffInYears($now);
        $monthsOfService = $joinDate->diffInMonths($now);

        $raw = 40;
        $milestone = '';
        if ($monthsOfService >= 10 && $monthsOfService <= 14) {
            $raw = 55;
            $milestone = '1 tahun (honeymoon phase berakhir)';
        } elseif ($monthsOfService >= 34 && $monthsOfService <= 38) {
            $raw = 70;
            $milestone = '3 tahun (career plateau)';
        } elseif ($monthsOfService >= 58 && $monthsOfService <= 62) {
            $raw = 65;
            $milestone = '5 tahun (explore new opportunities)';
        } elseif ($monthsOfService >= 82) {
            $raw = 50;
            $milestone = '7+ tahun (loyal tapi mungkin stagnan)';
        } elseif ($monthsOfService < 3) {
            $raw = 60;
            $milestone = 'Baru bergabung (masa probation)';
        } else {
            $milestone = 'Periode stabil';
        }

        return [
            'raw' => round($raw, 1),
            'weighted' => round($raw * $this->riskWeights['tenure'] / 100, 1),
            'risk' => $raw >= 60 ? 'high' : ($raw >= 40 ? 'medium' : 'low'),
            'label' => 'Masa Kerja',
            'details' => [
                'years' => $yearsOfService,
                'months' => $monthsOfService,
                'milestone' => $milestone,
            ],
        ];
    }

    protected function analyzeCompensation(Employee $employee): array
    {
        $salary = (float) ($employee->basic_salary ?? 0);
        if ($salary <= 0) {
            return [
                'raw' => 50,
                'weighted' => round(50 * $this->riskWeights['compensation'] / 100, 1),
                'risk' => 'medium',
                'label' => 'Kompensasi',
                'details' => ['salary' => 0, 'market_comparison' => 'Tidak ada data'],
            ];
        }

        $deptAvg = Employee::where('department_id', $employee->department_id)
            ->where('status', 'active')
            ->where('id', '!=', $employee->id)
            ->whereNotNull('basic_salary')
            ->avg('basic_salary') ?? $salary;

        $ratioVsDept = $salary / max(1, $deptAvg);
        $raw = 40;

        if ($ratioVsDept < 0.7) $raw = 85;
        elseif ($ratioVsDept < 0.85) $raw = 65;
        elseif ($ratioVsDept < 1.0) $raw = 45;
        elseif ($ratioVsDept < 1.1) $raw = 25;
        else $raw = 10;

        $lastRaiseDays = PHP_INT_MAX;
        $now = Carbon::now();

        $increaseJournals = \App\Models\Journal::where('company_id', $employee->company_id)
            ->where('status', 'posted')
            ->where('description', 'like', "%{$employee->first_name}%")
            ->where('description', 'like', '%kenaikan%')
            ->orWhere('description', 'like', '%adjustment%')
            ->orderBy('journal_date', 'desc')
            ->limit(3)
            ->get();

        $noRaiseMonths = 18;
        if ($increaseJournals->isNotEmpty()) {
            $lastRaiseDate = Carbon::parse($increaseJournals->first()->journal_date);
            $noRaiseMonths = $now->diffInMonths($lastRaiseDate);
            if ($noRaiseMonths > 18) $raw = min(100, $raw + 25);
            elseif ($noRaiseMonths > 12) $raw = min(100, $raw + 15);
        } else {
            $raw = min(100, $raw + 25);
        }

        return [
            'raw' => round($raw, 1),
            'weighted' => round($raw * $this->riskWeights['compensation'] / 100, 1),
            'risk' => $raw >= 60 ? 'high' : ($raw >= 30 ? 'medium' : 'low'),
            'label' => 'Kompensasi',
            'details' => [
                'salary' => (int) $salary,
                'dept_avg_salary' => (int) $deptAvg,
                'vs_dept_pct' => round(($ratioVsDept - 1) * 100, 1) . '%',
                'no_raise_months' => $noRaiseMonths,
            ],
        ];
    }

    protected function analyzePromotionGap(Employee $employee): array
    {
        $now = Carbon::now();
        $joinDate = $employee->join_date;

        $monthsInPosition = $joinDate ? $joinDate->diffInMonths($now) : 0;

        $raw = 10;
        $gapInfo = 'Baru dalam posisi ini';

        if ($monthsInPosition > 60) {
            $raw = 85;
            $gapInfo = '5+ tahun di posisi yang sama';
        } elseif ($monthsInPosition > 48) {
            $raw = 70;
            $gapInfo = '4+ tahun di posisi yang sama';
        } elseif ($monthsInPosition > 36) {
            $raw = 55;
            $gapInfo = '3+ tahun di posisi yang sama';
        } elseif ($monthsInPosition > 24) {
            $raw = 35;
            $gapInfo = '2+ tahun di posisi yang sama';
        }

        return [
            'raw' => round($raw, 1),
            'weighted' => round($raw * $this->riskWeights['promotion_gap'] / 100, 1),
            'risk' => $raw >= 60 ? 'high' : ($raw >= 30 ? 'medium' : 'low'),
            'label' => 'Celah Promosi',
            'details' => [
                'months_in_position' => $monthsInPosition,
                'years_in_position' => round($monthsInPosition / 12, 1),
                'status' => $gapInfo,
            ],
        ];
    }

    protected function analyzeOvertimeBurnout(Employee $employee): array
    {
        $now = Carbon::now();
        $threeMonthsAgo = $now->copy()->subMonths(3);

        $overtimes = Overtime::where('employee_id', $employee->id)
            ->where('date', '>=', $threeMonthsAgo)
            ->where('status', 'approved')
            ->get();

        $totalMinutes = $overtimes->sum('duration_minutes');
        $totalHours = $totalMinutes / 60;
        $monthlyAvg = $totalHours / 3;

        $highOvertimeMonths = Overtime::where('employee_id', $employee->id)
            ->where('date', '>=', $now->copy()->subMonths(6))
            ->where('status', 'approved')
            ->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as month'), DB::raw('SUM(duration_minutes) / 60 as hours'))
            ->groupBy('month')
            ->having('hours', '>', 40)
            ->get()
            ->count();

        $raw = 10;
        if ($monthlyAvg > 60) $raw = 90;
        elseif ($monthlyAvg > 45) $raw = 70;
        elseif ($monthlyAvg > 30) $raw = 50;
        elseif ($monthlyAvg > 15) $raw = 30;
        elseif ($monthlyAvg > 5) $raw = 15;

        if ($highOvertimeMonths >= 3) $raw = min(100, $raw + 20);

        return [
            'raw' => round($raw, 1),
            'weighted' => round($raw * $this->riskWeights['overtime_burnout'] / 100, 1),
            'risk' => $raw >= 60 ? 'high' : ($raw >= 30 ? 'medium' : 'low'),
            'label' => 'Burnout Lembur',
            'details' => [
                'avg_monthly_ot_hours' => round($monthlyAvg, 1),
                'total_ot_hours_3mo' => round($totalHours, 1),
                'high_ot_months' => $highOvertimeMonths,
            ],
        ];
    }

    protected function analyzeLeavePattern(Employee $employee): array
    {
        $now = Carbon::now();
        $threeMonthsAgo = $now->copy()->subMonths(3);

        $leaves = Leave::where('employee_id', $employee->id)
            ->where('start_date', '>=', $threeMonthsAgo)
            ->where('status', 'approved')
            ->get();

        $totalDays = $leaves->sum('total_days');
        $sickDays = $leaves->where('leave_type_id', function ($q) {
            $q->select('id')->from('leave_types')->where('name', 'like', '%sakit%');
        })->sum('total_days');

        $raw = 10;
        if ($totalDays > 15) $raw = 40;
        elseif ($totalDays > 10) $raw = 30;
        elseif ($totalDays > 5) $raw = 20;

        if ($sickDays > 5) $raw = min(100, $raw + 30);
        elseif ($sickDays > 3) $raw = min(100, $raw + 15);

        return [
            'raw' => round($raw, 1),
            'weighted' => round($raw * $this->riskWeights['leave_pattern'] / 100, 1),
            'risk' => $raw >= 40 ? 'high' : ($raw >= 20 ? 'medium' : 'low'),
            'label' => 'Pola Cuti',
            'details' => [
                'total_leave_days_3mo' => $totalDays,
                'sick_days' => $sickDays,
            ],
        ];
    }

    protected function generateRecommendations(Employee $employee, array $factors, float $score): array
    {
        $recommendations = [];

        if (($factors['compensation']['raw'] ?? 0) >= 60) {
            $recommendations[] = "Review kompensasi — gaji mungkin di bawah standar departemen. Pertimbangkan kenaikan gaji atau tunjangan tambahan.";
        }

        if (($factors['promotion_gap']['raw'] ?? 0) >= 50) {
            $recommendations[] = "Diskusikan rencana karir — {$employee->first_name} sudah lama di posisi yang sama. Tawarkan jalur promosi atau rotasi jabatan.";
        }

        if (($factors['overtime_burnout']['raw'] ?? 0) >= 50) {
            $recommendations[] = "Kurangi beban lembur — rata-rata lembur tinggi dalam 3 bulan terakhir. Evaluasi distribusi beban kerja atau tambah personel.";
        }

        if (($factors['attendance']['raw'] ?? 0) >= 50) {
            $recommendations[] = "Lakukan check-in personal — pola ketidakhadiran meningkat. Ada kemungkinan masalah personal atau demotivasi.";
        }

        if (($factors['performance']['raw'] ?? 0) >= 50) {
            $recommendations[] = "Berikan coaching — performa menurun. Identifikasi kendala dan berikan dukungan pengembangan skill.";
        }

        if (($factors['tenure']['raw'] ?? 0) >= 50) {
            $recommendations[] = "Tawarkan program retensi — {$employee->first_name} berada di milestone karir kritis. Pertimbangkan retensi bonus atau training khusus.";
        }

        if (empty($recommendations)) {
            $recommendations[] = "{$employee->first_name} menunjukkan indikator retensi yang baik. Pertahankan engagement dengan feedback positif dan development plan.";
        }

        return $recommendations;
    }

    protected function callLlm(AiProvider $provider, string $systemPrompt, string $userMessage): string
    {
        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o-mini';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.5,
                    'max_tokens' => 1000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? '';
            }

            Log::error('FlightRisk LLM error', ['status' => $response->status()]);
            return '';
        } catch (ConnectionException $e) {
            Log::error('FlightRisk connection error: ' . $e->getMessage());
            return '';
        }
    }
}

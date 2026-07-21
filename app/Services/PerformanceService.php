<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\KpiIndicator;
use App\Models\KpiTemplate;
use App\Models\PerformanceCycle;
use App\Models\PerformanceFeedback;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewScore;
use Illuminate\Support\Collection;

class PerformanceService
{
    public function calculateScore(PerformanceReview $review): float
    {
        $review->load('scores');
        $scores = $review->scores;

        if ($scores->isEmpty()) {
            return 0;
        }

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($scores as $score) {
            $finalScore = $score->calibration_score ?? $score->reviewer_score ?? $score->employee_score ?? 0;
            $weightedSum += $finalScore * ($score->weight / 100);
            $totalWeight += $score->weight;
        }

        if ($totalWeight <= 0) {
            return 0;
        }

        return round($weightedSum, 2);
    }

    public function getRatingDistribution(PerformanceCycle $cycle): array
    {
        $reviews = $cycle->reviews()->where('status', 'completed')->get();

        $distribution = [
            'A' => ['label' => 'Istimewa', 'count' => 0, 'color' => '#10b981'],
            'B' => ['label' => 'Melampaui Ekspektasi', 'count' => 0, 'color' => '#6366f1'],
            'C' => ['label' => 'Memenuhi Ekspektasi', 'count' => 0, 'color' => '#f59e0b'],
            'D' => ['label' => 'Di Bawah Ekspektasi', 'count' => 0, 'color' => '#f97316'],
            'E' => ['label' => 'Tidak Memuaskan', 'count' => 0, 'color' => '#ef4444'],
        ];

        foreach ($reviews as $review) {
            $rating = $review->rating;
            if (isset($distribution[$rating])) {
                $distribution[$rating]['count']++;
            }
        }

        $total = count($reviews);
        foreach ($distribution as &$item) {
            $item['percent'] = $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0;
        }

        return [
            'distribution' => array_values($distribution),
            'total_reviews' => $total,
            'average_score' => $reviews->avg('final_score') ? round($reviews->avg('final_score'), 2) : 0,
        ];
    }

    public function getRecommendations(PerformanceCycle $cycle): array
    {
        $reviews = $cycle->reviews()->with('employee.department', 'employee.position')->get();

        $promotionCandidates = [];
        $bonusEligible = [];
        $improvementNeeded = [];

        foreach ($reviews as $review) {
            $employee = $review->employee;
            $rating = $review->rating;

            if (in_array($rating, ['A', 'B'])) {
                $promotionCandidates[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . ($employee->last_name ?? ''),
                    'department' => $employee->department?->name,
                    'position' => $employee->position?->name,
                    'score' => $review->final_score,
                    'rating' => $rating,
                    'rating_label' => $review->rating_label,
                    'recommendation' => $rating === 'A' ? 'Promosi atau kenaikan grade' : 'Bonus maksimal',
                ];
            }

            if (in_array($rating, ['A', 'B', 'C'])) {
                $bonusEligible[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . ($employee->last_name ?? ''),
                    'score' => $review->final_score,
                    'rating' => $rating,
                    'bonus' => $this->calculateBonus($review),
                ];
            }

            if (in_array($rating, ['D', 'E'])) {
                $improvementNeeded[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . ($employee->last_name ?? ''),
                    'department' => $employee->department?->name,
                    'score' => $review->final_score,
                    'rating' => $rating,
                    'rating_label' => $review->rating_label,
                    'recommendation' => $rating === 'E' ? 'Performance Improvement Plan (PIP)' : 'Coaching intensif',
                ];
            }
        }

        return [
            'promotion_candidates' => $promotionCandidates,
            'bonus_eligible' => $bonusEligible,
            'improvement_needed' => $improvementNeeded,
        ];
    }

    public function aggregate360Feedback(PerformanceReview $review): array
    {
        $feedback = $review->feedback()->with('fromEmployee')->get();

        $byType = [
            'peer' => ['count' => 0, 'avg_rating' => 0, 'strengths' => [], 'improvements' => []],
            'subordinate' => ['count' => 0, 'avg_rating' => 0, 'strengths' => [], 'improvements' => []],
            'manager' => ['count' => 0, 'avg_rating' => 0, 'strengths' => [], 'improvements' => []],
            'self' => ['count' => 0, 'avg_rating' => 0, 'strengths' => [], 'improvements' => []],
        ];

        foreach ($feedback as $fb) {
            $type = $fb->feedback_type;
            $byType[$type]['count']++;
            $byType[$type]['avg_rating'] += $fb->rating;
            if ($fb->strengths) {
                $byType[$type]['strengths'][] = [
                    'from' => $fb->is_anonymous ? 'Anonim' : ($fb->fromEmployee?->first_name ?? 'Unknown'),
                    'text' => $fb->strengths,
                ];
            }
            if ($fb->improvements) {
                $byType[$type]['improvements'][] = [
                    'from' => $fb->is_anonymous ? 'Anonim' : ($fb->fromEmployee?->first_name ?? 'Unknown'),
                    'text' => $fb->improvements,
                ];
            }
        }

        foreach ($byType as &$type) {
            $type['avg_rating'] = $type['count'] > 0 ? round($type['avg_rating'] / $type['count'], 1) : 0;
        }

        $totalFeedbacks = $feedback->count();
        $overallRating = $totalFeedbacks > 0 ? round($feedback->avg('rating'), 1) : 0;

        return [
            'by_type' => $byType,
            'total_feedbacks' => $totalFeedbacks,
            'overall_rating' => $overallRating,
        ];
    }

    public function generateReport(Employee $employee): array
    {
        $reviews = PerformanceReview::where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->with(['scores.indicator', 'cycle'])
            ->orderBy('created_at', 'desc')
            ->get();

        $latestReview = $reviews->first();

        $bscScores = [
            'financial' => ['label' => 'Keuangan', 'score' => 0, 'weight' => 0, 'color' => '#6366f1'],
            'customer' => ['label' => 'Pelanggan', 'score' => 0, 'weight' => 0, 'color' => '#10b981'],
            'internal_process' => ['label' => 'Proses Internal', 'score' => 0, 'weight' => 0, 'color' => '#f59e0b'],
            'learning_growth' => ['label' => 'Pembelajaran', 'score' => 0, 'weight' => 0, 'color' => '#8b5cf6'],
        ];

        if ($latestReview) {
            foreach ($latestReview->scores as $score) {
                $category = $score->indicator?->category;
                if ($category && isset($bscScores[$category])) {
                    $finalScore = $score->calibration_score ?? $score->reviewer_score ?? $score->employee_score ?? 0;
                    $bscScores[$category]['score'] += $finalScore * ($score->weight / 100);
                    $bscScores[$category]['weight'] += $score->weight;
                }
            }
        }

        $history = [];
        foreach ($reviews as $review) {
            $history[] = [
                'cycle' => $review->cycle?->name,
                'period' => $review->cycle?->period_start?->format('M Y') . ' - ' . $review->cycle?->period_end?->format('M Y'),
                'final_score' => $review->final_score,
                'rating' => $review->rating,
                'rating_label' => $review->rating_label,
            ];
        }

        return [
            'employee' => [
                'name' => $employee->first_name . ' ' . ($employee->last_name ?? ''),
                'department' => $employee->department?->name,
                'position' => $employee->position?->name,
            ],
            'spider_chart' => array_values($bscScores),
            'current_score' => $latestReview?->final_score ?? 0,
            'current_rating' => $latestReview?->rating ?? '-',
            'current_rating_label' => $latestReview?->rating_label ?? '-',
            'history' => $history,
            'trend' => $this->calculateTrend($reviews),
        ];
    }

    private function calculateTrend(Collection $reviews): string
    {
        if ($reviews->count() < 2) {
            return 'stable';
        }

        $latest = $reviews->first()->final_score ?? 0;
        $previous = $reviews->skip(1)->first()->final_score ?? 0;

        if ($latest > $previous + 2) return 'up';
        if ($latest < $previous - 2) return 'down';
        return 'stable';
    }

    public function calculateBonus(PerformanceReview $review): float
    {
        $basicSalary = (float) ($review->employee->basic_salary ?? 0);
        $multiplier = match ($review->rating) {
            'A' => 2.0,
            'B' => 1.5,
            'C' => 1.0,
            'D' => 0.5,
            default => 0,
        };

        return $basicSalary * $multiplier;
    }

    public function startReview(PerformanceCycle $cycle, Employee $employee, Employee $reviewer, KpiTemplate $template): PerformanceReview
    {
        $existing = PerformanceReview::where('cycle_id', $cycle->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $review = PerformanceReview::create([
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'reviewer_id' => $reviewer->id,
            'kpi_template_id' => $template->id,
            'status' => 'self_assessment',
        ]);

        foreach ($template->indicators as $indicator) {
            PerformanceReviewScore::create([
                'review_id' => $review->id,
                'indicator_id' => $indicator->id,
                'weight' => $indicator->weight_percent,
                'employee_score' => null,
                'reviewer_score' => null,
                'calibration_score' => null,
            ]);
        }

        return $review->load('scores.indicator');
    }

    public function submitSelfAssessment(PerformanceReview $review, array $scores): void
    {
        foreach ($scores as $scoreData) {
            $reviewScore = PerformanceReviewScore::where('review_id', $review->id)
                ->where('indicator_id', $scoreData['indicator_id'])
                ->first();

            if ($reviewScore) {
                $reviewScore->update([
                    'employee_score' => $scoreData['score'],
                    'comments' => $scoreData['comments'] ?? $reviewScore->comments,
                ]);
            }
        }

        $selfScore = $this->calculateReviewScore($review, 'employee_score');
        $review->update([
            'employee_self_score' => $selfScore,
            'status' => 'manager_review',
            'self_submitted_at' => now(),
        ]);
    }

    public function submitManagerReview(PerformanceReview $review, array $scores): void
    {
        foreach ($scores as $scoreData) {
            $reviewScore = PerformanceReviewScore::where('review_id', $review->id)
                ->where('indicator_id', $scoreData['indicator_id'])
                ->first();

            if ($reviewScore) {
                $reviewScore->update([
                    'reviewer_score' => $scoreData['score'],
                    'comments' => $scoreData['comments'] ?? $reviewScore->comments,
                ]);
            }
        }

        $reviewerScore = $this->calculateReviewScore($review, 'reviewer_score');
        $review->update([
            'reviewer_score' => $reviewerScore,
            'status' => 'hr_calibration',
            'review_submitted_at' => now(),
        ]);
    }

    public function submitCalibration(PerformanceReview $review, ?array $adjustedScores = null): void
    {
        if ($adjustedScores) {
            foreach ($adjustedScores as $scoreData) {
                $reviewScore = PerformanceReviewScore::where('review_id', $review->id)
                    ->where('indicator_id', $scoreData['indicator_id'])
                    ->first();

                if ($reviewScore) {
                    $reviewScore->update(['calibration_score' => $scoreData['score']]);
                }
            }
        } else {
            foreach ($review->scores as $score) {
                $score->update([
                    'calibration_score' => $score->reviewer_score ?? $score->employee_score,
                ]);
            }
        }

        $calibrationScore = $this->calculateReviewScore($review, 'calibration_score');
        $review->update([
            'calibration_score' => $calibrationScore,
            'final_score' => $calibrationScore,
            'status' => 'completed',
            'calibration_at' => now(),
        ]);
    }

    private function calculateReviewScore(PerformanceReview $review, string $field): float
    {
        $review->load('scores');
        $scores = $review->scores;

        if ($scores->isEmpty()) {
            return 0;
        }

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($scores as $score) {
            $value = $score->{$field} ?? 0;
            if ($value > 0) {
                $weightedSum += $value * ($score->weight / 100);
                $totalWeight += $score->weight;
            }
        }

        return $totalWeight > 0 ? round($weightedSum, 2) : 0;
    }
}

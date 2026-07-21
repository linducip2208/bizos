<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Carbon\Carbon;

class AntiCheatService
{
    public function startQuizSession(Quiz $quiz, User $user): array
    {
        $existingAttempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('employee_id', $user->employee_id)
            ->count();

        $remainingAttempts = $quiz->max_attempts - $existingAttempts;
        if ($remainingAttempts <= 0) {
            throw new \RuntimeException('Anda telah mencapai batas maksimum percobaan untuk kuis ini');
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'employee_id' => $user->employee_id,
            'started_at' => now(),
            'attempt_number' => $existingAttempts + 1,
            'score' => 0,
            'total_points' => 0,
            'earned_points' => 0,
            'is_passed' => false,
            'violation_count' => 0,
            'violation_log' => [],
            'is_auto_submitted' => false,
        ]);

        return [
            'attempt_id' => $attempt->id,
            'quiz_title' => $quiz->title,
            'time_limit_minutes' => $quiz->time_limit_minutes,
            'passing_score' => $quiz->passing_score,
            'max_attempts' => $quiz->max_attempts,
            'remaining_attempts' => $remainingAttempts - 1,
            'warnings_allowed' => 3,
            'rules' => [
                'Dilarang berpindah tab selama kuis berlangsung',
                'Dilarang menyalin teks dari halaman kuis',
                'Kuis akan otomatis terkumpul jika melanggar lebih dari 3 kali',
                'Waktu akan berkurang jika Anda meninggalkan halaman kuis',
                'Fullscreen wajib diaktifkan selama kuis',
            ],
            'fullscreen_required' => true,
            'tab_monitoring' => true,
            'clipboard_monitoring' => true,
        ];
    }

    public function recordViolation(QuizAttempt $attempt, string $type): void
    {
        $violationLog = $attempt->violation_log ?? [];
        $violationLog[] = [
            'type' => $type,
            'timestamp' => now()->toIso8601String(),
            'violation_number' => ($attempt->violation_count ?? 0) + 1,
        ];

        $attempt->update([
            'violation_count' => ($attempt->violation_count ?? 0) + 1,
            'violation_log' => $violationLog,
        ]);
    }

    public function shouldAutoSubmit(QuizAttempt $attempt): bool
    {
        return ($attempt->violation_count ?? 0) >= 3;
    }

    public function autoSubmit(QuizAttempt $attempt): void
    {
        $attempt->update([
            'submitted_at' => now(),
            'is_auto_submitted' => true,
        ]);

        $questions = $attempt->quiz->questions()->with('answers')->get();
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($questions as $question) {
            $totalPoints += $question->points ?? 1;

            $userAnswer = QuizAttemptAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $question->id)
                ->first();

            if ($userAnswer && $this->isCorrectAnswer($question, $userAnswer)) {
                $earnedPoints += $question->points ?? 1;
            }
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $isPassed = $score >= ($attempt->quiz->passing_score ?? 60);

        $attempt->update([
            'score' => $score,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'is_passed' => $isPassed,
        ]);
    }

    public function enforceFullscreen(): string
    {
        return <<<'JS'
        <script>
        (function() {
            function requestFullscreen() {
                const el = document.documentElement;
                if (el.requestFullscreen) {
                    el.requestFullscreen().catch(() => {});
                } else if (el.mozRequestFullScreen) {
                    el.mozRequestFullScreen();
                } else if (el.webkitRequestFullscreen) {
                    el.webkitRequestFullscreen();
                } else if (el.msRequestFullscreen) {
                    el.msRequestFullscreen();
                }
            }

            let violationCount = 0;
            const maxViolations = 3;

            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement) {
                    violationCount++;
                    window.Livewire?.dispatch('quiz-violation', {
                        type: 'fullscreen_exit',
                        count: violationCount
                    });
                    if (violationCount < maxViolations) {
                        alert('Peringatan! Kuis harus dalam mode fullscreen. Pelanggaran: ' + violationCount + '/' + maxViolations);
                        setTimeout(requestFullscreen, 1000);
                    }
                }
            });

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    violationCount++;
                    window.Livewire?.dispatch('quiz-violation', {
                        type: 'tab_switch',
                        count: violationCount
                    });
                }
            });

            document.addEventListener('copy', function(e) {
                e.preventDefault();
                violationCount++;
                window.Livewire?.dispatch('quiz-violation', {
                    type: 'copy_paste',
                    count: violationCount
                });
                alert('Menyalin teks tidak diizinkan selama kuis!');
            });

            document.addEventListener('paste', function(e) {
                e.preventDefault();
                violationCount++;
                window.Livewire?.dispatch('quiz-violation', {
                    type: 'copy_paste',
                    count: violationCount
                });
                alert('Menempel teks tidak diizinkan selama kuis!');
            });

            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });

            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && (e.key === 'u' || e.key === 's' || e.key === 'p' || e.key === 'i')) {
                    e.preventDefault();
                }
                if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                    e.preventDefault();
                }
            });

            requestFullscreen();

            window.addEventListener('beforeunload', function(e) {
                if (violationCount < maxViolations) {
                    e.preventDefault();
                    e.returnValue = 'Anda masih dalam sesi kuis. Yakin ingin meninggalkan halaman?';
                }
            });
        })();
        </script>
        JS;
    }

    protected function isCorrectAnswer($question, $userAnswer): bool
    {
        if ($question->question_type === 'multiple_choice' || $question->question_type === 'single_choice') {
            $correctAnswer = $question->answers()->where('is_correct', true)->first();
            if ($correctAnswer) {
                return (int) $userAnswer->answer === (int) $correctAnswer->id;
            }
        }

        if ($question->question_type === 'essay') {
            return $userAnswer->score !== null && $userAnswer->score >= ($question->points ?? 1) * 0.6;
        }

        return false;
    }
}

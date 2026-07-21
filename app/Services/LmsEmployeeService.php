<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Competency;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Employee;
use App\Models\EmployeeCompetency;
use App\Models\EmployeeDocument;
use App\Models\PositionCompetency;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;

class LmsEmployeeService
{
    /**
     * Saat kursus selesai → update employee skills.
     */
    public function onCourseCompleted(CourseEnrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment) {
            $enrollment->load('course', 'employee.position');

            $employee = $enrollment->employee;
            $course = $enrollment->course;

            if (!$employee || !$course) return;

            $score = $this->assessCompetencyLevel($enrollment);

            $competency = $this->findOrCreateCompetencyFromCourse($course);

            $existing = EmployeeCompetency::where('employee_id', $employee->id)
                ->where('competency_id', $competency->id)
                ->first();

            if ($existing) {
                $newLevel = max($existing->current_level, $score);
                $existing->update([
                    'current_level' => $newLevel,
                    'assessed_by' => auth()->id(),
                    'assessed_at' => now(),
                    'notes' => 'Update dari penyelesaian kursus: ' . $course->title . ' (level ' . $score . ')',
                ]);
            } else {
                EmployeeCompetency::create([
                    'employee_id' => $employee->id,
                    'competency_id' => $competency->id,
                    'current_level' => $score,
                    'assessed_by' => auth()->id(),
                    'assessed_at' => now(),
                    'notes' => 'Diperoleh dari penyelesaian kursus: ' . $course->title,
                ]);
            }

            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'progress_percent' => 100,
            ]);

            $this->issueCertificate($enrollment);
        });
    }

    /**
     * Issue sertifikat → link ke employee.
     */
    public function onCertificateIssued(Certificate $certificate): void
    {
        DB::transaction(function () use ($certificate) {
            $enrollment = $certificate->enrollment()->with('employee')->first();
            if (!$enrollment || !$enrollment->employee) return;

            $employee = $enrollment->employee;

            $certificate->update(['employee_id' => $employee->id]);

            $existingDoc = EmployeeDocument::where('employee_id', $employee->id)
                ->where('document_type', 'certificate')
                ->where('document_name', 'like', '%' . $certificate->certificate_number . '%')
                ->first();

            if (!$existingDoc && $certificate->pdf_path) {
                EmployeeDocument::create([
                    'employee_id' => $employee->id,
                    'document_type' => 'certificate',
                    'document_name' => 'Sertifikat: ' . ($enrollment->course->title ?? 'Kursus') . ' (' . $certificate->certificate_number . ')',
                    'file_path' => $certificate->pdf_path,
                    'issue_date' => $certificate->issued_date,
                    'notes' => 'Auto-generated dari LMS',
                    'verification_status' => 'verified',
                ]);
            }
        });
    }

    /**
     * Training gap analysis: bandingkan requirement posisi vs kompetensi employee.
     * Returns: [{competency, required_level, current_level, gap, recommended_courses}]
     */
    public function getTrainingGap(Employee $employee): array
    {
        $employee->load('position', 'employeeCompetencies.competency');

        $position = $employee->position;
        if (!$position) {
            return [];
        }

        $positionCompetencies = PositionCompetency::where('position_id', $position->id)
            ->with('competency')
            ->get();

        $employeeCompetencies = $employee->employeeCompetencies->keyBy('competency_id');

        $gaps = [];

        foreach ($positionCompetencies as $posComp) {
            $competency = $posComp->competency;
            $requiredLevel = (int) $posComp->required_level;
            $currentLevel = 0;

            $empComp = $employeeCompetencies->get($posComp->competency_id);
            if ($empComp) {
                $currentLevel = (int) $empComp->current_level;
            }

            $gap = $requiredLevel - $currentLevel;

            $recommendedCourses = [];
            if ($gap > 0) {
                $recommendedCourses = Course::where('category', $competency->category ?? '')
                    ->where('is_published', true)
                    ->pluck('title', 'id')
                    ->toArray();

                if (empty($recommendedCourses)) {
                    $recommendedCourses = Course::where('is_published', true)
                        ->where(function ($q) use ($competency) {
                            $q->where('title', 'like', '%' . $competency->name . '%')
                                ->orWhere('description', 'like', '%' . $competency->name . '%');
                        })
                        ->pluck('title', 'id')
                        ->toArray();
                }
            }

            $gaps[] = [
                'competency_id' => $competency->id,
                'competency' => $competency->name,
                'category' => $competency->category,
                'required_level' => $requiredLevel,
                'current_level' => $currentLevel,
                'gap' => $gap,
                'weight' => (float) $posComp->weight,
                'severity' => $gap >= 3 ? 'kritis' : ($gap >= 2 ? 'sedang' : ($gap >= 1 ? 'ringan' : 'kompeten')),
                'recommended_courses' => $recommendedCourses,
            ];
        }

        usort($gaps, function ($a, $b) {
            return $b['gap'] - $a['gap'];
        });

        return $gaps;
    }

    /**
     * Auto-enroll employee ke kursus berdasarkan competency gap.
     */
    public function autoEnrollForGap(Employee $employee): array
    {
        $gaps = $this->getTrainingGap($employee);
        $enrollments = [];

        foreach ($gaps as $gap) {
            if ($gap['gap'] <= 0) continue;
            if (empty($gap['recommended_courses'])) continue;

            foreach ($gap['recommended_courses'] as $courseId => $courseTitle) {
                $alreadyEnrolled = CourseEnrollment::where('employee_id', $employee->id)
                    ->where('course_id', $courseId)
                    ->exists();

                if ($alreadyEnrolled) continue;

                $enrollment = CourseEnrollment::create([
                    'course_id' => $courseId,
                    'employee_id' => $employee->id,
                    'enrolled_at' => now(),
                    'progress_percent' => 0,
                    'status' => 'enrolled',
                ]);

                $enrollments[] = [
                    'enrollment_id' => $enrollment->id,
                    'course_title' => $courseTitle,
                    'competency' => $gap['competency'],
                    'gap' => $gap['gap'],
                ];

                break;
            }
        }

        return $enrollments;
    }

    /**
     * Compliance training tracking per department.
     * Returns: {employee, mandatory_courses_completed, required_courses, compliance_percent}
     */
    public function getComplianceStatus(int $departmentId): array
    {
        $employees = Employee::where('department_id', $departmentId)
            ->where('status', 'active')
            ->with(['position.positionCompetencies.competency', 'courseEnrollments.course'])
            ->get();

        $result = [];

        foreach ($employees as $employee) {
            $position = $employee->position;
            $requiredCompetencies = $position
                ? $position->positionCompetencies->pluck('competency_id')->unique()->count()
                : 0;

            if ($requiredCompetencies === 0) {
                $result[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'position' => $position->name ?? '-',
                    'mandatory_courses_completed' => 0,
                    'required_courses' => 0,
                    'compliance_percent' => 100,
                ];
                continue;
            }

            $completed = $employee->courseEnrollments
                ->where('status', 'completed')
                ->count();

            $compliancePercent = $requiredCompetencies > 0
                ? round(($completed / $requiredCompetencies) * 100, 1)
                : 100;

            $result[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'position' => $position->name ?? '-',
                'mandatory_courses_completed' => $completed,
                'required_courses' => $requiredCompetencies,
                'compliance_percent' => min(100, $compliancePercent),
            ];
        }

        usort($result, function ($a, $b) {
            return $a['compliance_percent'] - $b['compliance_percent'];
        });

        $totalEmployees = count($result);
        $compliantCount = count(array_filter($result, fn($r) => $r['compliance_percent'] >= 80));

        return [
            'department_id' => $departmentId,
            'total_employees' => $totalEmployees,
            'compliant_employees' => $compliantCount,
            'overall_compliance' => $totalEmployees > 0 ? round(($compliantCount / $totalEmployees) * 100, 1) : 100,
            'employees' => $result,
        ];
    }

    /**
     * Dapatkan semua sertifikat milik employee.
     */
    public function getEmployeeCertificates(Employee $employee): array
    {
        return Certificate::where('employee_id', $employee->id)
            ->with('enrollment.course')
            ->orderBy('issued_date', 'desc')
            ->get()
            ->map(function ($cert) {
                return [
                    'certificate_number' => $cert->certificate_number,
                    'course' => $cert->enrollment->course->title ?? '-',
                    'issued_date' => $cert->issued_date?->format('Y-m-d'),
                    'uuid' => $cert->uuid,
                    'pdf_path' => $cert->pdf_path,
                ];
            })
            ->toArray();
    }

    /**
     * Dapatkan ringkasan skills matrix employee.
     */
    public function getSkillMatrix(Employee $employee): array
    {
        $employee->load('employeeCompetencies.competency', 'position.positionCompetencies.competency');

        $skills = [];

        $competencies = $employee->employeeCompetencies;
        foreach ($competencies as $empComp) {
            $competency = $empComp->competency;
            if (!$competency) continue;

            $skills[] = [
                'competency_id' => $competency->id,
                'name' => $competency->name,
                'category' => $competency->category,
                'level' => (int) $empComp->current_level,
                'max_level' => count($competency->proficiency_levels ?? []),
                'assessed_at' => $empComp->assessed_at?->format('Y-m-d'),
            ];
        }

        usort($skills, function ($a, $b) {
            return $b['level'] - $a['level'];
        });

        return $skills;
    }

    /**
     * Registrasi student eksternal (non-employee).
     */
    public function registerStudent(array $data): Student
    {
        return Student::create($data);
    }

    /**
     * Enroll student ke kursus.
     */
    public function enrollStudent(int $studentId, int $courseId): StudentEnrollment
    {
        return StudentEnrollment::firstOrCreate(
            ['student_id' => $studentId, 'course_id' => $courseId],
            [
                'student_id' => $studentId,
                'course_id' => $courseId,
                'enrolled_at' => now(),
                'status' => 'enrolled',
            ]
        );
    }

    /**
     * Selesaikan enrollment student.
     */
    public function completeStudentCourse(StudentEnrollment $enrollment): void
    {
        $enrollment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Dapatkan semua kursus student.
     */
    public function getStudentCourses(Student $student): array
    {
        $enrollments = StudentEnrollment::where('student_id', $student->id)
            ->with('course')
            ->get();

        return $enrollments->map(fn($e) => [
            'enrollment_id' => $e->id,
            'course_id' => $e->course_id,
            'course_title' => $e->course->title ?? '-',
            'status' => $e->status,
            'enrolled_at' => $e->enrolled_at?->format('Y-m-d'),
            'completed_at' => $e->completed_at?->format('Y-m-d'),
        ])->toArray();
    }

    /**
     * Statistik enrollment student.
     */
    public function getStudentStats(Student $student): array
    {
        $total = StudentEnrollment::where('student_id', $student->id)->count();
        $completed = StudentEnrollment::where('student_id', $student->id)
            ->where('status', 'completed')->count();
        $active = StudentEnrollment::where('student_id', $student->id)
            ->where('status', 'enrolled')->count();

        return [
            'student_id' => $student->id,
            'total_courses' => $total,
            'completed' => $completed,
            'active' => $active,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Buat assignment untuk kursus.
     */
    public function createAssignment(array $data): Assignment
    {
        return Assignment::create($data);
    }

    /**
     * Submit jawaban assignment oleh student.
     */
    public function submitAssignment(int $assignmentId, int $studentId, array $data): AssignmentSubmission
    {
        return AssignmentSubmission::create([
            'assignment_id' => $assignmentId,
            'student_id' => $studentId,
            'content' => $data['content'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
    }

    /**
     * Grading assignment submission.
     */
    public function gradeSubmission(AssignmentSubmission $submission, float $score, ?string $feedback = null): void
    {
        $assignment = $submission->assignment;
        $passed = $score >= ($assignment->passing_score ?? 60);

        $submission->update([
            'score' => $score,
            'feedback' => $feedback,
            'graded_at' => now(),
            'status' => $passed ? 'passed' : 'failed',
        ]);
    }

    /**
     * Dapatkan assignment per kursus.
     */
    public function getCourseAssignments(Course $course): Collection
    {
        return Assignment::where('course_id', $course->id)
            ->where('is_active', true)
            ->withCount('submissions')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Nilai rata-rata assignment student.
     */
    public function getStudentAssignmentAverage(Student $student, ?int $courseId = null): array
    {
        $query = AssignmentSubmission::where('student_id', $student->id)
            ->whereNotNull('score')
            ->with('assignment.course');

        if ($courseId) {
            $query->whereHas('assignment', fn($q) => $q->where('course_id', $courseId));
        }

        $submissions = $query->get();

        $total = $submissions->count();
        $avgScore = $total > 0 ? round($submissions->avg('score'), 1) : 0;
        $passed = $submissions->where('status', 'passed')->count();

        return [
            'student_id' => $student->id,
            'total_submissions' => $total,
            'average_score' => $avgScore,
            'passed_count' => $passed,
            'failed_count' => $total - $passed,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Cek assignment yang sudah lewat deadline.
     */
    public function getOverdueAssignments(int $studentId): Collection
    {
        return Assignment::where('is_active', true)
            ->where('due_date', '<', now())
            ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $studentId))
            ->with('course')
            ->get()
            ->map(fn($a) => [
                'assignment_id' => $a->id,
                'title' => $a->title,
                'course' => $a->course->title ?? '-',
                'due_date' => $a->due_date?->format('Y-m-d H:i'),
                'max_score' => $a->max_score,
                'days_overdue' => $a->due_date ? now()->diffInDays($a->due_date) : 0,
            ]);
    }

    /**
     * Dapatkan semua student aktif.
     */
    public function getActiveStudents(int $companyId): Collection
    {
        return Student::where('company_id', $companyId)
            ->where('status', 'active')
            ->withCount(['enrollments', 'submissions'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Progress board: ringkasan progres semua student dalam kursus.
     */
    public function getCourseProgressBoard(int $courseId): array
    {
        $enrollments = StudentEnrollment::where('course_id', $courseId)
            ->with('student')
            ->get();

        $assignmentCount = Assignment::where('course_id', $courseId)
            ->where('is_active', true)
            ->count();

        $board = [];
        foreach ($enrollments as $enrollment) {
            $submittedCount = AssignmentSubmission::whereHas('assignment', fn($q) => $q->where('course_id', $courseId))
                ->where('student_id', $enrollment->student_id)
                ->count();

            $gradedCount = AssignmentSubmission::whereHas('assignment', fn($q) => $q->where('course_id', $courseId))
                ->where('student_id', $enrollment->student_id)
                ->whereNotNull('score')
                ->count();

            $avgScore = AssignmentSubmission::whereHas('assignment', fn($q) => $q->where('course_id', $courseId))
                ->where('student_id', $enrollment->student_id)
                ->whereNotNull('score')
                ->avg('score');

            $board[] = [
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->name ?? '-',
                'status' => $enrollment->status,
                'assignments_submitted' => $submittedCount,
                'assignments_graded' => $gradedCount,
                'total_assignments' => $assignmentCount,
                'progress_percent' => $assignmentCount > 0
                    ? round(($gradedCount / $assignmentCount) * 100, 1)
                    : 0,
                'average_score' => $avgScore ? round((float) $avgScore, 1) : null,
                'enrolled_at' => $enrollment->enrolled_at?->format('Y-m-d'),
                'completed_at' => $enrollment->completed_at?->format('Y-m-d'),
            ];
        }

        usort($board, fn($a, $b) => $b['progress_percent'] <=> $a['progress_percent']);

        return [
            'course_id' => $courseId,
            'total_students' => count($board),
            'total_assignments' => $assignmentCount,
            'students' => $board,
        ];
    }

    protected function assessCompetencyLevel(CourseEnrollment $enrollment): int
    {
        $progress = (float) $enrollment->progress_percent;

        return match (true) {
            $progress >= 90 => 4,
            $progress >= 75 => 3,
            $progress >= 50 => 2,
            $progress >= 25 => 1,
            default => 1,
        };
    }

    protected function findOrCreateCompetencyFromCourse(Course $course): Competency
    {
        $competency = Competency::where('name', $course->title)->first();

        if (!$competency) {
            $competency = Competency::where('name', 'like', '%' . $course->title . '%')->first();
        }

        if (!$competency) {
            $competency = Competency::create([
                'company_id' => $course->company_id,
                'name' => $course->title,
                'category' => $course->category ?? 'Teknis',
                'description' => 'Kompetensi dari kursus: ' . $course->title,
                'proficiency_levels' => ['Pemula', 'Dasar', 'Menengah', 'Mahir', 'Ahli'],
            ]);
        }

        return $competency;
    }

    protected function issueCertificate(CourseEnrollment $enrollment): void
    {
        $certService = app(CertificateService::class);
        $certService->generate($enrollment);
    }
}

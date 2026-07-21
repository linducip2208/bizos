<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    public function generate(CourseEnrollment $enrollment): Certificate
    {
        if ($enrollment->status !== 'completed') {
            throw new \RuntimeException('Sertifikat hanya dapat dibuat untuk kursus yang sudah selesai');
        }

        $existing = Certificate::where('enrollment_id', $enrollment->id)->first();
        if ($existing) {
            return $existing;
        }

        $enrollment->load(['course', 'employee']);
        $course = $enrollment->course;
        $employee = $enrollment->employee;

        $uuid = (string) Str::uuid();
        $certificateNumber = $this->generateCertificateNumber($course, $enrollment);

        $pdfPath = $this->generatePdf($course, $employee, $certificateNumber, $uuid, $enrollment);

        $certificate = Certificate::create([
            'enrollment_id' => $enrollment->id,
            'certificate_number' => $certificateNumber,
            'issued_date' => now()->toDateString(),
            'uuid' => $uuid,
            'pdf_path' => $pdfPath,
        ]);

        $enrollment->update(['certificate_issued' => true]);

        return $certificate;
    }

    public function verify(string $uuid): array
    {
        $certificate = Certificate::with(['enrollment.course', 'enrollment.employee'])
            ->where('uuid', $uuid)
            ->first();

        if (!$certificate) {
            return [
                'valid' => false,
                'message' => 'Sertifikat tidak ditemukan atau tidak valid',
            ];
        }

        return [
            'valid' => true,
            'certificate_number' => $certificate->certificate_number,
            'issued_date' => $certificate->issued_date,
            'course_title' => $certificate->enrollment->course->title ?? '',
            'employee_name' => trim(($certificate->enrollment->employee->first_name ?? '') . ' ' . ($certificate->enrollment->employee->last_name ?? '')),
            'completed_at' => $certificate->enrollment->completed_at?->format('d F Y'),
            'verified_at' => now()->toIso8601String(),
        ];
    }

    public function bulkGenerate(Course $course): int
    {
        $enrollments = CourseEnrollment::where('course_id', $course->id)
            ->where('status', 'completed')
            ->where('certificate_issued', false)
            ->get();

        $count = 0;
        foreach ($enrollments as $enrollment) {
            try {
                $this->generate($enrollment);
                $count++;
            } catch (\Exception $e) {
                \Log::error('Certificate bulk generate failed', [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    public function getCertificateUrl(Certificate $certificate): string
    {
        return route('lms.certificate.verify', ['uuid' => $certificate->uuid]);
    }

    public function getQrCodeContent(Certificate $certificate): string
    {
        return $this->getCertificateUrl($certificate);
    }

    protected function generatePdf(Course $course, $employee, string $certificateNumber, string $uuid, CourseEnrollment $enrollment): string
    {
        $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
        $verifyUrl = url('/lms/verify/' . $uuid);
        $qrData = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($verifyUrl);

        $html = view('lms.certificate', [
            'course' => $course,
            'employee_name' => $employeeName,
            'certificate_number' => $certificateNumber,
            'issued_date' => now()->translatedFormat('d F Y'),
            'verify_url' => $verifyUrl,
            'qr_url' => $qrData,
            'uuid' => $uuid,
            'company_name' => $course->company->name ?? 'BizOS',
        ])->render();

        $filename = 'certificates/' . $course->id . '/' . $uuid . '.pdf';
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    protected function generateCertificateNumber(Course $course, CourseEnrollment $enrollment): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $course->title ?? 'CRS'), 0, 4));
        $date = now()->format('Ymd');
        $count = Certificate::count() + 1;

        return $prefix . '-' . $date . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public function getAllCertificatesForEmployee(int $employeeId): array
    {
        $certificates = Certificate::whereHas('enrollment', function ($q) use ($employeeId) {
            $q->where('employee_id', $employeeId);
        })
            ->with(['enrollment.course'])
            ->orderBy('issued_date', 'desc')
            ->get();

        return $certificates->map(function ($cert) {
            return [
                'certificate_number' => $cert->certificate_number,
                'course_title' => $cert->enrollment->course->title ?? '',
                'issued_date' => $cert->issued_date,
                'uuid' => $cert->uuid,
                'pdf_path' => $cert->pdf_path,
                'verify_url' => $this->getCertificateUrl($cert),
            ];
        })->toArray();
    }
}

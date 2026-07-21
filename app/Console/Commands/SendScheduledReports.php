<?php

namespace App\Console\Commands;

use App\Models\ReportSchedule;
use App\Models\ReportSnapshot;
use App\Services\ReportBuilderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendScheduledReports extends Command
{
    protected $signature = 'report:send-scheduled';

    protected $description = 'Send scheduled reports via email based on frequency and time';

    public function handle(ReportBuilderService $service): int
    {
        $now = now();
        $currentTime = $now->format('H:i');
        $currentDayOfWeek = $now->dayOfWeek;
        $currentDayOfMonth = $now->day;

        $schedules = ReportSchedule::with('reportTemplate')
            ->where('is_active', true)
            ->where('time_of_day', '<=', $currentTime)
            ->get()
            ->filter(function (ReportSchedule $schedule) use ($currentDayOfWeek, $currentDayOfMonth) {
                if ($schedule->last_sent_at && $schedule->last_sent_at->isToday()) {
                    return false;
                }

                return match ($schedule->frequency) {
                    'daily' => true,
                    'weekly' => $schedule->day_of_week === $currentDayOfWeek,
                    'monthly' => $schedule->day_of_month === $currentDayOfMonth,
                    default => false,
                };
            });

        if ($schedules->isEmpty()) {
            $this->info('No scheduled reports to send at this time.');

            return Command::SUCCESS;
        }

        foreach ($schedules as $schedule) {
            $this->info("Processing schedule: {$schedule->name} ({$schedule->reportTemplate->name})");

            try {
                $data = $service->execute($schedule->reportTemplate);
                $chartData = null;

                if (in_array($schedule->reportTemplate->query_type, ['chart', 'summary'])) {
                    $chartData = $service->generateChartData($schedule->reportTemplate);
                }

                $fileName = \Illuminate\Support\Str::slug($schedule->reportTemplate->name)
                    . '_' . now()->format('Ymd_His')
                    . '.' . match ($schedule->format) {
                        'pdf' => 'pdf',
                        'excel' => 'xlsx',
                        'csv' => 'csv',
                        default => 'pdf',
                    };

                $tempPath = storage_path('app/temp/' . $fileName);
                if (!is_dir(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0755, true);
                }

                $filePath = null;
                $fileSize = 0;

                if ($schedule->format === 'pdf') {
                    $html = view('reports.pdf-template', [
                        'template' => $schedule->reportTemplate,
                        'data' => $data,
                        'chartData' => $chartData,
                        'generatedAt' => now()->format('d M Y H:i'),
                    ])->render();

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                    $pdf->setPaper('a4', 'landscape');
                    $pdf->save($tempPath);
                    $filePath = $tempPath;
                    $fileSize = filesize($tempPath);
                } elseif ($schedule->format === 'excel') {
                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    if ($data->isNotEmpty()) {
                        $headers = array_keys((array) $data->first());
                        $col = 'A';
                        foreach ($headers as $header) {
                            $sheet->setCellValue($col . '1', ucwords(str_replace('_', ' ', $header)));
                            $col++;
                        }

                        $row = 2;
                        foreach ($data as $record) {
                            $record = (array) $record;
                            $col = 'A';
                            foreach ($record as $value) {
                                $sheet->setCellValue($col . $row, $value);
                                $col++;
                            }
                            $row++;
                        }
                    }

                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $writer->save($tempPath);
                    $filePath = $tempPath;
                    $fileSize = filesize($tempPath);
                } elseif ($schedule->format === 'csv') {
                    $handle = fopen($tempPath, 'w');
                    if ($data->isNotEmpty()) {
                        fputcsv($handle, array_keys((array) $data->first()));
                        foreach ($data as $record) {
                            fputcsv($handle, (array) $record);
                        }
                    }
                    fclose($handle);
                    $filePath = $tempPath;
                    $fileSize = filesize($tempPath);
                }

                ReportSnapshot::create([
                    'report_template_id' => $schedule->report_template_id,
                    'generated_by' => null,
                    'snapshot_data' => $data->toArray(),
                    'format' => $schedule->format,
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'created_at' => now(),
                ]);

                $recipients = $schedule->recipients ?? [];
                if (!empty($recipients)) {
                    Mail::html(
                        view('emails.scheduled-report', [
                            'schedule' => $schedule,
                            'data' => $data->take(10),
                        ])->render(),
                        function ($message) use ($recipients, $schedule, $tempPath, $fileName) {
                            $message->to($recipients)
                                ->subject("Laporan Terjadwal: {$schedule->reportTemplate->name} - " . now()->format('d M Y'));

                            if (file_exists($tempPath)) {
                                $mimeType = match ($schedule->format) {
                                    'pdf' => 'application/pdf',
                                    'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'csv' => 'text/csv',
                                    default => 'application/octet-stream',
                                };
                                $message->attach($tempPath, [
                                    'as' => $fileName,
                                    'mime' => $mimeType,
                                ]);
                            }
                        }
                    );

                    if ($schedule->format !== 'csv') {
                        $this->info("  Emailed to: " . implode(', ', $recipients));
                    }
                }

                $schedule->update(['last_sent_at' => now()]);

                $this->info("  Done: {$fileName}");

            } catch (\Exception $e) {
                $this->error("  Failed: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}

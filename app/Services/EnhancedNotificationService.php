<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Collection;

class EnhancedNotificationService
{
    protected array $dndHours = ['start' => 22, 'end' => 6];

    public function send(Notification $notification, array $channels = ['in_app']): void
    {
        $user = User::find($notification->user_id);

        if (!$user || !$user->is_active) {
            return;
        }

        foreach ($channels as $channel) {
            if (!$this->shouldSend($user, $channel, $notification->notification_type)) {
                continue;
            }

            if ($channel === 'in_app') {
                $this->sendInApp($notification);
            } elseif ($channel === 'email') {
                $this->sendEmail($user, $notification);
            } elseif ($channel === 'whatsapp') {
                $this->sendWhatsapp($user, $notification);
            }
        }
    }

    public function sendForModule(string $module, string $event, array $data): void
    {
        $notificationType = "{$module}.{$event}";
        $template = NotificationTemplate::where('slug', $notificationType)->first();

        $title = $template?->title ?? $this->getDefaultTitle($module, $event);
        $body = $template?->body ?? $this->resolveBody($module, $event, $data);

        if ($template) {
            foreach ($data as $key => $value) {
                if (is_scalar($value)) {
                    $title = str_replace("{{$key}}", $value, $title);
                    $body = str_replace("{{$key}}", $value, $body);
                }
            }
        }

        $channel = $template?->channel ?? 'in_app';
        $recipients = $this->getRecipients($module, $event, $data);

        foreach ($recipients as $userId) {
            $notification = Notification::create([
                'user_id' => $userId,
                'notification_type' => $notificationType,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'channel' => $channel,
                'sent_at' => now(),
            ]);

            $this->send($notification, [$channel]);
        }
    }

    public function getUserPreferences(User $user): array
    {
        return [
            'channels' => [
                'in_app' => true,
                'email' => !empty($user->email),
                'whatsapp' => !empty($user->employee?->phone),
            ],
            'events' => $this->getDefaultEventPreferences(),
            'dnd_enabled' => false,
            'dnd_hours' => $this->dndHours,
        ];
    }

    public function shouldSend(User $user, string $channel, string $event): bool
    {
        $preferences = $this->getUserPreferences($user);

        if (!($preferences['channels'][$channel] ?? true)) {
            return false;
        }

        if (!($preferences['events'][$event] ?? true)) {
            return false;
        }

        if ($preferences['dnd_enabled'] ?? false) {
            $hour = (int) now()->format('H');
            $start = $preferences['dnd_hours']['start'] ?? $this->dndHours['start'];
            $end = $preferences['dnd_hours']['end'] ?? $this->dndHours['end'];

            if ($start > $end) {
                if ($hour >= $start || $hour < $end) {
                    return false;
                }
            } else {
                if ($hour >= $start && $hour < $end) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function sendInApp(Notification $notification): void
    {
        $notification->update([
            'channel' => 'in_app',
            'sent_at' => now(),
        ]);
    }

    protected function sendEmail(User $user, Notification $notification): void
    {
        if (empty($user->email)) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::raw($notification->body, function ($message) use ($user, $notification) {
                $message->to($user->email)
                    ->subject($notification->title);
            });

            $notification->update(['channel' => 'email']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send email notification', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendWhatsapp(User $user, Notification $notification): void
    {
        $phone = $user->employee?->phone;

        if (empty($phone)) {
            return;
        }

        $whatsappService = app(\App\Services\HelpdeskService::class);

        try {
            if (method_exists($whatsappService, 'sendWhatsAppMessage')) {
                $whatsappService->sendWhatsAppMessage($phone, "{$notification->title}\n\n{$notification->body}");
            }

            $notification->update(['channel' => 'whatsapp']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send WhatsApp notification', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getRecipients(string $module, string $event, array $data): array
    {
        $recipients = $data['recipients'] ?? [];

        if (!empty($recipients)) {
            return is_array($recipients) ? $recipients : [$recipients];
        }

        $userIds = [];

        if (isset($data['employee_id'])) {
            $user = User::where('employee_id', $data['employee_id'])->first();
            if ($user) {
                $userIds[] = $user->id;
            }
        }

        if (isset($data['user_id'])) {
            $userIds[] = (int) $data['user_id'];
        }

        if (empty($userIds)) {
            $admins = User::whereHas('role', function ($q) {
                $q->whereIn('slug', ['super-admin', 'admin']);
            })->where('is_active', true)->pluck('id')->toArray();

            $userIds = $admins;
        }

        return array_unique($userIds);
    }

    protected function getDefaultTitle(string $module, string $event): string
    {
        $titles = [
            'leave.created' => 'Pengajuan Cuti Baru',
            'leave.approved' => 'Cuti Disetujui',
            'leave.rejected' => 'Cuti Ditolak',
            'attendance.late' => 'Notifikasi Keterlambatan',
            'attendance.absent' => 'Notifikasi Ketidakhadiran',
            'invoice.overdue' => 'Faktur Jatuh Tempo',
            'invoice.paid' => 'Pembayaran Diterima',
            'ticket.created' => 'Tiket Baru Dibuat',
            'ticket.assigned' => 'Tiket Ditugaskan',
            'ticket.breached' => 'Tiket Melewati SLA',
            'ticket.resolved' => 'Tiket Diselesaikan',
            'reimbursement.created' => 'Pengajuan Reimbursement Baru',
            'reimbursement.approved' => 'Reimbursement Disetujui',
            'reimbursement.rejected' => 'Reimbursement Ditolak',
            'overtime.created' => 'Pengajuan Lembur Baru',
            'overtime.approved' => 'Lembur Disetujui',
            'project.created' => 'Proyek Baru Dibuat',
            'task.assigned' => 'Tugas Baru Ditugaskan',
            'task.completed' => 'Tugas Diselesaikan',
            'task.overdue' => 'Tugas Melewati Deadline',
            'payroll.generated' => 'Slip Gaji Tersedia',
            'announcement.published' => 'Pengumuman Baru',
        ];

        return $titles["{$module}.{$event}"] ?? "Notifikasi {$module} - {$event}";
    }

    protected function resolveBody(string $module, string $event, array $data): string
    {
        $employeeName = $data['employee_name'] ?? 'Karyawan';

        return match ("{$module}.{$event}") {
            'leave.created' => "{$employeeName} telah mengajukan cuti. Mohon untuk direview.",
            'leave.approved' => "Pengajuan cuti Anda telah disetujui.",
            'leave.rejected' => "Pengajuan cuti Anda ditolak. Alasan: " . ($data['rejection_reason'] ?? '-'),
            'attendance.late' => "Anda tercatat terlambat hari ini. Keterlambatan: {$data['late_minutes']} menit.",
            'attendance.absent' => "Anda tercatat tidak hadir hari ini.",
            'invoice.overdue' => "Faktur #{$data['invoice_number']} telah melewati tanggal jatuh tempo.",
            'invoice.paid' => "Pembayaran untuk faktur #{$data['invoice_number']} telah diterima.",
            'ticket.created' => "Tiket baru #{$data['ticket_number']} telah dibuat: {$data['subject']}",
            'ticket.assigned' => "Tiket #{$data['ticket_number']} telah ditugaskan kepada Anda.",
            'ticket.breached' => "Tiket #{$data['ticket_number']} telah melewati batas waktu SLA.",
            'ticket.resolved' => "Tiket #{$data['ticket_number']} telah diselesaikan.",
            'reimbursement.created' => "{$employeeName} telah mengajukan reimbursement sebesar Rp " . number_format($data['amount'] ?? 0, 0, ',', '.'),
            'reimbursement.approved' => "Pengajuan reimbursement Anda telah disetujui.",
            'reimbursement.rejected' => "Pengajuan reimbursement Anda ditolak.",
            'overtime.created' => "{$employeeName} telah mengajukan lembur.",
            'overtime.approved' => "Pengajuan lembur Anda telah disetujui.",
            'project.created' => "Proyek baru '{$data['project_name']}' telah dibuat.",
            'task.assigned' => "Tugas '{$data['task_title']}' telah ditugaskan kepada Anda.",
            'task.completed' => "Tugas '{$data['task_title']}' telah diselesaikan.",
            'task.overdue' => "Tugas '{$data['task_title']}' telah melewati deadline.",
            'payroll.generated' => "Slip gaji periode {$data['period_name']} telah tersedia.",
            'announcement.published' => "Pengumuman baru: {$data['announcement_title']}",
            default => "Notifikasi dari sistem: {$module}.{$event}",
        };
    }

    protected function getDefaultEventPreferences(): array
    {
        return [
            'leave.created' => true,
            'leave.approved' => true,
            'leave.rejected' => true,
            'attendance.late' => true,
            'attendance.absent' => true,
            'invoice.overdue' => true,
            'invoice.paid' => true,
            'ticket.created' => true,
            'ticket.assigned' => true,
            'ticket.breached' => true,
            'ticket.resolved' => true,
            'reimbursement.created' => true,
            'reimbursement.approved' => true,
            'reimbursement.rejected' => true,
            'overtime.created' => true,
            'overtime.approved' => true,
            'project.created' => true,
            'task.assigned' => true,
            'task.completed' => true,
            'task.overdue' => true,
            'payroll.generated' => true,
            'announcement.published' => true,
        ];
    }
}

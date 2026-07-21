<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected ?string $fcmServerKey;
    protected string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->fcmServerKey = config('services.fcm.server_key');
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::info('PushNotification: No device tokens for user', ['user_id' => $user->id]);
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if (empty($this->fcmServerKey)) {
            Log::warning('PushNotification: FCM server key not configured');
            return;
        }

        if (empty($tokens)) {
            return;
        }

        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
            'data' => $data,
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if (!$response->successful()) {
                Log::error('PushNotification: FCM send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            $result = $response->json();

            if (!empty($result['results'])) {
                foreach ($result['results'] as $index => $res) {
                    if (!empty($res['error'])) {
                        $this->handleTokenError($tokens[$index], $res['error']);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('PushNotification: Exception', ['message' => $e->getMessage()]);
        }
    }

    public function registerToken(User $user, string $token, string $platform, ?string $deviceName = null): void
    {
        DeviceToken::updateOrCreate(
            ['user_id' => $user->id, 'token' => $token],
            [
                'platform' => $platform,
                'device_name' => $deviceName,
            ]
        );
    }

    public function unregisterToken(User $user, string $token): void
    {
        DeviceToken::where('user_id', $user->id)
            ->where('token', $token)
            ->delete();
    }

    public function notifyAttendanceReminder(User $user): void
    {
        $this->sendToUser(
            $user,
            'Pengingat Absensi',
            'Jangan lupa clock-in ya! Shift Anda sudah dimulai.',
            ['type' => 'attendance_reminder']
        );
    }

    public function notifyLeaveApproved(User $user, string $leaveType, string $dateRange): void
    {
        $this->sendToUser(
            $user,
            'Pengajuan Cuti Disetujui',
            "Cuti {$leaveType} Anda ({$dateRange}) telah disetujui.",
            ['type' => 'leave_approved']
        );
    }

    public function notifyLeaveRejected(User $user, string $leaveType, string $reason): void
    {
        $this->sendToUser(
            $user,
            'Pengajuan Cuti Ditolak',
            "Cuti {$leaveType} Anda ditolak. Alasan: {$reason}",
            ['type' => 'leave_rejected']
        );
    }

    public function notifyApprovalRequest(User $user, string $type, string $from): void
    {
        $this->sendToUser(
            $user,
            'Approval Menunggu',
            "{$from} mengajukan {$type}. Perlu persetujuan Anda.",
            ['type' => 'approval_request']
        );
    }

    public function notifyPaySlip(User $user, string $period): void
    {
        $this->sendToUser(
            $user,
            'Slip Gaji Tersedia',
            "Slip gaji untuk periode {$period} sudah tersedia.",
            ['type' => 'payslip_ready']
        );
    }

    protected function handleTokenError(string $token, string $error): void
    {
        $invalidErrors = ['NotRegistered', 'InvalidRegistration', 'MismatchSenderId'];

        if (in_array($error, $invalidErrors)) {
            DeviceToken::where('token', $token)->delete();
            Log::info('PushNotification: Removed invalid token', ['token' => substr($token, 0, 20) . '...']);
        }
    }
}

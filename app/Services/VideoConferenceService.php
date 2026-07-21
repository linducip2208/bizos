<?php

namespace App\Services;

use App\Models\CourseLesson;
use App\Models\Interview;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoConferenceService
{
    protected array $providerConfig = [];

    public function __construct()
    {
        $this->providerConfig = [
            'zoom' => [
                'api_base' => config('services.zoom.api_base', 'https://api.zoom.us/v2'),
                'account_id' => config('services.zoom.account_id'),
                'client_id' => config('services.zoom.client_id'),
                'client_secret' => config('services.zoom.client_secret'),
            ],
            'google_meet' => [
                'api_base' => 'https://www.googleapis.com/calendar/v3',
                'service_account' => config('services.google.service_account_json'),
                'calendar_id' => config('services.google.calendar_id', 'primary'),
            ],
        ];
    }

    public function createMeeting(array $data): array
    {
        $provider = $data['provider'] ?? 'internal';

        $meeting = Meeting::create([
            'company_id' => $data['company_id'] ?? auth()->user()?->company_id,
            'organized_by' => $data['host_id'] ?? auth()->user()?->employee_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_time' => $data['start_time'],
            'end_time' => Carbon::parse($data['start_time'])->addMinutes($data['duration_minutes'] ?? 60),
            'location' => $data['location'] ?? null,
            'meeting_type' => $data['meeting_type'] ?? 'online',
            'status' => 'scheduled',
            'provider' => $provider,
            'meeting_link' => $data['meeting_link'] ?? null,
            'passcode' => $data['passcode'] ?? null,
        ]);

        $result = [
            'meeting_id' => $meeting->id,
            'meeting_url' => $meeting->meeting_link,
            'passcode' => $meeting->passcode,
            'dial_in' => $meeting->dial_in,
            'title' => $meeting->title,
            'start_time' => $meeting->start_time,
            'end_time' => $meeting->end_time,
        ];

        if ($provider !== 'internal') {
            $external = $this->createExternalMeeting($provider, $meeting, $data);
            $meeting->update([
                'provider_meeting_id' => $external['provider_meeting_id'] ?? null,
                'meeting_url' => $external['meeting_url'] ?? $meeting->meeting_link,
                'passcode' => $external['passcode'] ?? null,
                'dial_in' => $external['dial_in'] ?? null,
            ]);
            $result = array_merge($result, $external);
        }

        if (!empty($data['attendees'])) {
            foreach ($data['attendees'] as $attendee) {
                $meeting->attendees()->create([
                    'employee_id' => $attendee['employee_id'],
                    'response' => 'pending',
                ]);
            }
        }

        return $result;
    }

    protected function createExternalMeeting(string $provider, Meeting $meeting, array $data): array
    {
        return match ($provider) {
            'zoom' => $this->createZoomMeeting($meeting, $data),
            'google_meet' => $this->createGoogleMeetMeeting($meeting, $data),
            default => ['meeting_url' => $data['meeting_link'] ?? null],
        };
    }

    protected function createZoomMeeting(Meeting $meeting, array $data): array
    {
        try {
            $token = $this->getZoomAccessToken();

            $payload = [
                'topic' => $meeting->title,
                'type' => 2,
                'start_time' => Carbon::parse($meeting->start_time)->toIso8601String(),
                'duration' => $data['duration_minutes'] ?? 60,
                'timezone' => config('app.timezone', 'Asia/Jakarta'),
                'agenda' => $meeting->description ?? '',
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'join_before_host' => false,
                    'mute_upon_entry' => true,
                    'waiting_room' => true,
                    'approval_type' => 0,
                    'auto_recording' => 'cloud',
                ],
            ];

            if (!empty($data['passcode'])) {
                $payload['password'] = $data['passcode'];
                $payload['settings']['approval_type'] = 2;
            }

            $response = Http::withToken($token)
                ->post("{$this->providerConfig['zoom']['api_base']}/users/me/meetings", $payload);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'provider_meeting_id' => (string) $body['id'],
                    'meeting_url' => $body['join_url'],
                    'passcode' => $body['password'] ?? null,
                    'dial_in' => null,
                ];
            }

            \Log::warning('Zoom meeting creation failed', ['response' => $response->body()]);
        } catch (\Throwable $e) {
            \Log::error('Zoom API error: ' . $e->getMessage());
        }

        return [
            'provider_meeting_id' => 'zoom_local_' . Str::uuid(),
            'meeting_url' => null,
            'passcode' => null,
            'dial_in' => null,
        ];
    }

    protected function createGoogleMeetMeeting(Meeting $meeting, array $data): array
    {
        try {
            $conferenceRequest = new \stdClass();
            $conferenceRequest->createRequest = (object) [
                'requestId' => (string) Str::uuid(),
                'conferenceSolutionKey' => (object) ['type' => 'hangoutsMeet'],
            ];

            $event = [
                'summary' => $meeting->title,
                'description' => $meeting->description ?? '',
                'start' => [
                    'dateTime' => Carbon::parse($meeting->start_time)->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'Asia/Jakarta'),
                ],
                'end' => [
                    'dateTime' => Carbon::parse($meeting->end_time)->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'Asia/Jakarta'),
                ],
                'conferenceData' => $conferenceRequest,
            ];

            return [
                'provider_meeting_id' => 'gmeet_local_' . Str::uuid(),
                'meeting_url' => null,
                'passcode' => null,
                'dial_in' => null,
            ];
        } catch (\Throwable $e) {
            \Log::error('Google Meet API error: ' . $e->getMessage());
        }

        return [
            'provider_meeting_id' => 'gmeet_local_' . Str::uuid(),
            'meeting_url' => null,
            'passcode' => null,
            'dial_in' => null,
        ];
    }

    protected function getZoomAccessToken(): string
    {
        try {
            $response = Http::withBasicAuth(
                $this->providerConfig['zoom']['client_id'],
                $this->providerConfig['zoom']['client_secret']
            )->asForm()->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => $this->providerConfig['zoom']['account_id'],
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }
        } catch (\Throwable $e) {
            \Log::error('Zoom OAuth error: ' . $e->getMessage());
        }

        return '';
    }

    public function generateInvite(array $meetingData): string
    {
        $start = Carbon::parse($meetingData['start_time']);
        $end = Carbon::parse($meetingData['end_time'] ?? $start->copy()->addHour());
        $uid = Str::uuid() . '@bizos';
        $now = Carbon::now()->format('Ymd\THis\Z');
        $startFormatted = $start->format('Ymd\THis\Z');
        $endFormatted = $end->format('Ymd\THis\Z');

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//BizOS//Meeting//ID\r\n";
        $ics .= "METHOD:REQUEST\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTAMP:{$now}\r\n";
        $ics .= "DTSTART:{$startFormatted}\r\n";
        $ics .= "DTEND:{$endFormatted}\r\n";
        $ics .= "SUMMARY:" . $this->escapeIcs($meetingData['title']) . "\r\n";
        $ics .= "DESCRIPTION:" . $this->escapeIcs($meetingData['description'] ?? '') . "\r\n";
        $ics .= "LOCATION:" . $this->escapeIcs($meetingData['meeting_url'] ?? $meetingData['location'] ?? 'Online') . "\r\n";
        $ics .= "ORGANIZER;CN=" . $this->escapeIcs($meetingData['organizer_name'] ?? 'BizOS') . ":mailto:" . ($meetingData['organizer_email'] ?? 'noreply@bizos.id') . "\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "SEQUENCE:0\r\n";
        $ics .= "TRANSP:OPAQUE\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    protected function escapeIcs(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace("\n", '\\n', $text);
        $text = str_replace("\r", '', $text);
        return $text;
    }

    public function getRecordings(string $provider, string $meetingId): array
    {
        $recordings = \App\Models\MeetingRecording::where('meeting_id', $meetingId)->get();
        return $recordings->toArray();
    }

    public function downloadRecording(string $recordingId): string
    {
        $recording = \App\Models\MeetingRecording::findOrFail($recordingId);

        if (Storage::exists($recording->file_path)) {
            return Storage::path($recording->file_path);
        }

        throw new \RuntimeException('File rekaman tidak ditemukan: ' . $recording->file_path);
    }

    public function generateTranscript(string $recordingPath, string $language = 'id'): array
    {
        if (!file_exists($recordingPath)) {
            throw new \RuntimeException('File rekaman tidak ditemukan: ' . $recordingPath);
        }

        $hash = md5_file($recordingPath);
        $cached = \App\Models\MeetingTranscript::where('language', $language)
            ->whereHas('recording', fn($q) => $q->where('file_path', $recordingPath))
            ->latest()
            ->first();

        if ($cached && md5($cached->full_text) === $hash) {
            return [
                'full_text' => $cached->full_text,
                'segments' => $cached->segments ?? [],
            ];
        }

        $transcript = $this->transcribeWithAi($recordingPath, $language);

        return [
            'full_text' => $transcript['full_text'],
            'segments' => $transcript['segments'],
        ];
    }

    protected function transcribeWithAi(string $audioPath, string $language): array
    {
        $fullText = '';
        $segments = [];

        try {
            $aiProvider = \App\Models\AiProvider::where('is_active', true)
                ->where('api_format', 'openai_compatible')
                ->first();

            if ($aiProvider) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . decrypt($aiProvider->api_key_encrypted),
                    'Content-Type' => 'multipart/form-data',
                ])->attach('file', file_get_contents($audioPath), basename($audioPath))
                ->post(rtrim($aiProvider->base_url, '/') . '/v1/audio/transcriptions', [
                    'model' => 'whisper-1',
                    'language' => $language,
                    'response_format' => 'verbose_json',
                    'timestamp_granularities' => ['segment'],
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $fullText = $data['text'] ?? '';
                    $segments = collect($data['segments'] ?? [])->map(fn($seg) => [
                        'speaker' => 'Speaker ' . ($seg['id'] ?? 1),
                        'text' => $seg['text'] ?? '',
                        'start_time' => $seg['start'] ?? 0,
                        'end_time' => $seg['end'] ?? 0,
                    ])->toArray();
                }
            }
        } catch (\Throwable $e) {
            \Log::error('AI Transcription error: ' . $e->getMessage());
        }

        if (empty($fullText)) {
            $fullText = "[Transkrip tidak tersedia - file: " . basename($audioPath) . "]";
        }

        return [
            'full_text' => $fullText,
            'segments' => $segments,
        ];
    }

    public function generateSummary(string $transcript): array
    {
        try {
            $aiProvider = \App\Models\AiProvider::where('is_active', true)
                ->where('api_format', 'openai_compatible')
                ->first();

            if ($aiProvider) {
                $prompt = <<<PROMPT
Anda adalah asisten analisis rapat. Berdasarkan transkrip rapat berikut, buat ringkasan dalam Bahasa Indonesia dengan format JSON:
{
  "summary": "ringkasan 2-3 paragraf",
  "key_decisions": ["keputusan 1", "keputusan 2"],
  "action_items": [{"task": "tugas", "assignee": "penanggung jawab", "deadline": "YYYY-MM-DD"}],
  "topics_discussed": ["topik 1", "topik 2"],
  "sentiment": "positive/neutral/negative",
  "next_meeting_suggestion": "saran rapat berikutnya"
}

Transkrip:
{$transcript}
PROMPT;

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . decrypt($aiProvider->api_key_encrypted),
                ])->post(rtrim($aiProvider->base_url, '/') . '/v1/chat/completions', [
                    'model' => $aiProvider->default_model ?? 'gpt-4o',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                    'response_format' => ['type' => 'json_object'],
                ]);

                if ($response->successful()) {
                    return json_decode($response->json()['choices'][0]['message']['content'], true);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('AI Summary error: ' . $e->getMessage());
        }

        return [
            'summary' => 'Ringkasan tidak tersedia.',
            'key_decisions' => [],
            'action_items' => [],
            'topics_discussed' => [],
            'sentiment' => 'neutral',
            'next_meeting_suggestion' => null,
        ];
    }

    public function autoCreateTasks(array $actionItems, ?int $projectId = null): array
    {
        $created = [];

        foreach ($actionItems as $item) {
            $task = \App\Models\Task::create([
                'project_id' => $projectId,
                'title' => $item['task'] ?? 'Tugas dari rapat',
                'description' => $item['description'] ?? 'Dibuat otomatis dari action item rapat.',
                'status' => 'todo',
                'priority' => $item['priority'] ?? 'medium',
                'due_date' => $item['deadline'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if (!empty($item['assignee_id'])) {
                $task->assignees()->create(['user_id' => $item['assignee_id']]);
            }

            $created[] = $task->id;
        }

        return $created;
    }

    public function trackAttendance(string $provider, string $meetingId): array
    {
        $meeting = Meeting::with('attendanceLogs')->find($meetingId);

        if (!$meeting) {
            return [];
        }

        return $meeting->attendanceLogs->map(fn($log) => [
            'participant_name' => $log->participant_name,
            'participant_email' => $log->participant_email,
            'join_time' => $log->join_time,
            'leave_time' => $log->leave_time,
            'duration_minutes' => $log->duration_minutes,
        ])->toArray();
    }

    public function scheduleRecurring(array $data, string $frequency, Carbon $until): array
    {
        $meetings = [];
        $start = Carbon::parse($data['start_time']);
        $duration = $data['duration_minutes'] ?? 60;

        while ($start->lte($until)) {
            $meetingData = array_merge($data, [
                'start_time' => $start->toDateTimeString(),
                'duration_minutes' => $duration,
                'is_recurring' => true,
                'recurrence_frequency' => $frequency,
                'recurrence_until' => $until,
            ]);

            $meetings[] = $this->createMeeting($meetingData);

            $start = match ($frequency) {
                'daily' => $start->addDay(),
                'weekly' => $start->addWeek(),
                'biweekly' => $start->addWeeks(2),
                'monthly' => $start->addMonth(),
                default => $start->addWeek(),
            };
        }

        return $meetings;
    }

    public function createInterviewRoom(Interview $interview): array
    {
        $interviewers = $interview->interviewers()->with('employee')->get();
        $candidate = $interview->candidate;

        $attendees = [];
        foreach ($interviewers as $intv) {
            if ($intv->employee) {
                $attendees[] = ['employee_id' => $intv->employee->id];
            }
        }

        return $this->createMeeting([
            'title' => 'Interview: ' . ($candidate->name ?? 'Kandidat') . ' - ' . $interview->interview_type,
            'description' => "Sesi interview untuk posisi yang dilamar.\nKandidat: " . ($candidate->name ?? '-') . "\nTipe: {$interview->interview_type}",
            'host_id' => $interviewers->first()?->employee_id ?? auth()->user()?->employee_id,
            'start_time' => $interview->scheduled_at,
            'duration_minutes' => $interview->duration_minutes ?? 60,
            'meeting_type' => 'online',
            'location' => $interview->location,
            'attendees' => $attendees,
            'provider' => 'zoom',
            'company_id' => auth()->user()?->company_id,
        ]);
    }

    public function createLiveClass(CourseLesson $lesson): array
    {
        $module = $lesson->module;
        $course = $module?->course;
        $enrollments = $course ? $course->enrollments()->with('employee')->get() : collect();

        $attendees = $enrollments->map(fn($e) => ['employee_id' => $e->employee_id])->toArray();

        return $this->createMeeting([
            'title' => 'Live Class: ' . $lesson->title,
            'description' => "Sesi live class untuk modul: {$module->title}\nMateri: {$lesson->title}",
            'host_id' => auth()->user()?->employee_id,
            'start_time' => now()->addDay()->setHour(9)->setMinute(0),
            'duration_minutes' => $lesson->duration_minutes ?? 90,
            'meeting_type' => 'online',
            'attendees' => $attendees,
            'provider' => 'zoom',
            'company_id' => auth()->user()?->company_id,
        ]);
    }

    public function endMeeting(int $meetingId): void
    {
        $meeting = Meeting::findOrFail($meetingId);
        $meeting->update([
            'status' => 'completed',
            'end_time' => now(),
        ]);
    }

    public function getMeetingStats(): array
    {
        return [
            'total_today' => Meeting::whereDate('start_time', today())->count(),
            'total_this_week' => Meeting::whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_this_month' => Meeting::whereMonth('start_time', now()->month)->count(),
            'by_type' => [
                'online' => Meeting::where('meeting_type', 'online')->count(),
                'onsite' => Meeting::where('meeting_type', 'onsite')->count(),
                'hybrid' => Meeting::where('meeting_type', 'hybrid')->count(),
            ],
            'by_status' => [
                'scheduled' => Meeting::where('status', 'scheduled')->count(),
                'in_progress' => Meeting::where('status', 'in_progress')->count(),
                'completed' => Meeting::where('status', 'completed')->count(),
                'cancelled' => Meeting::where('status', 'cancelled')->count(),
            ],
            'total_recordings' => \App\Models\MeetingRecording::count(),
            'total_transcripts' => \App\Models\MeetingTranscript::count(),
        ];
    }
}

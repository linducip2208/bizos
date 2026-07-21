<?php

namespace App\Services;

use App\Models\User;
use App\Models\VoiceChannel;
use App\Models\VoiceChannelMember;
use App\Models\VoiceNote;
use App\Models\VoiceNoteRecipient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VoiceNoteService
{
    protected string $disk = 'public';
    protected string $basePath = 'voice-notes';

    public function upload(int $senderId, array $recipientIds, string $audioPath, int $durationSeconds): VoiceNote
    {
        $voiceNote = VoiceNote::create([
            'sender_id' => $senderId,
            'audio_path' => $audioPath,
            'duration_seconds' => $durationSeconds,
        ]);

        foreach ($recipientIds as $recipientId) {
            VoiceNoteRecipient::create([
                'voice_note_id' => $voiceNote->id,
                'user_id' => $recipientId,
                'is_played' => false,
            ]);
        }

        return $voiceNote->load(['sender', 'recipients.user']);
    }

    public function storeUploadedFile(UploadedFile $file, int $userId): string
    {
        $datePath = now()->format('Y/m/d');
        $filename = 'vn_' . $userId . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs("{$this->basePath}/{$datePath}", $filename, $this->disk);
    }

    public function transcribe(VoiceNote $note): string
    {
        if ($note->transcript) {
            return $note->transcript;
        }

        $audioPath = storage_path('app/public/' . $note->audio_path);

        if (!file_exists($audioPath)) {
            return '';
        }

        $transcript = '';
        $aiProvider = \App\Models\AiProvider::where('is_active', true)
            ->where('api_format', 'openai_compatible')
            ->first();

        if ($aiProvider) {
            try {
                $adapter = new \App\Services\AiWriteService();
                $transcript = $adapter->transcribeAudio($audioPath, $aiProvider);
            } catch (\Exception $e) {
                $transcript = '[Transkripsi gagal: ' . $e->getMessage() . ']';
            }
        }

        if ($transcript) {
            $note->update(['transcript' => $transcript]);
        }

        return $transcript;
    }

    public function markPlayed(VoiceNote $note, int $userId): void
    {
        VoiceNoteRecipient::where('voice_note_id', $note->id)
            ->where('user_id', $userId)
            ->update([
                'is_played' => true,
                'played_at' => now(),
            ]);
    }

    public function getUnplayedCount(int $userId): int
    {
        return VoiceNoteRecipient::where('user_id', $userId)
            ->where('is_played', false)
            ->count();
    }

    public function createChannel(string $name, array $memberIds): VoiceChannel
    {
        $channel = VoiceChannel::create([
            'name' => $name,
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        foreach ($memberIds as $memberId) {
            VoiceChannelMember::create([
                'voice_channel_id' => $channel->id,
                'user_id' => $memberId,
                'last_active_at' => now(),
            ]);
        }

        // Auto-add creator as member if not included
        $creatorId = auth()->id();
        if (!in_array($creatorId, $memberIds)) {
            VoiceChannelMember::create([
                'voice_channel_id' => $channel->id,
                'user_id' => $creatorId,
                'last_active_at' => now(),
            ]);
        }

        return $channel->load(['members.user', 'creator']);
    }

    public function broadcastToChannel(VoiceChannel $channel, int $senderId, string $audioPath): void
    {
        $memberIds = $channel->members()->pluck('user_id')->toArray();
        $memberIds = array_filter($memberIds, fn($id) => $id !== $senderId);

        if (empty($memberIds)) {
            return;
        }

        VoiceNote::create([
            'sender_id' => $senderId,
            'audio_path' => $audioPath,
            'duration_seconds' => 0,
            'context_type' => 'voice_channel',
            'context_id' => $channel->id,
        ]);

        // Broadcast: create voice note with all channel members as recipients
        $voiceNote = VoiceNote::create([
            'sender_id' => $senderId,
            'audio_path' => $audioPath,
            'duration_seconds' => 0,
            'context_type' => 'voice_channel',
            'context_id' => $channel->id,
        ]);

        foreach ($memberIds as $memberId) {
            VoiceNoteRecipient::create([
                'voice_note_id' => $voiceNote->id,
                'user_id' => $memberId,
                'is_played' => false,
            ]);
        }

        // Update member last active
        VoiceChannelMember::where('voice_channel_id', $channel->id)
            ->where('user_id', $senderId)
            ->update(['last_active_at' => now()]);
    }

    public function getVoiceNotes(int $userId, string $type = 'all', int $perPage = 20): array
    {
        $query = VoiceNote::query();

        if ($type === 'sent') {
            $query->where('sender_id', $userId);
        } elseif ($type === 'received') {
            $query->whereHas('recipients', fn($q) => $q->where('user_id', $userId));
        } else {
            $query->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                    ->orWhereHas('recipients', fn($sub) => $sub->where('user_id', $userId));
            });
        }

        $notes = $query->with(['sender', 'recipients'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $data = $notes->through(function ($note) use ($userId) {
            $recipientEntry = $note->recipients->first(fn($r) => $r->user_id === $userId);

            return [
                'id' => $note->id,
                'sender' => [
                    'id' => $note->sender->id,
                    'name' => $note->sender->name,
                    'avatar' => $note->sender->avatar,
                ],
                'duration_seconds' => $note->duration_seconds,
                'has_transcript' => !empty($note->transcript),
                'transcript' => $note->transcript,
                'is_played' => $recipientEntry ? $recipientEntry->is_played : ($note->sender_id === $userId),
                'audio_url' => route('api.mobile.voice-note.audio', ['id' => $note->id]),
                'created_at' => $note->created_at->toIso8601String(),
                'context_type' => $note->context_type,
                'context_id' => $note->context_id,
            ];
        });

        return [
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'has_more' => $data->hasMorePages(),
            ],
        ];
    }

    public function getUserChannels(int $userId): array
    {
        return VoiceChannel::whereHas('members', fn($q) => $q->where('user_id', $userId))
            ->where('is_active', true)
            ->with(['members.user', 'creator'])
            ->get()
            ->map(fn($channel) => [
                'id' => $channel->id,
                'name' => $channel->name,
                'creator' => [
                    'id' => $channel->creator->id,
                    'name' => $channel->creator->name,
                ],
                'members' => $channel->members->map(fn($m) => [
                    'user_id' => $m->user_id,
                    'name' => $m->user->name ?? 'Unknown',
                    'last_active_at' => $m->last_active_at?->toIso8601String(),
                ]),
                'member_count' => $channel->members->count(),
                'is_active' => $channel->is_active,
                'created_at' => $channel->created_at->toIso8601String(),
            ])
            ->toArray();
    }
}

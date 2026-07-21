<?php

namespace App\Http\Livewire;

use App\Services\VoiceToTextService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class VoiceCommander extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;
    public bool $isRecording = false;
    public bool $isProcessing = false;
    public ?string $transcript = null;
    public ?array $parsedCommand = null;
    public ?array $executionResult = null;
    public ?string $errorMessage = null;
    public $audioFile = null;

    protected $listeners = [
        'openVoiceCommander' => 'open',
        'closeVoiceCommander' => 'close',
    ];

    public function mount(): void
    {
        $this->isOpen = false;
    }

    public function open(): void
    {
        $this->isOpen = true;
        $this->resetState();
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->resetState();
    }

    public function resetState(): void
    {
        $this->isRecording = false;
        $this->isProcessing = false;
        $this->transcript = null;
        $this->parsedCommand = null;
        $this->executionResult = null;
        $this->errorMessage = null;
        $this->audioFile = null;
    }

    public function toggleRecording(): void
    {
        $this->isRecording = !$this->isRecording;
    }

    public function updatedAudioFile(): void
    {
        $this->validate([
            'audioFile' => 'required|file|mimes:mp3,wav,ogg,webm,m4a|max:25600',
        ]);

        $this->isProcessing = true;
        $this->errorMessage = null;

        try {
            $path = $this->audioFile->store('voice-commands', 'public');
            $fullPath = Storage::disk('public')->path($path);

            $service = app(VoiceToTextService::class);
            $transcription = $service->transcribe($fullPath);

            if (!$transcription['success']) {
                $this->errorMessage = $transcription['error'] ?? 'Gagal transkripsi audio.';
                Storage::disk('public')->delete($path);
                $this->isProcessing = false;
                return;
            }

            $this->transcript = $transcription['text'];
            $this->parsedCommand = $service->processCommand($this->transcript);
            $this->executionResult = $service->executeCommand($this->parsedCommand);

            Storage::disk('public')->delete($path);
        } catch (\Exception $e) {
            $this->errorMessage = 'Error: ' . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    public function executeAgain(): void
    {
        if (!$this->parsedCommand) return;

        $service = app(VoiceToTextService::class);
        $this->executionResult = $service->executeCommand($this->parsedCommand);
    }

    public function submitText(): void
    {
        $this->validate([
            'transcript' => 'required|string|min:2|max:500',
        ]);

        $this->isProcessing = true;

        try {
            $service = app(VoiceToTextService::class);
            $this->parsedCommand = $service->processCommand($this->transcript);
            $this->executionResult = $service->executeCommand($this->parsedCommand);
        } catch (\Exception $e) {
            $this->errorMessage = 'Error: ' . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    public function getCommandColor(string $type): string
    {
        return match ($type) {
            'create_task' => '#6366f1',
            'approve_leave' => '#22c55e',
            'reject_leave' => '#ef4444',
            'check_stock' => '#f59e0b',
            'create_note' => '#8b5cf6',
            'view_dashboard' => '#3b82f6',
            'search' => '#06b6d4',
            default => '#6b7280',
        };
    }

    public function getCommandLabel(string $type): string
    {
        return match ($type) {
            'create_task' => 'Buat Task',
            'approve_leave' => 'Setujui Cuti',
            'reject_leave' => 'Tolak Cuti',
            'check_stock' => 'Cek Stok',
            'create_note' => 'Buat Catatan',
            'view_dashboard' => 'Lihat Dashboard',
            'search' => 'Pencarian',
            'schedule_meeting' => 'Jadwalkan Rapat',
            default => 'Tidak Dikenali',
        };
    }

    public function render()
    {
        return view('livewire.voice-commander');
    }
}

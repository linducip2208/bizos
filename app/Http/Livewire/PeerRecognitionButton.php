<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Services\GamificationService;
use Livewire\Component;

class PeerRecognitionButton extends Component
{
    public int $userId;
    public string $userName = '';
    public bool $showModal = false;
    public string $recognitionMessage = '';
    public string $recognitionBadge = '';
    public bool $sent = false;

    protected array $availableBadges = [
        'hero' => 'Pahlawan',
        'team_player' => 'Team Player',
        'innovator' => 'Inovator',
        'mentor' => 'Mentor',
        'problem_solver' => 'Problem Solver',
        'cheerleader' => 'Penyemangat',
        'detail_oriented' => 'Teliti',
        'go_getter' => 'Go-Getter',
    ];

    protected $listeners = ['openRecognitionModal' => 'open'];

    public function open(int $userId, string $userName): void
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->showModal = true;
        $this->sent = false;
        $this->recognitionMessage = '';
        $this->recognitionBadge = '';
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function send(): void
    {
        $this->validate([
            'recognitionBadge' => 'required',
            'recognitionMessage' => 'required|min:5|max:500',
        ]);

        $fromUserId = auth()->id();
        if ($fromUserId === $this->userId) {
            session()->flash('recognition_error', 'Anda tidak bisa memberikan pengakuan ke diri sendiri.');
            return;
        }

        $service = app(GamificationService::class);
        $service->giveRecognition($fromUserId, $this->userId, $this->recognitionMessage, $this->recognitionBadge);

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.peer-recognition-button', [
            'badges' => $this->availableBadges,
        ]);
    }
}

<?php

namespace App\Http\Livewire;

use App\Services\GamificationService;
use Livewire\Component;

class LeaderboardWidget extends Component
{
    public array $leaderboard = [];
    public string $period = 'weekly';
    public int $limit = 10;

    public function mount(): void
    {
        $this->loadLeaderboard();
    }

    public function loadLeaderboard(): void
    {
        $service = app(GamificationService::class);
        $this->leaderboard = $service->getLeaderboard('company', $this->period, $this->limit);
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->loadLeaderboard();
    }

    public function render()
    {
        return view('livewire.leaderboard-widget');
    }
}

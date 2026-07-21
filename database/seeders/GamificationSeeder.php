<?php

namespace Database\Seeders;

use App\Services\GamificationService;
use Illuminate\Database\Seeder;

class GamificationSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(GamificationService::class);
        $service->seedDefaultActions();
        $service->seedDefaultBadges();

        $this->command?->info('Gamification: Default actions and badges seeded successfully.');
    }
}

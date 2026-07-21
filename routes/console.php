<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('invoices:check-overdue')->hourly();
Schedule::command('notifications:send-pending')->everyFiveMinutes();
Schedule::command('db:backup')->dailyAt('02:00');

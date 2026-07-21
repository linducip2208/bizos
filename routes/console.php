<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('invoices:check-overdue')->hourly();
Schedule::command('notifications:send-pending')->everyFiveMinutes();
Schedule::command('approval:check-sla')->hourly();
Schedule::command('db:backup')->dailyAt('02:00');
Schedule::command('report:send-scheduled')->everyFiveMinutes();
Schedule::command('billing:generate-invoices')->daily();
Schedule::command('billing:check-expired')->daily();
Schedule::command('helpdesk:check-sla')->everyFifteenMinutes();
Schedule::command('compliance:retention-cleanup')->dailyAt('01:00');
Schedule::command('compliance:check-breach-deadline')->hourly();
Schedule::command('marketing:send-scheduled')->everyMinute();
Schedule::command('bizos:scan-anomalies --send-wa')->dailyAt('07:00');
Schedule::command('webhook:retry-failed')->everyFiveMinutes();
Schedule::command('tenant:enforce-limits')->dailyAt('03:00');
Schedule::command('tenant:record-usage')->hourly();
Schedule::command('wa:sync-templates')->hourly();
Schedule::command('logistics:record-gps')->everyTwoMinutes();
Schedule::command('ecommerce:pull-orders')->hourly();
Schedule::command('ecommerce:push-inventory')->everyThirtyMinutes();
Schedule::command('manufacturing:run-mrp')->dailyAt('05:00');
Schedule::command('manufacturing:daily-oee')->dailyAt('23:00');
Schedule::command('healthcare:send-reminders')->dailyAt('08:00');
Schedule::command('esg:calculate-carbon')->monthlyOn(1, '03:00');
Schedule::command('integrations:sync-all')->hourly();
Schedule::command('integrations:pull-bank')->dailyAt('07:00');
Schedule::command('integrations:djp-sync')->dailyAt('06:00');
Schedule::command('bpmn:check-sla')->everyFifteenMinutes();
Schedule::command('fieldservice:generate-work-orders')->dailyAt('06:00');
Schedule::command('fieldservice:check-van-stock')->dailyAt('08:00');
Schedule::command('email:sync-accounts')->everyFiveMinutes();
Schedule::command('bizos:notify-overdue')->dailyAt('08:00');
Schedule::command('license:check-expired')->dailyAt('03:00');

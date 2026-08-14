<?php

namespace App\Http\Controllers;

use App\Services\CalendarSyncService;
use Illuminate\Http\Response;

class CalendarFeedController extends Controller
{
    public function show(string $token): Response
    {
        $companyId = app(CalendarSyncService::class)->resolveCompanyByToken($token);

        if (!$companyId) {
            abort(404);
        }

        $ics = app(CalendarSyncService::class)->buildIcalFeed($companyId);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="bizos-calendar.ics"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}

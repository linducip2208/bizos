<?php

namespace App\Http\Controllers;

use App\Services\EmailCampaignService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailTrackingController extends Controller
{
    public function open(string $token): Response
    {
        $service = app(EmailCampaignService::class);
        $service->trackOpen($token);

        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function click(string $token, Request $request): \Illuminate\Http\RedirectResponse
    {
        $service = app(EmailCampaignService::class);
        $service->trackClick($token, $request->get('url', '/'));

        $redirectUrl = $request->get('url', '/');

        if (!filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            $redirectUrl = config('app.url');
        }

        return redirect()->away($redirectUrl);
    }
}

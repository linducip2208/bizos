<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\LeadActivityLog;
use App\Services\LandingPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function show(string $slug): View
    {
        $service = app(LandingPageService::class);
        $data = $service->renderPage($slug);

        $page = LandingPage::where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('landing-page.show', [
            'page' => $page->toArray(),
            'form' => $data['form'],
            'pageData' => $data,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\WikiPage;
use App\Services\WikiService;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    protected WikiService $wikiService;

    public function __construct(WikiService $wikiService)
    {
        $this->wikiService = $wikiService;
    }

    public function show(string $slug)
    {
        $page = WikiPage::where('slug', $slug)->published()->firstOrFail();
        $this->wikiService->incrementView($page);

        $popular = $this->wikiService->getPopular(5);
        $tree = $this->wikiService->getTree();

        return view('wiki.show', compact('page', 'popular', 'tree'));
    }

    public function index()
    {
        $pages = WikiPage::published()->with('category')->orderByDesc('published_at')->paginate(12);
        $popular = $this->wikiService->getPopular(5);
        $tree = $this->wikiService->getTree();

        return view('wiki.index', compact('pages', 'popular', 'tree'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(12);

        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();

        $query = request('q');
        if ($query) {
            $posts = BlogPost::published()
                ->with(['category', 'author'])
                ->where('title', 'like', "%{$query}%")
                ->orWhere('excerpt', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%")
                ->latest('published_at')
                ->paginate(12);
        }

        $seoMeta = [
            'title' => 'Blog — BizOS | Tips Bisnis, HRM, Akuntansi & Teknologi',
            'description' => 'Baca artikel terbaru tentang manajemen bisnis, HRM, akuntansi, CRM, project management, dan tips teknologi untuk bisnis Indonesia.',
            'canonical' => route('blog.index'),
        ];

        return view('blog.index', compact('posts', 'categories', 'query', 'seoMeta'));
    }

    public function show($slug): View
    {
        $post = BlogPost::published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPosts = BlogPost::published()
            ->with(['category', 'author'])
            ->where('id', '!=', $post->id)
            ->where(function ($q) use ($post) {
                if ($post->category_id) {
                    $q->where('category_id', $post->category_id);
                }
            })
            ->latest('published_at')
            ->limit(3)
            ->get();

        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();

        $seoMeta = [
            'title' => $post->meta_title ?: $post->title . ' — Blog BizOS',
            'description' => $post->meta_description ?: $post->excerpt ?: Str::limit(strip_tags($post->content), 160),
            'canonical' => route('blog.show', $post->slug),
            'og_title' => $post->meta_title ?: $post->title,
            'og_description' => $post->meta_description ?: $post->excerpt,
            'og_image' => $post->featured_image ? asset('storage/' . $post->featured_image) : null,
            'jsonld' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $post->title,
                'author' => [
                    '@type' => 'Person',
                    'name' => $post->author?->name ?? 'BizOS',
                ],
                'datePublished' => $post->published_at?->toIso8601String(),
                'dateModified' => $post->updated_at?->toIso8601String(),
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'BizOS',
                    'url' => url('/'),
                ],
                'image' => $post->featured_image ? asset('storage/' . $post->featured_image) : null,
                'description' => $post->meta_description ?: $post->excerpt,
            ],
        ];

        return view('blog.show', compact('post', 'relatedPosts', 'categories', 'seoMeta'));
    }

    public function category($slug): View
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = BlogPost::published()
            ->with(['category', 'author'])
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(12);

        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();

        $seoMeta = [
            'title' => 'Kategori: ' . $category->name . ' — Blog BizOS',
            'description' => 'Artikel BizOS dalam kategori ' . $category->name . ($category->description ? ': ' . $category->description : ''),
            'canonical' => route('blog.category', $category->slug),
        ];

        return view('blog.index', compact('posts', 'categories', 'category', 'seoMeta'));
    }

    public function feed(): Response
    {
        $posts = BlogPost::published()
            ->with('category')
            ->latest('published_at')
            ->limit(20)
            ->get();

        $appUrl = config('app.url', url('/'));
        $feedUrl = url('/blog/feed.xml');

        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"></rss>'
        );

        $channel = $xml->addChild('channel');
        $channel->addChild('title', 'BizOS Blog');
        $channel->addChild('link', $appUrl);
        $channel->addChild('description', 'Artikel terbaru dari BizOS');
        $channel->addChild('language', 'id');

        $atomLink = $channel->addChild('atom:link');
        $atomLink->addAttribute('href', $feedUrl);
        $atomLink->addAttribute('rel', 'self');
        $atomLink->addAttribute('type', 'application/rss+xml');

        foreach ($posts as $post) {
            $item = $channel->addChild('item');
            $item->addChild('title', htmlspecialchars($post->title, ENT_XML1, 'UTF-8'));
            $item->addChild('link', $appUrl . '/blog/' . $post->slug);
            $item->addChild('pubDate', $post->published_at->toRfc2822String());

            $description = $post->excerpt ?: Str::limit(strip_tags($post->content), 300);
            $item->addChild('description', htmlspecialchars($description, ENT_XML1, 'UTF-8'));

            if ($post->category) {
                $item->addChild('category', htmlspecialchars($post->category->name, ENT_XML1, 'UTF-8'));
            }

            $guid = $item->addChild('guid', $appUrl . '/blog/' . $post->slug);
            $guid->addAttribute('isPermaLink', 'true');
        }

        $headers = [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ];

        return response($xml->asXML(), 200, $headers);
    }
}

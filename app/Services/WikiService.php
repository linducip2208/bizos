<?php

namespace App\Services;

use App\Models\WikiCategory;
use App\Models\WikiPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WikiService
{
    public function search(string $query): Collection
    {
        return WikiPage::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->with('category')
            ->orderByDesc('view_count')
            ->limit(50)
            ->get();
    }

    public function getPopular(int $limit = 10): Collection
    {
        return WikiPage::published()
            ->with('category')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();
    }

    public function getRecentChanges(int $limit = 20): Collection
    {
        return WikiPage::published()
            ->with(['lastEditor', 'category'])
            ->orderByDesc('last_edited_at')
            ->limit($limit)
            ->get();
    }

    public function getTree(): array
    {
        $categories = WikiCategory::with(['children.children', 'pages' => fn($q) => $q->published()])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return $this->buildTree($categories);
    }

    protected function buildTree(Collection $categories): array
    {
        $result = [];
        foreach ($categories as $category) {
            $node = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'pages' => $category->pages->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'updated_at' => $p->last_edited_at ?? $p->updated_at,
                ])->toArray(),
                'children' => [],
            ];

            if ($category->children->isNotEmpty()) {
                $node['children'] = $this->buildTree($category->children);
            }

            $result[] = $node;
        }
        return $result;
    }

    public function incrementView(WikiPage $page): void
    {
        $page->increment('view_count');
    }
}

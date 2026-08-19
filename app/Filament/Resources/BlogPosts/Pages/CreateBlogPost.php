<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Services\Seo\IndexNowService;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function afterCreate(): void
    {
        $post = $this->record;

        if ($post->is_published && $post->published_at && $post->published_at->lte(now())) {
            $url = config('app.url') . '/blog/' . $post->slug;

            try {
                app(IndexNowService::class)->submit($url);
            } catch (\Throwable) {
                // Silently fail — IndexNow is best-effort
            }
        }
    }
}

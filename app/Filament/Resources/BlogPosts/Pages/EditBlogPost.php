<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Services\Seo\IndexNowService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
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

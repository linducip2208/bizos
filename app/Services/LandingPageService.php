<?php

namespace App\Services;

use App\Models\Form;
use App\Models\LandingPage;

class LandingPageService
{
    public function createPage(array $data): LandingPage
    {
        return LandingPage::create([
            'company_id' => $data['company_id'] ?? auth()->user()->company_id,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'] ?? [],
            'meta_title' => $data['meta_title'] ?? $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'form_id' => $data['form_id'] ?? null,
            'created_by' => $data['created_by'] ?? auth()->user()?->employee?->id,
        ]);
    }

    public function publishPage(LandingPage $page): void
    {
        $page->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function renderPage(string $slug): array
    {
        $page = LandingPage::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $form = null;
        if ($page->form_id) {
            $form = Form::with(['fields' => function ($query) {
                $query->orderBy('sort_order');
            }])->find($page->form_id);

            if ($form && $form->status !== 'published') {
                $form = null;
            }
        }

        return [
            'page' => $page->toArray(),
            'form' => $form ? $form->toArray() : null,
        ];
    }
}

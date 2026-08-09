<?php

namespace App\Console\Commands\Seo;

use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow
                            {--url= : Single URL to submit}
                            {--all : Submit all recent PSEO, blog, and public URLs}';

    protected $description = 'Submit URLs to IndexNow search engines (Bing, Yandex, Seznam, Naver)';

    public function handle(IndexNowService $indexNow): int
    {
        if (! config('indexnow.enabled', true)) {
            $this->warn('IndexNow is disabled in config. Set INDEXNOW_ENABLED=true to enable.');

            return self::SUCCESS;
        }

        $singleUrl = $this->option('url');

        if ($singleUrl) {
            return $this->submitSingle($indexNow, $singleUrl);
        }

        if ($this->option('all')) {
            return $this->submitAll($indexNow);
        }

        $this->error('Please specify --url= or --all option.');

        return self::FAILURE;
    }

    protected function submitSingle(IndexNowService $indexNow, string $url): int
    {
        $this->info("Submitting: {$url}");

        $result = $indexNow->submit($url);

        if (! $result['submitted']) {
            $this->warn("Skipped: {$result['reason']}");

            return self::SUCCESS;
        }

        foreach ($result['results'] as $engine => $status) {
            $icon = $status === 'ok' ? '<info>OK</info>' : '<error>FAIL</error>';
            $this->line("  {$engine}: {$icon}");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    protected function submitAll(IndexNowService $indexNow): int
    {
        $this->info('Collecting all public URLs for IndexNow submission...');
        $this->newLine();

        $baseUrl = rtrim(config('app.url'), '/');
        $urls = [];

        // Static pages
        $urls[] = $baseUrl . '/';
        $urls[] = $baseUrl . '/docs';

        // PSEO routes
        $pseoRoutes = [
            '/best-hrm-software',
            '/best-accounting-software-indonesia',
            '/best-payroll-software-indonesia',
            '/best-crm-software-indonesia',
            '/best-project-management-software',
            '/compare/bizos-vs-spreadsheet',
            '/compare/bizos-vs-talenta',
            '/compare/bizos-vs-jurnal',
            '/alternatives-to-excel-for-hr',
            '/alternatives-to-talenta',
        ];

        foreach ($pseoRoutes as $route) {
            $urls[] = $baseUrl . $route;
        }

        // Blog posts (if BlogPost model exists)
        if (class_exists('\App\Models\BlogPost')) {
            $posts = \App\Models\BlogPost::where('is_published', true)
                ->where('published_at', '<=', now())
                ->pluck('slug');

            foreach ($posts as $slug) {
                $urls[] = $baseUrl . '/blog/' . $slug;
            }

            $this->line("  Blog posts: {$posts->count()} URLs");
        }

        // Contact page
        $urls[] = $baseUrl . '/contact';

        $urls = array_unique($urls);

        $this->info("Total URLs to submit: " . count($urls));
        $this->newLine();

        $submitted = 0;
        $skipped = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        foreach ($urls as $url) {
            $result = $indexNow->submit($url);

            if (! $result['submitted'] && ($result['reason'] ?? '') === 'already_submitted') {
                $skipped++;
            } elseif (! $result['submitted']) {
                $skipped++;
            } else {
                $allOk = true;
                foreach ($result['results'] as $status) {
                    if ($status !== 'ok') {
                        $allOk = false;
                        break;
                    }
                }
                if ($allOk) {
                    $submitted++;
                } else {
                    $failed++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();

        $this->info("Results: {$submitted} submitted, {$skipped} skipped, {$failed} failed");

        return self::SUCCESS;
    }
}

<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    protected string $key;

    protected array $engines;

    protected int $cacheTtl;

    public function __construct()
    {
        $this->key = $this->getKey();
        $this->engines = config('indexnow.engines', [
            'www.bing.com',
            'yandex.com',
            'search.seznam.cz',
            'searchadvisor.naver.com',
        ]);
        $this->cacheTtl = (int) config('indexnow.cache_ttl', 30);
    }

    public function submit(string $url): array
    {
        if (! config('indexnow.enabled', true)) {
            return ['submitted' => false, 'reason' => 'disabled'];
        }

        if ($this->isAlreadySubmitted($url)) {
            return ['submitted' => false, 'reason' => 'already_submitted'];
        }

        $results = [];

        foreach ($this->engines as $engine) {
            try {
                $response = Http::timeout(10)->get("https://{$engine}/indexnow", [
                    'url' => $url,
                    'key' => $this->key,
                ]);

                $results[$engine] = $response->successful() ? 'ok' : 'failed';
            } catch (\Exception $e) {
                $results[$engine] = 'error';
                Log::warning("IndexNow submit failed for {$engine}: {$e->getMessage()}", [
                    'url' => $url,
                ]);
            }
        }

        $this->markAsSubmitted($url);

        return ['submitted' => true, 'results' => $results];
    }

    public function submitMany(array $urls): array
    {
        $results = [];

        foreach ($urls as $url) {
            $results[$url] = $this->submit($url);
        }

        return $results;
    }

    public function getKey(): string
    {
        $keyPath = public_path(config('indexnow.key_file', 'indexnow-key.txt'));

        if (file_exists($keyPath)) {
            $key = trim(file_get_contents($keyPath));

            if (! empty($key)) {
                return $key;
            }
        }

        $key = bin2hex(random_bytes(32));
        file_put_contents($keyPath, $key);

        return $key;
    }

    public function getSearchEngines(): array
    {
        return $this->engines;
    }

    public function isAlreadySubmitted(string $url): bool
    {
        return Cache::has('indexnow:' . md5($url));
    }

    public function markAsSubmitted(string $url): void
    {
        Cache::put('indexnow:' . md5($url), true, now()->addDays($this->cacheTtl));
    }

    public function clearSubmissionCache(string $url): void
    {
        Cache::forget('indexnow:' . md5($url));
    }
}

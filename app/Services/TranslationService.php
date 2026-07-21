<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class TranslationService
{
    protected array $availableLanguages = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ];

    public function get(string $key, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return Cache::remember("translation.{$locale}.{$key}", 3600, function () use ($key, $locale) {
            $translation = Translation::where('key', $key)->where('locale', $locale)->first();
            return $translation?->value ?? $key;
        });
    }

    public function setLocale(string $locale): void
    {
        if (array_key_exists($locale, $this->availableLanguages)) {
            session()->put('locale', $locale);
            app()->setLocale($locale);
        }
    }

    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }

    public function exportTranslations(): array
    {
        $translations = Translation::all()->groupBy('key');
        $result = [];

        foreach ($translations as $key => $items) {
            $result[$key] = [];
            foreach ($this->availableLanguages as $locale => $label) {
                $result[$key][$locale] = $items->where('locale', $locale)->first()?->value ?? '';
            }
        }

        return $result;
    }

    public function importFromLanguageFiles(): void
    {
        $locales = array_keys($this->availableLanguages);

        foreach ($locales as $locale) {
            $path = lang_path($locale);
            if (!File::isDirectory($path)) continue;

            $files = File::allFiles($path);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') continue;

                $group = $file->getFilenameWithoutExtension();
                $keys = require $file->getPathname();

                $this->flattenAndImport($keys, $group, $locale);
            }
        }

        Cache::flush();
    }

    protected function flattenAndImport(array $array, string $prefix, string $locale): void
    {
        foreach ($array as $key => $value) {
            $fullKey = $prefix . '.' . $key;
            if (is_array($value)) {
                $this->flattenAndImport($value, $fullKey, $locale);
            } else {
                Translation::updateOrCreate(
                    ['key' => $fullKey, 'locale' => $locale],
                    ['value' => (string) $value]
                );
            }
        }
    }
}

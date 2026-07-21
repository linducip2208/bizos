<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\TranslationService;

class LanguageSwitcher extends Component
{
    public string $currentLocale = 'id';
    public array $availableLanguages = [];

    protected TranslationService $translationService;

    public function boot(TranslationService $translationService): void
    {
        $this->translationService = $translationService;
    }

    public function mount(): void
    {
        $this->currentLocale = app()->getLocale();
        $this->availableLanguages = $this->translationService->getAvailableLanguages();
    }

    public function switchLanguage(string $locale): void
    {
        $this->translationService->setLocale($locale);
        $this->currentLocale = $locale;
        $this->redirect(request()->header('Referer', '/admin'));
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}

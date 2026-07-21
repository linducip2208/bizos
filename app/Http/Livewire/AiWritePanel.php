<?php

namespace App\Http\Livewire;

use App\Services\AiWriteService;
use Livewire\Component;

class AiWritePanel extends Component
{
    public bool $isOpen = false;
    public string $prompt = '';
    public string $tone = 'formal';
    public string $language = 'id';
    public string $generatedText = '';
    public string $targetTextareaId = '';
    public string $contextInfo = '';
    public bool $isGenerating = false;
    public string $errorMessage = '';
    public string $mode = 'generate';

    protected $listeners = [
        'openAiWritePanel' => 'open',
        'closeAiWritePanel' => 'close',
    ];

    protected $rules = [
        'prompt' => 'required|string|min:3|max:2000',
        'tone' => 'required|in:formal,casual,persuasive,empathetic',
        'language' => 'required|in:id,en',
    ];

    public function mount(): void
    {
        $this->isOpen = false;
    }

    public function open(string $textareaId = '', string $context = ''): void
    {
        $this->isOpen = true;
        $this->targetTextareaId = $textareaId;
        $this->contextInfo = $context;
        $this->prompt = '';
        $this->generatedText = '';
        $this->errorMessage = '';
        $this->mode = 'generate';
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->prompt = '';
        $this->generatedText = '';
        $this->errorMessage = '';
    }

    public function generate(): void
    {
        $this->validate();

        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->generatedText = '';

        try {
            $service = app(AiWriteService::class);

            $this->generatedText = $service->generate([
                'prompt' => $this->prompt,
                'context' => $this->contextInfo,
                'tone' => $this->tone,
                'language' => $this->language,
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->generatedText = '';
        } finally {
            $this->isGenerating = false;
        }
    }

    public function summarize(string $text): void
    {
        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->generatedText = '';

        try {
            $service = app(AiWriteService::class);
            $this->generatedText = $service->summarize($text);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function rewrite(string $text): void
    {
        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->generatedText = '';

        try {
            $service = app(AiWriteService::class);
            $this->generatedText = $service->rewrite($text, $this->tone);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function fixGrammar(string $text): void
    {
        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->generatedText = '';

        try {
            $service = app(AiWriteService::class);
            $this->generatedText = $service->fixGrammar($text);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function translate(string $text): void
    {
        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->generatedText = '';

        try {
            $service = app(AiWriteService::class);
            $toLang = $this->language;
            if ($toLang === 'id') {
                $toLang = 'en';
            }
            $this->generatedText = $service->translate($text, $toLang);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function expand(string $text): void
    {
        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->generatedText = '';

        try {
            $service = app(AiWriteService::class);
            $this->generatedText = $service->expand($text, 'longer');
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function insert(): void
    {
        if ($this->targetTextareaId) {
            $this->dispatch('insertAiText', textareaId: $this->targetTextareaId, text: $this->generatedText);
        }
        $this->close();
    }

    public function render()
    {
        return view('livewire.ai-write-panel');
    }
}

<?php

namespace App\Filament\Pages;

use App\Services\NaturalLanguageQueryService;
use Filament\Pages\Page;

class NaturalLanguageQuery extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?int $navigationSort = 904;

    protected static ?string $title = 'AI Query';

    protected static ?string $navigationLabel = 'AI Query';

    protected static ?string $slug = 'natural-language-query';

    protected string $view = 'filament.pages.natural-language-query';

    public array $suggestions = [];
    public array $history = [];
    public array $schema = [];

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::AUTOMATION->value;
    }

    public function mount(): void
    {
        $service = app(NaturalLanguageQueryService::class);
        $user = auth()->user();

        $this->schema = $service->getQuerySchema();
        $this->suggestions = collect($this->schema)->pluck('example')->values()->toArray();
        $this->history = $user ? $service->getHistory($user) : [];
    }

    public function handleQuery(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'question' => 'required|string|max:1000',
        ]);

        $question = trim($data['question']);
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Sesi berakhir. Silakan login ulang.'], 401);
        }

        try {
            $result = app(NaturalLanguageQueryService::class)->query($question, $user);

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memproses pertanyaan.'], 500);
        }
    }

    public function getHistory(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['history' => []]);
        }

        return response()->json([
            'history' => app(NaturalLanguageQueryService::class)->getHistory($user),
        ]);
    }
}

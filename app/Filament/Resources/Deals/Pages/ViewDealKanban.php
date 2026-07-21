<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Resources\Deals\DealResource;
use App\Models\Deal;
use App\Models\PipelineStage;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class ViewDealKanban extends Page
{
    protected static string $resource = DealResource::class;

    protected string $view = 'filament.pages.deal-kanban';

    protected static ?string $title = 'Kanban Pipeline';

    protected static ?int $navigationSort = 407;

    public array $stages = [];
    public array $deals = [];
    public array $stageNames = [];
    public array $stageColors = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $companyId = auth()->user()?->company_id;

        $this->stages = PipelineStage::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        $this->stageNames = [];
        $this->stageColors = [];
        foreach ($this->stages as $stage) {
            $this->stageNames[$stage['id']] = $stage['name'];
            $this->stageColors[$stage['id']] = $stage['color'] ?? '#6366f1';
        }

        $this->deals = Deal::where('company_id', $companyId)
            ->where('status', 'open')
            ->with(['client', 'assignedTo', 'stage'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();
    }

    public function moveDeal(int $dealId, int $stageId): void
    {
        $deal = Deal::findOrFail($dealId);
        $stage = PipelineStage::findOrFail($stageId);

        $deal->update([
            'stage_id' => $stageId,
        ]);

        $this->loadData();

        Notification::make()
            ->title('Deal dipindahkan')
            ->body("Deal \"{$deal->title}\" dipindahkan ke tahap \"{$stage->name}\"")
            ->success()
            ->send();

        $this->dispatch('deal-moved', dealId: $dealId, stageId: $stageId);
    }

    public function openCreateDealModal(): void
    {
        $this->dispatch('open-modal', id: 'create-deal-modal');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Buat Deal Baru')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    TextInput::make('title')
                        ->label('Judul Deal')
                        ->required()
                        ->maxLength(500),
                    Select::make('client_id')
                        ->label('Klien')
                        ->relationship('client', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('stage_id')
                        ->label('Tahap Pipeline')
                        ->options(
                            PipelineStage::where('company_id', auth()->user()?->company_id)
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('name', 'id')
                        )
                        ->required(),
                    Select::make('assigned_to')
                        ->label('Sales Person')
                        ->relationship('assignedTo', 'first_name')
                        ->searchable()
                        ->preload(),
                    TextInput::make('expected_value')
                        ->label('Nilai Deal')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $data['company_id'] = auth()->user()?->company_id;
                    $data['status'] = 'open';

                    Deal::create($data);

                    $this->loadData();

                    Notification::make()
                        ->title('Deal berhasil dibuat')
                        ->success()
                        ->send();

                    $this->dispatch('close-modal', id: 'create-deal-modal');
                }),

            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->loadData();
                }),
        ];
    }

    protected function getViewData(): array
    {
        $stagesWithDeals = [];
        foreach ($this->stages as $stage) {
            $stageId = $stage['id'];
            $stageDeals = array_filter($this->deals, function ($deal) use ($stageId) {
                return $deal['stage_id'] === $stageId;
            });

            $stage['deals'] = array_values($stageDeals);
            $stage['deal_count'] = count($stage['deals']);
            $stage['total_value'] = array_sum(array_column($stage['deals'], 'expected_value'));

            $stagesWithDeals[] = $stage;
        }

        return [
            'stages' => $stagesWithDeals,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'CRM';
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return true;
    }
}

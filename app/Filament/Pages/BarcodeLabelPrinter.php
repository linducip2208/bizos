<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\ProductBarcodeService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class BarcodeLabelPrinter extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static ?int $navigationSort = 122;

    protected static ?string $title = 'Cetak Label Barcode';

    protected static ?string $slug = 'barcode-label-printer';

    protected string $view = 'filament.pages.barcode-label-printer';

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::INVENTORY->value;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'product_ids' => [],
            'format' => 'code128',
            'size' => 'medium',
            'copies' => 1,
        ]);

        $preselect = request()->query('products') ?? request()->query('product_id');
        if ($preselect) {
            $ids = array_values(array_filter(array_map('intval', explode(',', (string) $preselect))));
            $this->form->fill(['product_ids' => $ids]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_ids')
                    ->label('Pilih Produk')
                    ->options(fn () => Product::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->limit(2000)
                        ->get()
                        ->mapWithKeys(fn (Product $p) => [$p->id => $p->name.' ('.$p->code.')'])
                        ->toArray())
                    ->multiple()
                    ->searchable()
                    ->live()
                    ->required()
                    ->placeholder('Cari dan pilih produk...')
                    ->columnSpanFull(),
                Select::make('format')
                    ->label('Format Barcode')
                    ->options([
                        'code128' => 'Code 128',
                        'ean13' => 'EAN-13',
                    ])
                    ->default('code128')
                    ->live()
                    ->required(),
                Select::make('size')
                    ->label('Ukuran Label')
                    ->options([
                        'small' => 'Kecil (40mm × 25mm)',
                        'medium' => 'Sedang (50mm × 30mm)',
                        'large' => 'Besar (60mm × 40mm)',
                    ])
                    ->default('medium')
                    ->live()
                    ->required(),
                TextInput::make('copies')
                    ->label('Salinan / Label')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(1)
                    ->live()
                    ->required(),
            ])
            ->statePath('data')
            ->columns(4);
    }

    public function getLabelsProperty(): array
    {
        $data = $this->form->getState();
        $ids = $data['product_ids'] ?? [];

        if (empty($ids)) {
            return [];
        }

        $format = $data['format'] ?? 'code128';
        $copies = (int) ($data['copies'] ?? 1);

        $service = app(ProductBarcodeService::class);
        $job = $service->printLabels(array_map('intval', $ids), $format, $copies);

        return $job['labels'];
    }

    public function getSizeProperty(): string
    {
        return $this->data['size'] ?? 'medium';
    }
}

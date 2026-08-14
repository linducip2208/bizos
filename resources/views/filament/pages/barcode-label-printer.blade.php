<x-filament-panels::page>
    <style>
        .barcode-printer { display: flex; flex-direction: column; gap: 24px; }

        .barcode-controls {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }
        .dark .barcode-controls { background: #1e293b; border-color: #334155; }

        .barcode-preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .barcode-preview-header h2 { font-size: 17px; font-weight: 700; color: #1e293b; }
        .dark .barcode-preview-header h2 { color: #e2e8f0; }
        .barcode-preview-header .hint { font-size: 12px; color: #94a3b8; }

        .barcode-print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 6px 16px -6px rgba(79, 70, 229, .55);
            transition: transform .15s, box-shadow .15s;
        }
        .barcode-print-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .65); }

        .label-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }
        .label-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-align: center;
            break-inside: avoid;
        }
        .dark .label-card { background: #f8fafc; }
        .label-card img { max-width: 100%; height: auto; }
        .label-name { font-size: 12px; font-weight: 700; color: #0f172a; line-height: 1.25; }
        .label-sku { font-size: 10px; color: #64748b; }
        .label-price { font-size: 13px; font-weight: 800; color: #4f46e5; }

        .label-grid.label-small .label-card { width: 40mm; min-height: 25mm; }
        .label-grid.label-medium .label-card { width: 50mm; min-height: 30mm; }
        .label-grid.label-large .label-card { width: 60mm; min-height: 40mm; }
        .label-grid.label-small .label-name { font-size: 10px; }
        .label-grid.label-small .label-price { font-size: 11px; }
        .label-grid.label-large .label-name { font-size: 14px; }
        .label-grid.label-large .label-price { font-size: 15px; }

        .barcode-empty {
            padding: 48px;
            text-align: center;
            color: #94a3b8;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
        }
        .dark .barcode-empty { background: #1e293b; border-color: #334155; }

        @media print {
            @page { size: A4; margin: 8mm; }
            body * { visibility: hidden; }
            #barcode-print-area, #barcode-print-area * { visibility: visible; }
            #barcode-print-area {
                position: absolute;
                inset: 0;
                margin: 0;
                padding: 0;
            }
            .label-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 4mm;
            }
            .label-card {
                border: 1px dashed #94a3b8;
                box-shadow: none;
            }
            .dark .label-card { background: #fff; }
            .label-name { color: #000; }
            .label-sku { color: #000; }
            .label-price { color: #000; }
        }
    </style>

    <div class="barcode-printer">
        <div class="barcode-controls">
            {{ $this->form }}
        </div>

        <div class="barcode-preview-header">
            <div>
                <h2>Pratinjau Label ({{ count($this->labels) }} label)</h2>
                <span class="hint">Label dicetak sesuai ukuran yang dipilih. Gunakan kertas stiker label.</span>
            </div>
            @if (count($this->labels))
                <button type="button" class="barcode-print-btn" onclick="window.print()">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5z"/></svg>
                    Cetak Label
                </button>
            @endif
        </div>

        <div id="barcode-print-area">
            @if (count($this->labels))
                <div class="label-grid label-{{ $this->size }}">
                    @foreach ($this->labels as $label)
                        <div class="label-card">
                            <img src="{{ $label['barcode_image'] }}" alt="Barcode {{ $label['sku'] }}">
                            <div class="label-name">{{ $label['name'] }}</div>
                            <div class="label-sku">SKU: {{ $label['sku'] }}</div>
                            <div class="label-price">Rp {{ number_format($label['price'], 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="barcode-empty">
                    Pilih satu atau lebih produk di atas untuk melihat pratinjau label barcode.
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <style>
        .scanner-container { max-width: 720px; margin: 0 auto; }
        .scan-upload-zone {
            border: 3px dashed #cbd5e1; border-radius: 20px;
            padding: 60px 20px; text-align: center; cursor: pointer;
            transition: all .3s; background: #f8fafc;
        }
        .scan-upload-zone:hover { border-color: #6366f1; background: #eef2ff; }
        .scan-upload-zone.drag-over { border-color: #4f46e5; background: #e0e7ff; }
        .scan-icon {
            font-size: 64px; line-height: 1; margin-bottom: 16px;
            display: block; color: #6366f1;
        }
        .scan-title { font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .scan-subtitle { font-size: 14px; color: #64748b; margin-bottom: 24px; }
        .scan-upload-btn {
            display: inline-block; padding: 12px 32px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff; border: none; border-radius: 12px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            box-shadow: 0 4px 14px rgba(79,70,229,.35);
            transition: all .3s;
        }
        .scan-upload-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,70,229,.45); }

        /* Processing */
        .processing-card {
            background: #fff; border-radius: 16px; padding: 40px 20px;
            text-align: center; border: 1px solid #e2e8f0;
        }
        .scan-animation {
            width: 160px; height: 160px; margin: 0 auto 24px; position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .scan-ring {
            width: 100%; height: 100%; border: 4px solid #e0e7ff;
            border-top-color: #4f46e5; border-radius: 50%;
            animation: scanSpin 1.2s linear infinite;
        }
        .scan-inner {
            position: absolute; font-size: 48px;
            animation: scanPulse 1.8s ease-in-out infinite;
        }
        .scan-line {
            position: absolute; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, #6366f1, transparent);
            animation: scanLine 2.4s ease-in-out infinite;
        }
        .scan-text { font-size: 16px; font-weight: 600; color: #4f46e5; }
        .scan-text-sub { font-size: 13px; color: #64748b; margin-top: 4px; }

        /* Result */
        .result-card {
            background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .result-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff; padding: 20px 24px; display: flex;
            align-items: center; gap: 12px;
        }
        .result-header-icon { font-size: 32px; }
        .result-header h3 { font-size: 18px; font-weight: 700; margin: 0; }
        .result-header p { font-size: 13px; opacity: .85; margin: 2px 0 0; }
        .result-body { padding: 24px; }
        .ocr-field {
            display: flex; align-items: flex-start; padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .ocr-field-label { width: 140px; font-size: 13px; color: #64748b; font-weight: 500; flex-shrink: 0; }
        .ocr-field-value { font-size: 14px; color: #1e293b; font-weight: 600; flex: 1; }
        .ocr-field-value.empty { color: #94a3b8; font-style: italic; }
        .ocr-confidence {
            display: inline-block; padding: 2px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600; margin-left: 8px;
        }
        .ocr-confidence.high { background: #dcfce7; color: #166534; }
        .ocr-confidence.medium { background: #fef3c7; color: #92400e; }
        .ocr-confidence.low { background: #fee2e2; color: #991b1b; }

        .line-items-table { width: 100%; font-size: 13px; margin-top: 12px; }
        .line-items-table th { text-align: left; padding: 6px 8px; font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        .line-items-table td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; }

        .result-actions { display: flex; gap: 12px; padding: 0 24px 24px; flex-wrap: wrap; }

        .btn-primary {
            padding: 10px 24px; border-radius: 10px; font-size: 14px; font-weight: 600;
            border: none; cursor: pointer;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff; box-shadow: 0 4px 12px rgba(79,70,229,.25);
            transition: all .3s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,.4); }
        .btn-secondary {
            padding: 10px 24px; border-radius: 10px; font-size: 14px; font-weight: 500;
            border: 1.5px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer;
            transition: all .3s;
        }
        .btn-secondary:hover { border-color: #6366f1; color: #4f46e5; background: #f8fafc; }
        .btn-success {
            padding: 14px 40px; border-radius: 12px; font-size: 15px; font-weight: 700;
            border: none; cursor: pointer;
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff; box-shadow: 0 4px 14px rgba(5,150,105,.3);
            transition: all .3s; width: 100%;
        }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(5,150,105,.45); }

        .error-card {
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 14px; padding: 24px; text-align: center;
        }
        .error-icon { font-size: 48px; color: #ef4444; margin-bottom: 12px; }
        .error-title { font-size: 16px; font-weight: 700; color: #991b1b; margin-bottom: 8px; }
        .error-text { font-size: 13px; color: #b91c1c; margin-bottom: 16px; }

        .submitted-card {
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 20px; padding: 48px 24px; text-align: center;
        }
        .submitted-icon { font-size: 56px; color: #16a34a; margin-bottom: 16px; }
        .submitted-title { font-size: 22px; font-weight: 700; color: #166534; margin-bottom: 8px; }
        .submitted-text { font-size: 14px; color: #15803d; }

        .edit-section {
            background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
            padding: 24px;
        }
        .edit-section h4 { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }

        .preview-image {
            max-width: 100%; max-height: 300px; border-radius: 12px;
            border: 1px solid #e2e8f0; margin-top: 16px;
        }

        @keyframes scanSpin { to { transform: rotate(360deg); } }
        @keyframes scanPulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.6; transform:scale(.95); } }
        @keyframes scanLine { 0% { top:0; } 50% { top:95%; } 100% { top:0; } }
    </style>

    <div class="scanner-container" x-data="{
        dragging: false,
    }">
        {{-- Upload State --}}
        @if ($state === 'upload')
            <div
                class="scan-upload-zone"
                x-bind:class="{ 'drag-over': dragging }"
                x-on:dragover.prevent="dragging = true"
                x-on:dragleave.prevent="dragging = false"
                x-on:drop.prevent="dragging = false"
                x-on:click="$refs.fileInput.click()"
            >
                <span class="scan-icon">📸</span>
                <div class="scan-title">Pindai Struk Pengeluaran</div>
                <div class="scan-subtitle">
                    Upload foto struk/kwitansi untuk ekstrak data otomatis dengan AI.<br>
                    Format: JPG, PNG, WEBP, PDF. Maks 10 MB.
                </div>
                <span class="scan-upload-btn">📎 Pilih Gambar</span>
                <input
                    type="file"
                    x-ref="fileInput"
                    wire:model="receiptImage"
                    accept="image/jpeg,image/png,image/webp,application/pdf"
                    style="display:none;"
                >
            </div>

            {{-- Provider Check --}}
            <div style="margin-top:24px;padding:14px 18px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;font-size:12px;color:#64748b;">
                @php
                    try {
                        $provider = (new \App\Services\ReceiptOcrService)->getProvider();
                        $providerName = $provider->name . ' (' . ($provider->default_model ?: 'gpt-4o') . ')';
                    } catch (\Throwable $e) {
                        $providerName = '<span style="color:#dc2626;">Tidak tersedia — konfigurasi AI Provider terlebih dahulu</span>';
                    }
                @endphp
                🤖 AI Provider: {!! $providerName !!}
            </div>
        @endif

        {{-- Processing State --}}
        @if ($state === 'processing')
            <div class="processing-card">
                <div class="scan-animation">
                    <div class="scan-ring"></div>
                    <div class="scan-inner">🧾</div>
                    <div class="scan-line"></div>
                </div>
                <div class="scan-text">Memproses struk...</div>
                <div class="scan-text-sub">AI sedang mengekstrak data dari gambar</div>

                @if ($receiptPreview)
                    <img src="{{ $receiptPreview }}" class="preview-image" alt="Receipt preview">
                @endif

                <div wire:poll.1s="ocrBusy"></div>
            </div>
        @endif

        {{-- Result State --}}
        @if ($state === 'result' && $ocrResult)
            <div class="result-card">
                <div class="result-header">
                    <span class="result-header-icon">✅</span>
                    <div>
                        <h3>Data Berhasil Diekstrak</h3>
                        <p>AI berhasil membaca data dari struk</p>
                    </div>
                </div>

                <div class="result-body">
                    {{-- Merchant --}}
                    <div class="ocr-field">
                        <span class="ocr-field-label">🏪 Merchant</span>
                        <span class="ocr-field-value {{ empty($ocrResult['merchant_name']) ? 'empty' : '' }}">
                            {{ $ocrResult['merchant_name'] ?: 'Tidak terdeteksi' }}
                        </span>
                    </div>

                    {{-- Date --}}
                    <div class="ocr-field">
                        <span class="ocr-field-label">📅 Tanggal</span>
                        <span class="ocr-field-value {{ empty($ocrResult['transaction_date']) ? 'empty' : '' }}">
                            {{ $ocrResult['transaction_date'] ?: 'Tidak terdeteksi' }}
                        </span>
                    </div>

                    {{-- Total --}}
                    <div class="ocr-field">
                        <span class="ocr-field-label">💰 Total</span>
                        <span class="ocr-field-value">
                            Rp {{ number_format($ocrResult['total_amount'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Tax --}}
                    <div class="ocr-field">
                        <span class="ocr-field-label">🧾 Pajak</span>
                        <span class="ocr-field-value">
                            Rp {{ number_format($ocrResult['tax_amount'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Payment Method --}}
                    <div class="ocr-field">
                        <span class="ocr-field-label">💳 Pembayaran</span>
                        <span class="ocr-field-value {{ empty($ocrResult['payment_method']) ? 'empty' : '' }}">
                            @php
                                $paymentLabel = match ($ocrResult['payment_method'] ?? '') {
                                    'cash' => 'Tunai',
                                    'card' => 'Kartu',
                                    'transfer' => 'Transfer',
                                    'qris' => 'QRIS',
                                    default => $ocrResult['payment_method'] ?: 'Tidak terdeteksi',
                                };
                            @endphp
                            {{ $paymentLabel }}
                        </span>
                    </div>

                    {{-- Confidence --}}
                    <div class="ocr-field">
                        <span class="ocr-field-label">🎯 Keyakinan</span>
                        <span class="ocr-field-value">
                            @php $conf = $ocrResult['confidence'] ?? null; @endphp
                            @if ($conf !== null)
                                @if ($conf >= 0.85)
                                    <span class="ocr-confidence high">{{ number_format($conf * 100, 0) }}%</span>
                                @elseif ($conf >= 0.6)
                                    <span class="ocr-confidence medium">{{ number_format($conf * 100, 0) }}%</span>
                                @else
                                    <span class="ocr-confidence low">{{ number_format($conf * 100, 0) }}%</span>
                                @endif
                            @else
                                <span class="ocr-field-value empty">N/A</span>
                            @endif
                        </span>
                    </div>

                    {{-- Category Match --}}
                    @if (!empty($ocrResult['category_id']))
                        <div class="ocr-field">
                            <span class="ocr-field-label">📂 Kategori</span>
                            <span class="ocr-field-value">
                                {{ \App\Models\ReimbursementCategory::find($ocrResult['category_id'])?->name ?? '—' }}
                            </span>
                        </div>
                    @endif

                    {{-- Receipt Number --}}
                    @if (!empty($ocrResult['receipt_number']))
                        <div class="ocr-field">
                            <span class="ocr-field-label">🔢 No. Struk</span>
                            <span class="ocr-field-value">{{ $ocrResult['receipt_number'] }}</span>
                        </div>
                    @endif

                    {{-- Line Items --}}
                    @if (!empty($ocrResult['line_items']))
                        <div class="ocr-field" style="flex-direction:column;">
                            <span class="ocr-field-label" style="margin-bottom:8px;">📋 Item</span>
                            <table class="line-items-table">
                                <thead>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th style="text-align:right;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ocrResult['line_items'] as $item)
                                        <tr>
                                            <td>{{ $item['description'] ?? '—' }}</td>
                                            <td style="text-align:right;">
                                                Rp {{ number_format($item['amount'] ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($receiptPreview)
                        <img src="{{ $receiptPreview }}" class="preview-image" alt="Receipt" style="margin-top:16px;">
                    @endif
                </div>

                <div class="result-actions">
                    <button type="button" wire:click="useOcrData" class="btn-primary" style="flex:1;">
                        📝 Lanjut Isi Form
                    </button>
                    <button type="button" wire:click="rescan" class="btn-secondary">
                        🔄 Pindai Ulang
                    </button>
                </div>
            </div>
        @endif

        {{-- Error State --}}
        @if ($state === 'error')
            <div class="error-card">
                <div class="error-icon">⚠️</div>
                <div class="error-title">Gagal Memproses Struk</div>
                <div class="error-text">{{ $ocrError ?: 'Terjadi kesalahan saat memproses gambar.' }}</div>
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                    <button type="button" wire:click="rescan" class="btn-secondary">🔄 Coba Lagi</button>
                    <button type="button" wire:click="backToUpload" class="btn-primary">📝 Isi Manual</button>
                </div>
            </div>
        @endif

        {{-- Edit State — Pre-filled Form --}}
        @if ($state === 'edit')
            <div class="edit-section">
                <h4>Form Pengajuan Reimbursement</h4>

                {{ $this->form }}

                @if ($receiptPreview)
                    <img src="{{ $receiptPreview }}" class="preview-image" alt="Receipt" style="margin-top:16px;">
                @endif

                <div style="display:flex;gap:12px;margin-top:20px;">
                    <button type="button" wire:click="submitReimbursement" class="btn-success">
                        🚀 Ajukan Reimbursement
                    </button>
                    <button type="button" wire:click="rescan" class="btn-secondary" style="flex-shrink:0;">
                        🔄 Pindai Ulang
                    </button>
                </div>
            </div>
        @endif

        {{-- Submitted State --}}
        @if ($state === 'submitted')
            <div class="submitted-card">
                <div class="submitted-icon">🎉</div>
                <div class="submitted-title">Reimbursement Berhasil Diajukan!</div>
                <div class="submitted-text">Pengajuan kamu telah disimpan dan siap diproses.</div>
                <div style="margin-top:24px;">
                    <button type="button" wire:click="rescan" class="btn-primary">
                        📸 Pindai Pengeluaran Baru
                    </button>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>

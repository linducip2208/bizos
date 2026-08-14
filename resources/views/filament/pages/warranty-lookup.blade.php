<x-filament-panels::page>
    <style>
        .wl-wrap { display: flex; flex-direction: column; gap: 24px; max-width: 860px; }

        .wl-search-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }
        .dark .wl-search-card { background: #1e293b; border-color: #334155; }

        .wl-search-card h2 { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 6px; }
        .dark .wl-search-card h2 { color: #e2e8f0; }
        .wl-search-card .sub { font-size: 13px; color: #64748b; margin: 0 0 20px; }

        .wl-search-row { display: flex; gap: 12px; align-items: stretch; }
        .wl-search-input {
            flex: 1;
            min-height: 46px;
            padding: 0 16px;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14px;
            color: #0f172a;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .wl-search-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, .12); }
        .dark .wl-search-input { background: #0f172a; border-color: #475569; color: #e2e8f0; }

        .wl-search-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 46px;
            padding: 0 22px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 6px 16px -6px rgba(79, 70, 229, .55);
            transition: transform .15s, box-shadow .15s;
        }
        .wl-search-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .65); }

        .wl-result-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }
        .dark .wl-result-card { background: #1e293b; border-color: #334155; }

        .wl-result-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .wl-result-header { border-color: #334155; }
        .wl-result-header h3 { font-size: 17px; font-weight: 800; color: #0f172a; margin: 0; }
        .dark .wl-result-header h3 { color: #e2e8f0; }
        .wl-result-header .sn { font-size: 12px; color: #64748b; font-family: 'JetBrains Mono', monospace; }

        .wl-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .wl-badge.active { background: #dcfce7; color: #166534; }
        .wl-badge.expired { background: #fee2e2; color: #991b1b; }
        .wl-badge.void { background: #e2e8f0; color: #475569; }
        .dark .wl-badge.active { background: #14532d; color: #86efac; }
        .dark .wl-badge.expired { background: #7f1d1d; color: #fca5a5; }
        .dark .wl-badge.void { background: #334155; color: #cbd5e1; }

        .wl-result-body { padding: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; }
        .wl-metric .label { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-bottom: 4px; }
        .wl-metric .value { font-size: 15px; font-weight: 700; color: #0f172a; }
        .dark .wl-metric .value { color: #e2e8f0; }
        .wl-metric .value.danger { color: #dc2626; }
        .wl-metric .value.success { color: #16a34a; }

        .wl-claims { border-top: 1px solid #e2e8f0; padding: 20px 24px; }
        .dark .wl-claims { border-color: #334155; }
        .wl-claims h4 { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 12px; }
        .dark .wl-claims h4 { color: #e2e8f0; }
        .wl-claim-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 8px;
        }
        .dark .wl-claim-item { border-color: #334155; }
        .wl-claim-item .cnum { font-weight: 700; font-size: 13px; color: #0f172a; }
        .dark .wl-claim-item .cnum { color: #e2e8f0; }
        .wl-claim-item .cdesc { font-size: 12px; color: #64748b; }
        .wl-claim-item .cdate { font-size: 11px; color: #94a3b8; white-space: nowrap; }

        .wl-empty {
            padding: 56px 24px;
            text-align: center;
            color: #94a3b8;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
        }
        .dark .wl-empty { background: #1e293b; border-color: #334155; }
        .wl-empty .icon { font-size: 40px; margin-bottom: 10px; }
        .wl-empty .title { font-size: 15px; font-weight: 700; color: #64748b; margin-bottom: 4px; }
        .wl-empty .desc { font-size: 13px; }
    </style>

    <div class="wl-wrap">
        <div class="wl-search-card">
            <h2>Cek Garansi Produk</h2>
            <p class="sub">Masukkan nomor seri produk untuk melihat status garansi dan riwayat klaim.</p>

            <form wire:submit="search" class="wl-search-row">
                <input
                    type="text"
                    wire:model="serialNumber"
                    class="wl-search-input"
                    placeholder="Contoh: SN-2024-000123"
                    autofocus
                >
                <button type="submit" class="wl-search-btn">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/></svg>
                    Cek Garansi
                </button>
            </form>
        </div>

        @if (!$this->searched)
            <div class="wl-empty">
                <div class="icon">🔍</div>
                <div class="title">Belum ada pencarian</div>
                <div class="desc">Masukkan nomor seri lalu klik "Cek Garansi" untuk melihat status.</div>
            </div>
        @elseif (!$this->result)
            <div class="wl-empty">
                <div class="icon">😕</div>
                <div class="title">Garansi tidak ditemukan</div>
                <div class="desc">Tidak ada registrasi garansi untuk nomor seri "{{ $serialNumber }}".</div>
            </div>
        @else
            @php
                $status = $this->result->effectiveStatus();
            @endphp
            <div class="wl-result-card">
                <div class="wl-result-header">
                    <div>
                        <h3>{{ $this->result->product?->name ?? 'Produk' }}</h3>
                        <div class="sn">
                            SN: {{ $this->result->serialNumber?->serial_number ?? '-' }}
                        </div>
                    </div>
                    @if ($status === 'active')
                        <span class="wl-badge active">● Aktif</span>
                    @elseif ($status === 'expired')
                        <span class="wl-badge expired">● Kedaluwarsa</span>
                    @else
                        <span class="wl-badge void">● Batal</span>
                    @endif
                </div>

                <div class="wl-result-body">
                    <div class="wl-metric">
                        <div class="label">Garansi</div>
                        <div class="value">{{ $this->result->warranty?->name ?? '-' }}</div>
                    </div>
                    <div class="wl-metric">
                        <div class="label">Mulai</div>
                        <div class="value">{{ $this->result->start_date?->format('d M Y') ?? '-' }}</div>
                    </div>
                    <div class="wl-metric">
                        <div class="label">Berakhir</div>
                        <div class="value">{{ $this->result->end_date?->format('d M Y') ?? '-' }}</div>
                    </div>
                    <div class="wl-metric">
                        <div class="label">Sisa Hari</div>
                        @if ($status === 'active')
                            <div class="value success">{{ $this->result->daysRemaining() }} hari</div>
                        @elseif ($status === 'expired')
                            <div class="value danger">0 hari</div>
                        @else
                            <div class="value">-</div>
                        @endif
                    </div>
                    <div class="wl-metric">
                        <div class="label">Pelanggan</div>
                        <div class="value">{{ $this->result->client?->name ?? '-' }}</div>
                    </div>
                    <div class="wl-metric">
                        <div class="label">Jumlah Klaim</div>
                        <div class="value">{{ $this->result->claims->count() }} klaim</div>
                    </div>
                </div>

                <div class="wl-claims">
                    <h4>Riwayat Klaim</h4>
                    @forelse ($this->result->claims as $claim)
                        <div class="wl-claim-item">
                            <div>
                                <div class="cnum">{{ $claim->claim_number }}</div>
                                <div class="cdesc">{{ \Illuminate\Support\Str::limit($claim->issue_description, 70) }}</div>
                            </div>
                            <div style="text-align:right;">
                                <span class="wl-badge {{ $claim->status === 'resolved' ? 'active' : ($claim->status === 'rejected' ? 'expired' : 'void') }}">
                                    {{ match ($claim->status) {
                                        'submitted' => 'Diajukan',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        'in_progress' => 'Diproses',
                                        'resolved' => 'Selesai',
                                        default => $claim->status,
                                    } }}
                                </span>
                                <div class="cdate">{{ $claim->claim_date?->format('d M Y') ?? '-' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="wl-empty" style="padding: 28px;">Belum ada klaim garansi untuk produk ini.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>

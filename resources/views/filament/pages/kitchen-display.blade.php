<x-filament-panels::page>
    <div wire:poll.15s x-data="kdsBoard">
        <style>
            .kds-wrap { display: flex; flex-direction: column; gap: 16px; color: #f8fafc; }
            .kds-topbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
            .kds-title { font-size: 22px; font-weight: 800; letter-spacing: -0.02em; }
            .kds-live { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #94a3b8; }
            .kds-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; animation: kdsPulse 1.6s ease-in-out infinite; }
            @keyframes kdsPulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .4; transform: scale(1.4); } }

            .kds-columns { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; align-items: start; }
            .kds-col { background: rgba(148,163,184,.06); border: 1px solid rgba(148,163,184,.14); border-radius: 20px; padding: 12px; min-height: 60vh; display: flex; flex-direction: column; gap: 12px; }
            .kds-col-head { display: flex; align-items: center; gap: 8px; padding: 6px 8px; }
            .kds-col-head h2 { font-size: 16px; font-weight: 800; margin: 0; letter-spacing: .02em; }
            .kds-col-count { margin-left: auto; font-size: 14px; font-weight: 800; min-width: 28px; height: 28px; border-radius: 9px; display: flex; align-items: center; justify-content: center; }

            .kds-card { background: #0f172a; border: 1px solid #1e293b; border-radius: 18px; padding: 14px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 10px 30px -14px rgba(0,0,0,.5); }
            .kds-card.high { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,.35), 0 10px 30px -14px rgba(0,0,0,.6); }
            .kds-card-head { display: flex; align-items: center; gap: 10px; }
            .kds-table-badge { font-size: 20px; font-weight: 800; background: #1e293b; padding: 6px 14px; border-radius: 12px; }
            .kds-order-no { font-size: 12px; color: #64748b; font-weight: 600; }
            .kds-priority { font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; margin-left: auto; }
            .kds-priority.normal { background: #1e293b; color: #94a3b8; }
            .kds-priority.high { background: #7f1d1d; color: #fecaca; animation: kdsPulse 1.6s ease-in-out infinite; }
            .kds-timer { font-size: 26px; font-weight: 800; font-variant-numeric: tabular-nums; color: #fbbf24; }

            .kds-items { display: flex; flex-direction: column; gap: 8px; }
            .kds-item { background: #1e293b; border-radius: 14px; padding: 10px 12px; display: flex; align-items: center; gap: 10px; }
            .kds-item-qty { font-size: 18px; font-weight: 800; min-width: 30px; text-align: center; background: #0f172a; border-radius: 9px; padding: 4px 6px; }
            .kds-item-main { flex: 1; min-width: 0; }
            .kds-item-name { font-size: 16px; font-weight: 700; line-height: 1.2; }
            .kds-item-sub { font-size: 12px; color: #94a3b8; margin-top: 2px; }
            .kds-item-status { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 8px; text-align: center; }
            .kds-item-status.pending { background: #334155; color: #cbd5e1; }
            .kds-item-status.preparing { background: #1e3a8a; color: #bfdbfe; }
            .kds-item-status.ready { background: #14532d; color: #bbf7d0; }
            .kds-item-status.served { background: #1e293b; color: #64748b; }

            .kds-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: none; border-radius: 12px; font-weight: 800; cursor: pointer; transition: transform .12s, filter .12s; line-height: 1; min-height: 46px; padding: 10px 16px; font-size: 15px; color: #fff; }
            .kds-btn:active { transform: scale(.96); }
            .kds-btn-sm { min-height: 42px; padding: 8px 14px; font-size: 14px; white-space: nowrap; }
            .kds-btn-start { background: linear-gradient(135deg, #2563eb, #4f46e5); }
            .kds-btn-ready { background: linear-gradient(135deg, #059669, #10b981); }
            .kds-btn-serve { background: linear-gradient(135deg, #334155, #475569); }
            .kds-btn-danger { background: transparent; color: #fca5a5; border: 1.5px solid #7f1d1d; }
            .kds-btn-danger:hover { background: #7f1d1d; }
            .kds-btn:hover { filter: brightness(1.1); }

            .kds-footer { display: flex; gap: 8px; }
            .kds-empty { text-align: center; padding: 40px 10px; color: #475569; font-size: 14px; font-weight: 600; }

            .kds-note { background: #172033; border-left: 3px solid #f59e0b; border-radius: 10px; padding: 8px 12px; font-size: 13px; color: #fcd34d; }

            @media (max-width: 1023px) { .kds-columns { grid-template-columns: 1fr; } .kds-col { min-height: auto; } }
            @media (max-width: 640px) { .kds-title { font-size: 18px; } .kds-item-name { font-size: 14px; } .kds-table-badge { font-size: 17px; } }
        </style>

        <div class="kds-wrap">
            <div class="kds-topbar">
                <div class="kds-title">🍳 Kitchen Display System</div>
                <span class="kds-live"><span class="kds-live-dot"></span> Auto-refresh 15 detik · {{ $this->dashboard['now']->format('H:i:s') }}</span>
                <button type="button" wire:click="$refresh" class="kds-btn kds-btn-sm" style="background:#1e293b;margin-left:auto;">🔄 Segarkan</button>
            </div>

            <div class="kds-columns">
                {{-- ===================== PENDING ===================== --}}
                <div class="kds-col">
                    <div class="kds-col-head" style="color:#fbbf24;">
                        <h2>⏳ Menunggu</h2>
                        <span class="kds-col-count" style="background:#451a03;color:#fbbf24;">{{ $this->dashboard['pending']->count() }}</span>
                    </div>
                    @forelse ($this->dashboard['pending'] as $order)
                        @include('filament.pages.kitchen-order-card', ['order' => $order])
                    @empty
                        <div class="kds-empty">Tidak ada pesanan menunggu.</div>
                    @endforelse
                </div>

                {{-- ===================== PREPARING ===================== --}}
                <div class="kds-col">
                    <div class="kds-col-head" style="color:#60a5fa;">
                        <h2>🔥 Disiapkan</h2>
                        <span class="kds-col-count" style="background:#1e3a8a;color:#60a5fa;">{{ $this->dashboard['preparing']->count() }}</span>
                    </div>
                    @forelse ($this->dashboard['preparing'] as $order)
                        @include('filament.pages.kitchen-order-card', ['order' => $order])
                    @empty
                        <div class="kds-empty">Tidak ada pesanan disiapkan.</div>
                    @endforelse
                </div>

                {{-- ===================== READY ===================== --}}
                <div class="kds-col">
                    <div class="kds-col-head" style="color:#34d399;">
                        <h2>✅ Siap Disajikan</h2>
                        <span class="kds-col-count" style="background:#14532d;color:#34d399;">{{ $this->dashboard['ready']->count() }}</span>
                    </div>
                    @forelse ($this->dashboard['ready'] as $order)
                        @include('filament.pages.kitchen-order-card', ['order' => $order])
                    @empty
                        <div class="kds-empty">Tidak ada pesanan siap.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

@script
<script>
    Alpine.data('kdsBoard', () => ({
        now: Date.now(),
        init() {
            setInterval(() => {
                this.now = Date.now();
            }, 1000);
        },
        elapsed(orderedAt) {
            const s = Math.max(0, Math.floor((this.now - new Date(orderedAt).getTime()) / 1000));
            const m = Math.floor(s / 60);
            const sec = s % 60;
            if (m >= 60) {
                const h = Math.floor(m / 60);
                return `${h}j ${m % 60}m`;
            }
            if (m > 0) return `${m}m ${sec}s`;
            return `${sec}s`;
        }
    }));
</script>
@endscript

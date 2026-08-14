<div class="kds-card {{ $order->isHighPriority() ? 'high' : '' }}">
    <div class="kds-card-head">
        <span class="kds-table-badge">🍽️ {{ $order->getTableLabel() }}</span>
        <div>
            <div class="kds-order-no">#{{ $order->order_number }}</div>
            <div class="kds-timer" x-text="elapsed('{{ $order->ordered_at->toIso8601String() }}')">0s</div>
        </div>
        <span class="kds-priority {{ $order->isHighPriority() ? 'high' : 'normal' }}">
            {{ $order->isHighPriority() ? '⚠ PRIORITAS' : 'Normal' }}
        </span>
    </div>

    @if ($order->note)
        <div class="kds-note">📝 {{ $order->note }}</div>
    @endif

    <div class="kds-items">
        @foreach ($order->items as $item)
            <div class="kds-item">
                <span class="kds-item-qty">{{ number_format($item->quantity, 0, ',', '.') }}×</span>
                <div class="kds-item-main">
                    <div class="kds-item-name">{{ $item->product?->name ?? 'Produk' }}</div>
                    @if (!empty($item->modifiers) || $item->notes)
                        <div class="kds-item-sub">
                            @if (!empty($item->modifiers))
                                + {{ implode(', ', array_map(fn ($m) => is_array($m) ? ($m['name'] ?? '') : $m, $item->modifiers)) }}
                            @endif
                            @if ($item->notes)
                                <span style="color:#fcd34d;"> · {{ $item->notes }}</span>
                            @endif
                        </div>
                    @endif
                </div>
                <span class="kds-item-status {{ $item->status }}">
                    {{ match ($item->status) {
                        'pending' => '⏳ Menunggu',
                        'preparing' => '🔥 Disiapkan',
                        'ready' => '✅ Siap',
                        'served' => '🍽 Disajikan',
                        'cancelled' => '✕ Batal',
                        default => $item->status,
                    } }}
                </span>
                @if ($item->isPending())
                    <button type="button" wire:click="updateItemStatus({{ $item->id }}, 'preparing')" class="kds-btn kds-btn-sm kds-btn-start">▶ Mulai</button>
                @elseif ($item->isPreparing())
                    <button type="button" wire:click="updateItemStatus({{ $item->id }}, 'ready')" class="kds-btn kds-btn-sm kds-btn-ready">✓ Siap</button>
                @elseif ($item->isReady())
                    <button type="button" wire:click="updateItemStatus({{ $item->id }}, 'served')" class="kds-btn kds-btn-sm kds-btn-serve">🍽 Sajikan</button>
                @endif
            </div>
        @endforeach
    </div>

    <div class="kds-footer">
        <button type="button" wire:click="updateOrderStatus({{ $order->id }}, 'served')" class="kds-btn kds-btn-sm kds-btn-serve" style="flex:1;">🍽 Sajikan Semua</button>
        <button type="button" wire:click="updateOrderStatus({{ $order->id }}, 'cancelled')" class="kds-btn kds-btn-sm kds-btn-danger">✕ Batalkan</button>
    </div>
</div>

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Metrics --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::section>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Diskon Diberikan</div>
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($metrics['total_discount'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">🏷️</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Redemptions</div>
                        <div class="text-2xl font-bold text-success-600 dark:text-success-400">{{ number_format($metrics['redemptions'] ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-success-50 dark:bg-success-900/30 flex items-center justify-center text-success-600 dark:text-success-400">🎟️</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Order (dengan diskon)</div>
                <div class="text-2xl font-bold text-warning-600 dark:text-warning-400">Rp {{ number_format($metrics['avg_with'] ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">tanpa diskon: Rp {{ number_format($metrics['avg_without'] ?? 0, 0, ',', '.') }}</div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Kupon Auto-Apply</div>
                <div class="text-2xl font-bold text-info-600 dark:text-info-400">{{ $couponStats['auto_apply'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 mt-1">dari {{ $couponStats['active'] ?? 0 }} kupon aktif</div>
            </x-filament::section>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Active Promotions --}}
            <div class="lg:col-span-2 space-y-4">
                <x-filament::section>
                    <x-slot name="heading">📢 Promosi Aktif</x-slot>
                    <x-slot name="description">Nyalakan auto-apply agar promosi otomatis diterapkan di kasir.</x-slot>

                    <div class="space-y-3">
                        @forelse ($activePromotions as $p)
                            <div class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold truncate">{{ $p['name'] }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">{{ $p['type_label'] }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                            {{ $p['status'] === 'active' ? 'bg-success-50 text-success-700 dark:bg-success-900/40 dark:text-success-300' : ($p['status'] === 'scheduled' ? 'bg-info-50 text-info-700 dark:bg-info-900/40 dark:text-info-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400') }}">
                                            {{ $p['status'] === 'active' ? 'Aktif' : ($p['status'] === 'scheduled' ? 'Terjadwal' : ($p['status'] === 'expired' ? 'Kedaluwarsa' : 'Nonaktif')) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $p['discount_label'] }}</span>
                                        · {{ $p['start_date'] }} — {{ $p['end_date'] }}
                                        · {{ $p['coupons_count'] }} kupon · {{ $p['used_count'] }} terpakai
                                        @if ($p['stacking_allowed'])
                                            · <span class="text-amber-600 dark:text-amber-400">boleh ditumpuk</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <button type="button" wire:click="togglePromotionAutoApply({{ $p['id'] }})"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $p['auto_apply'] ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-700' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $p['auto_apply'] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                    <div class="text-[10px] text-gray-400 mt-1">{{ $p['auto_apply'] ? 'Auto' : 'Manual' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-8 text-sm">Belum ada promosi. Buat promosi baru di bawah.</div>
                        @endforelse
                    </div>
                </x-filament::section>

                {{-- Recent discount activity --}}
                <x-filament::section>
                    <x-slot name="heading">🕘 Aktivitas Diskon Terbaru</x-slot>
                    <div class="space-y-2 max-h-72 overflow-y-auto">
                        @forelse ($recentActivity as $a)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-danger-50 dark:bg-danger-900/30 flex items-center justify-center text-danger-500">%</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium">{{ $a['receipt_number'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $a['date'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-danger-500">−Rp {{ number_format($a['discount'], 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-400">Total Rp {{ number_format($a['grand_total'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-8 text-sm">Belum ada transaksi dengan diskon.</div>
                        @endforelse
                    </div>
                </x-filament::section>
            </div>

            {{-- Right column --}}
            <div class="space-y-4">
                {{-- Expiring soon --}}
                <x-filament::section>
                    <x-slot name="heading">⏳ Segera Berakhir</x-slot>
                    <div class="space-y-2">
                        @forelse ($expiringSoon as $p)
                            <div class="flex items-center gap-2 p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800">
                                <span class="text-lg">⚠️</span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium truncate">{{ $p['name'] }}</div>
                                    <div class="text-xs text-amber-600 dark:text-amber-400">{{ $p['days_left'] }} hari lagi · {{ $p['end_date'] }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-6 text-sm">Tidak ada promosi yang segera berakhir.</div>
                        @endforelse
                    </div>
                </x-filament::section>

                {{-- Coupon usage --}}
                <x-filament::section>
                    <x-slot name="heading">🎟️ Kupon</x-slot>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/50 text-center">
                            <div class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ $couponStats['total'] ?? 0 }}</div>
                            <div class="text-[11px] text-gray-500">Total Kupon</div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/50 text-center">
                            <div class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ number_format($couponStats['usage_rate'] ?? 0, 1) }}%</div>
                            <div class="text-[11px] text-gray-500">Tingkat Pakai</div>
                        </div>
                    </div>
                    <div class="space-y-2 max-h-72 overflow-y-auto">
                        @forelse ($couponList as $c)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-mono font-semibold">{{ $c['code'] }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $c['discount_type'] === 'percentage' ? $c['discount'] . '%' : 'Rp ' . number_format($c['discount'], 0, ',', '.') }}
                                        · {{ $c['used_count'] }}{{ $c['max_uses'] ? ' / ' . $c['max_uses'] : '' }}
                                    </div>
                                </div>
                                <button type="button" wire:click="toggleCouponAutoApply({{ $c['id'] }})"
                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors {{ $c['auto_apply'] ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-700' }}">
                                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform {{ $c['auto_apply'] ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                                </button>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-6 text-sm">Belum ada kupon aktif.</div>
                        @endforelse
                    </div>
                </x-filament::section>

                {{-- Quick create --}}
                <x-filament::section>
                    <x-slot name="heading">⚡ Buat Promosi Cepat</x-slot>
                    <form wire:submit="createQuickPromotion" class="space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Nama Promosi</label>
                            <input type="text" wire:model="quickName" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm" placeholder="Contoh: Diskon 10% Akhir Pekan">
                            @error('quickName') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Tipe</label>
                                <select wire:model="quickType" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm">
                                    <option value="discount_percent">Diskon %</option>
                                    <option value="discount_amount">Diskon Rp</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Nilai</label>
                                <input type="number" min="0" step="0.01" wire:model="quickValue" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm" placeholder="10">
                                @error('quickValue') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Mulai</label>
                                <input type="date" wire:model="quickStart" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm">
                                @error('quickStart') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Selesai</label>
                                <input type="date" wire:model="quickEnd" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm">
                                @error('quickEnd') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-gray-300">
                                <input type="checkbox" wire:model="quickAutoApply" class="rounded border-gray-300 text-primary-600"> Auto-apply
                            </label>
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-gray-300">
                                <input type="checkbox" wire:model="quickStacking" class="rounded border-gray-300 text-primary-600"> Boleh ditumpuk
                            </label>
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold py-2.5 transition">Buat Promosi</button>
                    </form>
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>

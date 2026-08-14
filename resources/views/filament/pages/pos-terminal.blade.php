<x-filament-panels::page x-data="posTerminal">
    <style>
        .pos-wrap { display: flex; flex-direction: column; gap: 16px; }
        .pos-grid { display: grid; grid-template-columns: minmax(0, 1fr) 400px; gap: 16px; align-items: start; }
        .pos-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.04); overflow: hidden; }
        .dark .pos-card { background: #1f2937; border-color: #374151; }
        .pos-card-head { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; }
        .dark .pos-card-head { border-color: #374151; }
        .pos-card-head h2 { font-size: 15px; font-weight: 700; color: #111827; margin: 0; }
        .dark .pos-card-head h2 { color: #f9fafb; }
        .pos-body { padding: 16px; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; max-height: 58vh; overflow-y: auto; padding: 4px; }
        .product-card {
            border: 1px solid #e5e7eb; border-radius: 14px; padding: 12px;
            display: flex; flex-direction: column; gap: 6px; cursor: pointer;
            transition: transform .15s, box-shadow .15s, border-color .15s; background: #fff; position: relative;
        }
        .dark .product-card { background: #1f2937; border-color: #374151; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px -12px rgba(79,70,229,.25); border-color: #6366f1; }
        .product-thumb {
            height: 72px; border-radius: 10px; background: linear-gradient(135deg, #eef2ff, #f5f3ff);
            display: flex; align-items: center; justify-content: center; font-size: 30px; overflow: hidden;
        }
        .dark .product-thumb { background: #111827; }
        .product-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .product-name { font-size: 13px; font-weight: 600; color: #111827; line-height: 1.25; }
        .dark .product-name { color: #f9fafb; }
        .product-price { font-size: 13px; font-weight: 800; color: #4f46e5; }
        .product-stock { font-size: 11px; color: #94a3b8; }
        .product-variant-chips { display: flex; flex-wrap: wrap; gap: 4px; }
        .variant-chip { font-size: 10px; padding: 2px 8px; border-radius: 20px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; cursor: pointer; }
        .dark .variant-chip { background: #111827; color: #cbd5e1; border-color: #374151; }
        .variant-chip:hover { background: #6366f1; color: #fff; border-color: #6366f1; }

        .cat-bar { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; }
        .cat-pill { white-space: nowrap; padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1.5px solid #e2e8f0; color: #475569; background: #fff; transition: all .15s; }
        .dark .cat-pill { background: #1f2937; border-color: #374151; color: #cbd5e1; }
        .cat-pill:hover { border-color: #6366f1; color: #4f46e5; }
        .cat-pill.active { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; border-color: transparent; }

        .cart-items { display: flex; flex-direction: column; gap: 8px; max-height: 42vh; overflow-y: auto; }
        .cart-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #f1f5f9; border-radius: 12px; background: #fafafa; }
        .dark .cart-item { background: #111827; border-color: #374151; }
        .cart-item-info { flex: 1; min-width: 0; }
        .cart-item-name { font-size: 13px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .cart-item-name { color: #f9fafb; }
        .cart-item-price { font-size: 12px; color: #6b7280; }
        .dark .cart-item-price { color: #9ca3af; }
        .qty-btn { width: 26px; height: 26px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; color: #475569; font-weight: 700; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .dark .qty-btn { background: #1f2937; border-color: #374151; color: #e5e7eb; }
        .qty-btn:hover { background: #6366f1; color: #fff; border-color: #6366f1; }
        .qty-val { font-size: 13px; font-weight: 700; color: #111827; min-width: 22px; text-align: center; }
        .dark .qty-val { color: #f9fafb; }

        .total-row { display: flex; justify-content: space-between; font-size: 13px; color: #475569; padding: 3px 0; }
        .dark .total-row { color: #cbd5e1; }
        .total-grand { display: flex; justify-content: space-between; align-items: center; font-size: 20px; font-weight: 800; color: #111827; border-top: 2px dashed #e5e7eb; padding-top: 12px; margin-top: 6px; }
        .dark .total-grand { color: #f9fafb; border-color: #374151; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 11px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer;
            border: 1.5px solid transparent; transition: all .18s; line-height: 1;
        }
        .btn-primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; box-shadow: 0 4px 14px rgba(79,70,229,.3); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(79,70,229,.4); }
        .btn-success { background: linear-gradient(135deg, #059669, #10b981); color: #fff; box-shadow: 0 4px 14px rgba(5,150,105,.3); }
        .btn-success:hover { transform: translateY(-1px); }
        .btn-ghost { background: #fff; color: #475569; border-color: #e2e8f0; }
        .dark .btn-ghost { background: #1f2937; color: #e5e7eb; border-color: #374151; }
        .btn-ghost:hover { border-color: #6366f1; color: #4f46e5; }
        .btn-danger { background: #fff; color: #dc2626; border-color: #fecaca; }
        .btn-danger:hover { background: #fef2f2; }
        .btn-lg { padding: 14px 20px; font-size: 15px; }

        .input {
            width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid #e2e8f0;
            font-size: 14px; background: #fff; color: #111827; outline: none; transition: border-color .15s, box-shadow .15s;
        }
        .dark .input { background: #111827; border-color: #374151; color: #f9fafb; }
        .input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .input-sm { padding: 8px 12px; font-size: 13px; }

        .pay-method-btn { padding: 10px 14px; border-radius: 12px; border: 1.5px solid #e2e8f0; background: #fff; font-size: 13px; font-weight: 600; cursor: pointer; color: #475569; transition: all .15s; }
        .dark .pay-method-btn { background: #1f2937; border-color: #374151; color: #e5e7eb; }
        .pay-method-btn:hover, .pay-method-btn.active { border-color: #4f46e5; color: #4f46e5; background: #eef2ff; }
        .dark .pay-method-btn:hover, .dark .pay-method-btn.active { background: #312e81; color: #c7d2fe; }

        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); backdrop-filter: blur(3px); z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .modal { background: #fff; border-radius: 20px; width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px -15px rgba(0,0,0,.3); }
        .dark .modal { background: #1f2937; }
        .modal-head { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
        .dark .modal-head { border-color: #374151; }
        .modal-head h3 { font-size: 16px; font-weight: 700; color: #111827; margin: 0; }
        .dark .modal-head h3 { color: #f9fafb; }
        .modal-body { padding: 20px; }

        .kbd { display: inline-block; padding: 2px 7px; border-radius: 6px; background: #f1f5f9; border: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #475569; font-family: ui-monospace, monospace; }
        .dark .kbd { background: #111827; border-color: #374151; color: #cbd5e1; }

        .receipt-hero { text-align: center; padding: 20px 0; }
        .receipt-check { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #059669, #10b981); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 14px; box-shadow: 0 10px 30px -8px rgba(5,150,105,.5); }
        .receipt-total { font-size: 30px; font-weight: 800; color: #111827; }
        .dark .receipt-total { color: #f9fafb; }

        .receipt-preview { background: #fff; border: 1px dashed #d1d5db; border-radius: 12px; padding: 16px; max-height: 40vh; overflow-y: auto; }
        .dark .receipt-preview { background: #0b0f19; border-color: #4b5563; }

        .scan-indicator { animation: scanPulse 1.8s ease-in-out infinite; }
        @keyframes scanPulse { 0%,100% { opacity: 1; } 50% { opacity: .55; } }

        @media (max-width: 1100px) {
            .pos-grid { grid-template-columns: 1fr; }
            .product-grid { max-height: 44vh; }
        }
        @media (max-width: 640px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); }
            .btn-lg { width: 100%; }
        }
    </style>

    <div class="pos-wrap">
        @if ($error)
            <div class="flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-medium bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                <span>⚠️</span>{{ $error }}
                <button type="button" wire:click="$set('error', null)" class="ml-auto text-red-500 font-bold">&times;</button>
            </div>
        @endif

        <div class="pos-grid">
            {{-- ============ LEFT: PRODUCTS ============ --}}
            <div class="pos-card">
                <div class="pos-card-head">
                    <h2>🛍️ Produk</h2>
                    <div class="relative flex-1">
                        <input
                            type="text"
                            class="input input-sm"
                            placeholder="Cari produk / scan barcode (F2)..."
                            wire:model.live.debounce.250ms="search"
                            x-ref="searchInput"
                        >
                    </div>
                    <button type="button" wire:click="$set('search', '')" class="btn btn-ghost" style="padding:8px 12px;">✕</button>
                    <button type="button" wire:click="openHoldModal" class="btn btn-ghost" style="padding:8px 12px;">⏸ Hold</button>
                </div>

                <div class="pos-body">
                    <div class="cat-bar mb-3">
                        <button type="button" wire:click="$set('categoryId', null)" class="cat-pill {{ $categoryId === null ? 'active' : '' }}">Semua</button>
                        @foreach ($this->categories as $cat)
                            <button type="button" wire:click="$set('categoryId', {{ $cat->id }})" class="cat-pill {{ $categoryId === $cat->id ? 'active' : '' }}">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    @if ($this->products->isEmpty())
                        <div class="text-center py-16 text-gray-400">
                            <div style="font-size:40px;">🛒</div>
                            <p class="text-sm mt-2">Tidak ada produk ditemukan.</p>
                        </div>
                    @else
                        <div class="product-grid">
                            @foreach ($this->products as $product)
                                <div class="product-card" wire:click="addToCart({{ $product->id }})">
                                    <div class="product-thumb">
                                        @if ($product->photo)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->photo) }}" alt="{{ $product->name }}">
                                        @else
                                            🛍️
                                        @endif
                                    </div>
                                    <div class="product-name">{{ $product->name }}</div>
                                    <div class="product-price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
                                    @if ($product->modifierGroups->isNotEmpty())
                                        <div class="text-xs font-semibold" style="color:#7c3aed;">🧩 {{ $product->modifierGroups->count() }} grup modifier</div>
                                    @endif
                                    <div class="product-stock">
                                        Stok: {{ number_format($product->stock, 0, ',', '.') }}
                                        @if ($product->variants->isNotEmpty())
                                            · {{ $product->variants->count() }} varian
                                        @endif
                                    </div>
                                    @if ($product->variants->isNotEmpty())
                                        <div class="product-variant-chips" @click.stop>
                                            @foreach ($product->variants as $variant)
                                                <button type="button" wire:click="addVariantToCart({{ $variant->id }})" class="variant-chip">
                                                    {{ $variant->name }} (+{{ number_format($variant->price_adjustment, 0, ',', '.') }})
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ============ RIGHT: CART ============ --}}
            <div class="pos-card" style="position: sticky; top: 0;">
                <div class="pos-card-head">
                    <h2>🧾 Keranjang</h2>
                    <span class="text-xs text-gray-400 font-semibold ml-auto">{{ count($cart) }} item</span>
                    @if (!empty($cart))
                        <button type="button" wire:click="clearCart" class="text-xs font-semibold text-red-500 hover:underline">Kosongkan</button>
                    @endif
                </div>

                <div class="pos-body">
                    <div class="cart-items mb-4">
                        @forelse ($cart as $key => $item)
                            <div class="cart-item">
                                <div class="cart-item-info">
                                    <div class="cart-item-name">{{ $item['name'] }}</div>
                                    @if (!empty($item['modifiers']))
                                        <div class="text-xs" style="color:#7c3aed;">
                                            + {{ implode(', ', array_map(fn ($m) => $m['name'], $item['modifiers'])) }}
                                        </div>
                                    @endif
                                    <div class="cart-item-price">
                                        {{ number_format($item['quantity'], 0, ',', '.') }} × Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                        = <b>Rp {{ number_format($item['quantity'] * $item['unit_price'], 0, ',', '.') }}</b>
                                    </div>
                                </div>
                                <button type="button" wire:click="decrementQty('{{ $key }}')" class="qty-btn">−</button>
                                <span class="qty-val">{{ number_format($item['quantity'], 0, ',', '.') }}</span>
                                <button type="button" wire:click="incrementQty('{{ $key }}')" class="qty-btn">+</button>
                                <button type="button" wire:click="removeItem('{{ $key }}')" class="qty-btn" style="color:#dc2626;">🗑</button>
                            </div>
                        @empty
                            <div class="text-center py-10 text-gray-400">
                                <div style="font-size:36px;">🧺</div>
                                <p class="text-sm mt-2">Keranjang kosong. Klik produk untuk menambah.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Member --}}
                    <div class="mb-3">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">👤 Member</label>
                        @if ($this->member)
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-indigo-50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-800">
                                <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">{{ $this->member->name }}</span>
                                <span class="text-xs text-indigo-500">{{ $this->member->member_code }} · {{ $this->member->tier }}</span>
                                <button type="button" wire:click="clearMember" class="ml-auto text-xs font-bold text-red-500">&times;</button>
                            </div>
                        @else
                            <div class="relative">
                                <input type="text" class="input input-sm" placeholder="Cari member (nama/HP/kode)..." wire:model.live.debounce.300ms="memberSearch">
                                @if ($this->memberResults->isNotEmpty())
                                    <div class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden">
                                        @foreach ($this->memberResults as $m)
                                            <button type="button" wire:click="setMember({{ $m->id }})" class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                                {{ $m->name }} <span class="text-xs text-gray-400">({{ $m->phone }})</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Voucher --}}
                    <div class="mb-3">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">🎟️ Voucher</label>
                        @if ($appliedVoucher)
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800">
                                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $appliedVoucher['code'] }}</span>
                                <span class="text-xs text-emerald-600">−Rp {{ number_format($voucherDiscount, 0, ',', '.') }}</span>
                                <button type="button" wire:click="removeVoucher" class="ml-auto text-xs font-bold text-red-500">&times;</button>
                            </div>
                        @else
                            <div class="flex gap-2">
                                <input type="text" class="input input-sm flex-1" placeholder="Masukkan kode voucher" wire:model="voucherCode">
                                <button type="button" wire:click="applyVoucher" class="btn btn-ghost" style="padding:8px 14px;">Terapkan</button>
                            </div>
                        @endif
                    </div>

                    {{-- Discount --}}
                    <div class="mb-3">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">🏷️ Diskon Manual (Rp)</label>
                        <div class="flex gap-2">
                            <input type="number" min="0" class="input input-sm flex-1" placeholder="0" wire:model="extraDiscount">
                            <button type="button" wire:click="applyExtraDiscount" class="btn btn-ghost" style="padding:8px 14px;">Terapkan</button>
                        </div>
                    </div>

                    {{-- Auto Discount --}}
                    <div class="mb-3">
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">⚡ Diskon Otomatis</label>
                            <button type="button" wire:click="toggleAutoApply" class="text-xs font-semibold {{ $autoApplyEnabled ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400' }}">
                                {{ $autoApplyEnabled ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </div>
                        @if ($autoApplyEnabled && !empty($this->autoDiscounts['applied_discounts']))
                            <div class="space-y-1 p-2.5 rounded-xl bg-amber-50 border border-amber-100 dark:bg-amber-900/20 dark:border-amber-800">
                                @foreach ($this->autoDiscounts['applied_discounts'] as $d)
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="text-amber-700 dark:text-amber-300 font-semibold">{{ $d['name'] }}</span>
                                        <span class="ml-auto text-amber-600 dark:text-amber-400">−Rp {{ number_format($d['amount'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @elseif ($autoApplyEnabled)
                            <div class="text-xs text-gray-400">Tidak ada diskon otomatis yang berlaku.</div>
                        @else
                            <div class="text-xs text-gray-400">Diskon otomatis dimatikan untuk transaksi ini.</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">📝 Catatan</label>
                        <input type="text" class="input input-sm" placeholder="Catatan transaksi (opsional)" wire:model="notes">
                    </div>

                    {{-- Service Type --}}
                    <div class="mb-3">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">🍽️ Tipe Layanan</label>
                        @if ($this->serviceTypes->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($this->serviceTypes as $st)
                                    <button type="button" wire:click="setServiceType({{ $st->id }})" class="pay-method-btn {{ $serviceTypeId === $st->id ? 'active' : '' }}">
                                        {{ $st->name }}
                                    </button>
                                @endforeach
                                @if ($serviceTypeId)
                                    <button type="button" wire:click="clearServiceType" class="pay-method-btn" style="color:#dc2626;">✕</button>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-gray-400">Belum ada tipe layanan. Tambahkan di menu <b>Tipe Layanan</b>.</p>
                        @endif

                        @if ($this->serviceType?->isDineIn())
                            <div class="mt-2">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">🪑 Meja (opsional)</label>
                                <select class="input input-sm" wire:model="tableId">
                                    <option value="">— Tanpa meja —</option>
                                    @foreach ($this->tables as $table)
                                        <option value="{{ $table->id }}">{{ $table->name }} ({{ $table->capacity }} kursi)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-2">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">🧑‍🍳 Pelayan / Waiter (opsional)</label>
                                <select class="input input-sm" wire:model="serviceStaffId">
                                    <option value="">— Tanpa pelayan —</option>
                                    @foreach ($this->serviceStaff as $staff)
                                        <option value="{{ $staff->id }}">{{ trim($staff->first_name . ' ' . ($staff->last_name ?? '')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if ($this->serviceType?->isDelivery())
                            <div class="mt-2 space-y-2">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">📍 Alamat Pengiriman</label>
                                    <textarea class="input input-sm" rows="2" placeholder="Alamat pengiriman lengkap" wire:model="deliveryAddress"></textarea>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">🛵 Biaya Antar</label>
                                    <input type="number" min="0" class="input input-sm" placeholder="0" wire:model="deliveryFee">
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Totals --}}
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-4 mb-3">
                        <div class="total-row"><span>Subtotal</span><span>Rp {{ number_format($this->totals['subtotal'], 0, ',', '.') }}</span></div>
                        <div class="total-row"><span>Diskon</span><span class="text-red-500">−Rp {{ number_format($this->totals['discount_total'], 0, ',', '.') }}</span></div>
                        <div class="total-row"><span>Pajak</span><span>Rp {{ number_format($this->totals['tax_total'], 0, ',', '.') }}</span></div>
                        @if (($this->totals['pack_charge'] ?? 0) > 0)
                            <div class="total-row"><span>Biaya Kemasan</span><span>Rp {{ number_format($this->totals['pack_charge'], 0, ',', '.') }}</span></div>
                        @endif
                        @if (($this->totals['delivery_fee'] ?? 0) > 0)
                            <div class="total-row"><span>Biaya Antar</span><span>Rp {{ number_format($this->totals['delivery_fee'], 0, ',', '.') }}</span></div>
                        @endif
                        <div class="total-grand">
                            <span>Total</span>
                            <span>Rp {{ number_format($this->totals['grand_total'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <button type="button" wire:click="openHoldModal" class="btn btn-ghost flex-1">⏸ Hold</button>
                        <button type="button" wire:click="openPayment" class="btn btn-success btn-lg flex-[2]" {{ empty($cart) ? 'disabled style=opacity:.5;cursor:not-allowed' : '' }}>
                            💳 Bayar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs text-gray-400 flex-wrap">
            <span><span class="kbd">F2</span> Fokus pencarian</span>
            <span><span class="kbd">Enter</span> Tambah produk pertama</span>
            <span><span class="kbd">F9</span> Hold</span>
            <span><span class="kbd">F10</span> Bayar</span>
            <span class="scan-indicator">● Siap menerima scan barcode</span>
        </div>
    </div>

    {{-- ============ PAYMENT MODAL ============ --}}
    @if ($showPayment)
        <div class="modal-overlay">
            <div class="modal">
                <div class="modal-head">
                    <h3>💳 Pembayaran</h3>
                    <button type="button" wire:click="closePayment" class="text-gray-400 text-xl font-bold">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="text-sm text-gray-500">Total Tagihan</div>
                        <div class="text-3xl font-extrabold text-indigo-600">Rp {{ number_format($this->totals['grand_total'], 0, ',', '.') }}</div>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach (['cash' => '💵 Tunai', 'qris' => '📱 QRIS', 'bank_transfer' => '🏦 Transfer', 'e_wallet' => '👛 E-Wallet'] as $code => $label)
                            <button type="button" wire:click="$set('splitPayments', [['method' => '{{ $code }}', 'amount' => {{ $this->totals['grand_total'] }}]])" class="pay-method-btn {{ ($splitPayments[0]['method'] ?? '') === $code ? 'active' : '' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="space-y-2 mb-4">
                        @foreach ($splitPayments as $i => $line)
                            <div class="flex gap-2 items-center">
                                <select class="input input-sm" style="width:130px;" wire:model="splitPayments.{{ $i }}.method">
                                    @foreach (['cash' => 'Tunai', 'qris' => 'QRIS', 'bank_transfer' => 'Transfer', 'e_wallet' => 'E-Wallet', 'card' => 'Kartu'] as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                    @foreach ($this->paymentMethods as $pm)
                                        <option value="{{ $pm->code }}">{{ $pm->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" min="0" step="1" class="input input-sm flex-1" placeholder="Nominal" wire:model="splitPayments.{{ $i }}.amount">
                                <button type="button" wire:click="removePaymentLine({{ $i }})" class="qty-btn" style="color:#dc2626;">&times;</button>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addPaymentLine" class="text-xs font-semibold text-indigo-600 hover:underline">+ Tambah metode (split)</button>
                    </div>

                    @if (!empty($splitPayments) && ($splitPayments[0]['method'] ?? '') === 'cash')
                        <div class="mb-4">
                            <label class="text-xs font-semibold text-gray-500 mb-1 block">💵 Uang Diterima</label>
                            <input type="number" min="0" class="input" placeholder="Nominal tunai diterima" wire:model="tenderAmount">
                            @if ($tenderAmount)
                                <div class="text-sm mt-2 {{ $this->change > 0 ? 'text-emerald-600 font-semibold' : 'text-red-500' }}">
                                    Kembalian: Rp {{ number_format($this->change, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <button type="button" wire:click="completeSale" class="btn btn-success btn-lg w-full">
                        ✅ Proses Transaksi
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ HOLD MODAL ============ --}}
    @if ($showHold)
        <div class="modal-overlay">
            <div class="modal">
                <div class="modal-head">
                    <h3>⏸ Hold Transaksi</h3>
                    <button type="button" wire:click="$set('showHold', false)" class="text-gray-400 text-xl font-bold">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-sm text-gray-500 mb-4">Simpan keranjang saat ini untuk dilanjutkan nanti.</p>
                    <input type="text" class="input mb-4" placeholder="Nama / label hold (contoh: Meja 3)" wire:model="holdName">
                    <div class="mb-4">
                        <div class="text-xs font-semibold text-gray-500 mb-2">Hold tersimpan ({{ count($this->holds) }}):</div>
                        @forelse ($this->holds as $hold)
                            <button type="button" wire:click="recallHold({{ $hold['id'] }})" class="w-full flex items-center gap-2 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 mb-1 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-left">
                                <span class="text-sm font-semibold">{{ $hold['name'] }}</span>
                                <span class="text-xs text-gray-400">{{ $hold['item_count'] }} item · {{ $hold['held_at'] }}</span>
                                <span class="ml-auto text-sm font-bold text-indigo-600">Rp {{ number_format($hold['grand_total'], 0, ',', '.') }}</span>
                            </button>
                        @empty
                            <div class="text-xs text-gray-400">Belum ada hold order.</div>
                        @endforelse
                    </div>
                    <button type="button" wire:click="saveHold" class="btn btn-primary btn-lg w-full">💾 Simpan Hold</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ MODIFIER MODAL ============ --}}
    @if ($modifierProductId)
        <div class="modal-overlay">
            <div class="modal">
                <div class="modal-head">
                    <h3>🧩 Pilih Modifier</h3>
                    <button type="button" wire:click="closeModifierModal" class="text-gray-400 text-xl font-bold">&times;</button>
                </div>
                <div class="modal-body">
                    @forelse ($this->modifierGroups as $group)
                        <div class="mb-4">
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">
                                {{ $group->name }}
                                @if ($group->is_required)
                                    <span class="text-red-500 text-xs">*wajib</span>
                                @endif
                                @if ($group->selection_type === 'multiple')
                                    <span class="text-xs text-gray-400">(bisa pilih beberapa)</span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                @foreach ($group->modifiers as $mod)
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                        <input type="checkbox"
                                               value="{{ $mod->id }}"
                                               wire:model="selectedModifiers"
                                               class="h-4 w-4 rounded">
                                        <span class="text-sm flex-1">{{ $mod->name }}</span>
                                        @if ((float) $mod->price > 0)
                                            <span class="text-sm font-semibold text-indigo-600">+Rp {{ number_format($mod->price, 0, ',', '.') }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 mb-4">Tidak ada modifier untuk produk ini.</p>
                    @endforelse

                    <button type="button" wire:click="addWithModifiers" class="btn btn-primary btn-lg w-full">➕ Tambahkan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ RECEIPT MODAL ============ --}}
    @if ($showReceipt && $receipt)
        <div class="modal-overlay">
            <div class="modal">
                <div class="modal-body">
                    <div class="receipt-hero">
                        <div class="receipt-check">✓</div>
                        <div class="text-sm text-gray-500">Transaksi Berhasil</div>
                        <div class="receipt-total">Rp {{ number_format($receipt['grand_total'], 0, ',', '.') }}</div>
                    </div>
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between"><span class="text-gray-500">No. Struk</span><b>{{ $receipt['receipt_number'] }}</b></div>
                        <div class="flex justify-between"><span class="text-gray-500">Item</span><b>{{ $receipt['item_count'] }}</b></div>
                        @if ($receipt['member'])
                            <div class="flex justify-between"><span class="text-gray-500">Member</span><b>{{ $receipt['member'] }}</b></div>
                        @endif
                        @if ($receipt['service_type'])
                            <div class="flex justify-between"><span class="text-gray-500">Tipe Layanan</span><b>{{ $receipt['service_type'] }}</b></div>
                        @endif
                        @if ($receipt['delivery_address'])
                            <div class="flex justify-between"><span class="text-gray-500">Alamat</span><b>{{ $receipt['delivery_address'] }}</b></div>
                        @endif
                        @if (($receipt['delivery_fee'] ?? 0) > 0)
                            <div class="flex justify-between"><span class="text-gray-500">Biaya Antar</span><b>Rp {{ number_format($receipt['delivery_fee'], 0, ',', '.') }}</b></div>
                        @endif
                        <div class="flex justify-between"><span class="text-gray-500">Pembayaran</span><b>{{ $receipt['payment_methods'] }}</b></div>
                        <div class="flex justify-between"><span class="text-gray-500">Dibayar</span><b>Rp {{ number_format($receipt['paid'], 0, ',', '.') }}</b></div>
                        @if ($receipt['change'] > 0)
                            <div class="flex justify-between text-emerald-600"><span>Kembalian</span><b>Rp {{ number_format($receipt['change'], 0, ',', '.') }}</b></div>
                        @endif
                    </div>

                    @if ($this->printers->isNotEmpty())
                        <div class="mb-4">
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">🖨️ Printer</label>
                            <select class="input" wire:model="selectedPrinterId">
                                @foreach ($this->printers as $printer)
                                    <option value="{{ $printer->id }}">
                                        {{ $printer->name }}{{ $printer->is_default ? ' (Utama)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <button type="button" wire:click="openPrintDialog" class="btn btn-ghost flex-1">🖨️ Cetak Struk</button>
                        <button type="button" wire:click="closeReceipt" class="btn btn-primary flex-1">🆕 Transaksi Baru</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ PRINT DIALOG ============ --}}
    @if ($showPrintDialog)
        <div class="modal-overlay">
            <div class="modal" style="max-width: 560px;">
                <div class="modal-head">
                    <h3>🖨️ Cetak Struk</h3>
                    <button type="button" wire:click="closePrintDialog" class="text-gray-400 text-xl font-bold">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Printer</label>
                        <select class="input" wire:model="selectedPrinterId">
                            @foreach ($this->printers as $printer)
                                <option value="{{ $printer->id }}">
                                    {{ $printer->name }}{{ $printer->is_default ? ' (Utama)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if ($this->printers->isEmpty())
                            <p class="text-xs text-red-500 mt-1">Belum ada printer. Tambahkan di menu <b>Printer</b>.</p>
                        @endif
                    </div>

                    <div class="mb-3">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Pratinjau Struk</div>
                        <div class="receipt-preview">
                            {!! $receiptPreviewHtml !!}
                        </div>
                    </div>

                    @if ($printResult)
                        <div class="mb-3 px-3 py-2 rounded-lg text-sm {{ $printResult['success'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">
                            {{ $printResult['message'] ?? '' }}
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <button type="button" wire:click="closePrintDialog" class="btn btn-ghost flex-1">Tutup</button>
                        <button type="button" wire:click="printReceipt" class="btn btn-primary flex-1">📨 Kirim ke Printer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>

@script
<script>
    Alpine.data('posTerminal', () => ({
        init() {
            document.addEventListener('keydown', (e) => {
                if (e.target.matches('input, textarea, select')) {
                    return;
                }
                if (e.key === 'F2') {
                    e.preventDefault();
                    this.$refs.searchInput?.focus();
                }
                if (e.key === 'F9') {
                    e.preventDefault();
                    this.$wire.openHoldModal();
                }
                if (e.key === 'F10') {
                    e.preventDefault();
                    this.$wire.openPayment();
                }
            });
        }
    }));
</script>
@endscript

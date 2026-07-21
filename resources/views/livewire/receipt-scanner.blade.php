<div class="max-w-2xl mx-auto">
    @if ($successMessage)
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold text-emerald-800">{{ $successMessage }}</p>
                    <button wire:click="resetForm" class="text-sm text-emerald-600 hover:text-emerald-800 mt-2 underline">
                        Scan struk baru
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($error)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700">{{ $error }}</p>
            </div>
        </div>
    @endif

    @if ($duplicateDetected)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <p class="text-sm text-amber-700 font-medium">Peringatan: Struk serupa sudah pernah diklaim sebelumnya.</p>
            </div>
        </div>
    @endif

    @if ($budgetInfo)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 mb-4">
            <p class="text-sm text-blue-800">
                Budget: Rp {{ number_format($budgetInfo['budget_total'] ?? 0, 0, ',', '.') }} |
                Terpakai: Rp {{ number_format($budgetInfo['spent'] ?? 0, 0, ',', '.') }} |
                Sisa: Rp {{ number_format($budgetInfo['remaining'] ?? 0, 0, ',', '.') }}
                @if (!($budgetInfo['within_budget'] ?? true))
                    <span class="text-red-600 font-semibold">(Melebihi Budget!)</span>
                @endif
            </p>
        </div>
    @endif

    @if (!$isProcessed)
        <div class="border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center hover:border-indigo-400 transition-colors @if($receiptImage) border-indigo-400 bg-indigo-50/30 @endif"
             x-data="{ dragging: false }"
             x-on:dragover.prevent="dragging = true"
             x-on:dragleave.prevent="dragging = false"
             x-on:drop.prevent="dragging = false; $wire.upload('receiptImage', $event.dataTransfer.files[0])">
            <label for="receipt-upload" class="cursor-pointer block">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-lg font-semibold text-gray-700 mb-1">Upload Struk atau Kwitansi</p>
                <p class="text-sm text-gray-500 mb-2">Drag & drop file di sini, atau klik untuk memilih</p>
                <p class="text-xs text-gray-400">JPG, PNG, WEBP — Maks 10MB</p>
                <input id="receipt-upload" type="file" wire:model.live="receiptImage" accept="image/*" class="hidden" capture="environment"/>
            </label>
            @if ($receiptImage)
                <div class="mt-4">
                    <img src="{{ $receiptImage->temporaryUrl() }}" class="max-h-48 mx-auto rounded-lg shadow" alt="Preview struk"/>
                </div>
            @endif
        </div>
    @endif

    @if ($isProcessing)
        <div class="flex flex-col items-center py-12">
            <div class="relative">
                <div class="w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                <svg class="w-6 h-6 text-indigo-600 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="mt-4 text-gray-600 font-medium">Memproses struk dengan AI...</p>
            <p class="text-sm text-gray-400">Mengekstrak data dari gambar</p>
        </div>
    @endif

    @if ($isProcessed && !$isProcessing)
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="bg-indigo-600 px-6 py-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-white font-semibold">Hasil Scan Struk</span>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Vendor / Toko</label>
                        <input type="text" wire:model.lazy="vendorName" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama toko"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Transaksi</label>
                        <input type="date" wire:model.lazy="transactionDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total (Rp)</label>
                        <input type="number" wire:model.lazy="totalAmount" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pajak (Rp)</label>
                        <input type="number" wire:model.lazy="taxAmount" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Metode Bayar</label>
                        <input type="text" wire:model.lazy="paymentMethod" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="cash/card/transfer"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">No. Struk</label>
                        <input type="text" wire:model.lazy="receiptNumber" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nomor struk"/>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Kategori Reimbursement</label>
                        <select wire:model.lazy="categoryId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Deskripsi</label>
                        <textarea wire:model.lazy="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Deskripsi keperluan"></textarea>
                    </div>
                </div>

                @if (!empty($lineItems))
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Item Terdeteksi</p>
                        <div class="space-y-1">
                            @foreach ($lineItems as $index => $item)
                                <div class="flex justify-between text-sm text-gray-700 py-1 px-3 bg-gray-50 rounded">
                                    <span>{{ $item['description'] ?? 'Item ' . ($index + 1) }}</span>
                                    <span class="font-mono text-gray-500">
                                        @if (isset($item['amount']) && $item['amount'] > 0)
                                            Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button wire:click="createReimbursement"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Buat Reimbursement
                    </button>
                    <button wire:click="resetForm"
                            class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

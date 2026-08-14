<div x-data="{ step: @entangle('step'), selectedTx: @entangle('selectedBtxId') }" class="p-4 sm:p-6 lg:p-8">
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            @for ($i = 1; $i <= 5; $i++)
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="step = {{ $i }}"
                        class="flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold border-2 transition-all duration-200"
                        :class="step >= {{ $i }}
                            ? 'bg-primary-600 border-primary-600 text-white shadow-md shadow-primary-200 dark:shadow-primary-900'
                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500'"
                    >
                        {{ $i }}
                    </button>
                    <span
                        class="text-sm font-medium hidden sm:inline"
                        :class="step >= {{ $i }} ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'"
                    >
                        {{ match($i) {
                            1 => 'Pilih Rekening',
                            2 => 'Upload CSV',
                            3 => 'Review Auto-Match',
                            4 => 'Match Manual',
                            5 => 'Konfirmasi',
                        } }}
                    </span>
                </div>
                @if ($i < 5)
                    <div class="flex-1 h-0.5 max-w-[60px] rounded-full transition-colors duration-300"
                         :class="step > {{ $i }} ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700'">
                    </div>
                @endif
            @endfor
        </div>
    </div>

    @if ($step === 1)
    <div class="max-w-xl">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Pilih Rekening Bank</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Pilih rekening bank yang akan direkonsiliasi dengan statement CSV.</p>

        <div class="space-y-4">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="bankAccountId">
                    <option value="">-- Pilih Rekening Bank --</option>
                    @foreach ($this->bankAccounts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            @if ($bankAccountId)
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    @php $account = \App\Models\BankAccount::find($bankAccountId); @endphp
                    @if ($account)
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Bank:</span>
                                <span class="font-semibold text-gray-900 dark:text-white ml-2">{{ $account->bank_name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">No Rekening:</span>
                                <span class="font-semibold text-gray-900 dark:text-white ml-2">{{ $account->account_number }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Nama:</span>
                                <span class="font-semibold text-gray-900 dark:text-white ml-2">{{ $account->account_name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Saldo Saat Ini:</span>
                                <span class="font-semibold text-gray-900 dark:text-white ml-2">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="pt-4">
                <x-filament::button wire:click="selectAccount" color="primary" size="lg" :disabled="!$bankAccountId">
                    Lanjut ke Upload CSV &rarr;
                </x-filament::button>
            </div>
        </div>
    </div>

    @elseif ($step === 2)
    <div class="max-w-xl">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Upload Statement Bank (CSV)</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Upload file CSV dari bank. Format yang didukung: BCA, Mandiri, dan format generik (Date, Description, Debit, Credit, Balance).
        </p>

        <div class="space-y-6">
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-primary-400 transition-colors">
                <input type="file" wire:model="statementFile" accept=".csv,.txt" class="hidden" id="csvUpload" x-ref="fileInput">
                <label for="csvUpload" class="cursor-pointer block">
                    <div class="text-4xl mb-3">📄</div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Klik untuk memilih file CSV</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Format: BCA, Mandiri, atau Generic (max 10MB)</p>
                </label>
            </div>

            @if ($statementFile)
                <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                    <span class="text-green-600 dark:text-green-400 text-xl">&#10003;</span>
                    <div>
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">{{ $statementFile->getClientOriginalName() }}</p>
                        <p class="text-xs text-green-500">{{ number_format($statementFile->getSize() / 1024, 1) }} KB</p>
                    </div>
                </div>
            @endif

            <div>
                <x-filament::button wire:click="uploadStatement" color="primary" size="lg" :disabled="!$statementFile">
                    Upload & Auto-Match &rarr;
                </x-filament::button>
            </div>

            <div wire:loading wire:target="uploadStatement" class="text-sm text-gray-500 dark:text-gray-400 animate-pulse">
                Memproses file CSV dan menjalankan auto-matching...
            </div>
        </div>
    </div>

    @elseif ($step === 3)
    <div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Review Hasil Auto-Match</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Format Terdeteksi</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ ucfirst($statementData['format'] ?? '-') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Total Statement</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $statementData['total_rows'] ?? 0 }}</p>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 p-4">
                <p class="text-xs uppercase tracking-wider text-green-600 dark:text-green-400 font-medium">Matched</p>
                <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">{{ $matchResult['matched_count'] ?? 0 }}</p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl border border-orange-200 dark:border-orange-800 p-4">
                <p class="text-xs uppercase tracking-wider text-orange-600 dark:text-orange-400 font-medium">Unmatched</p>
                <p class="text-2xl font-bold text-orange-700 dark:text-orange-300 mt-1">{{ $matchResult['unmatched_count'] ?? 0 }}</p>
            </div>
        </div>

        @if (count($matchResult['matched'] ?? []) > 0)
            <div class="mb-8">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">
                    &#9989; Transaksi Matched ({{ count($matchResult['matched']) }})
                </h4>
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Tgl Statement</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Keterangan</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Jumlah</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Bank TX #ID</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Jumlah Bank TX</th>
                                <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($matchResult['matched'] as $m)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">{{ $m['statement']['date'] ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300 max-w-[200px] truncate">{{ Str::limit($m['statement']['description'] ?? '', 50) }}</td>
                                    <td class="px-3 py-2.5 text-right font-mono text-gray-900 dark:text-white">
                                        {{ number_format(abs((float)($m['statement']['amount'] ?? 0)), 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-gray-600 dark:text-gray-400">
                                        #{{ $m['transaction']['id'] ?? '-' }}
                                        <span class="text-xs text-gray-400">
                                            {{ \Carbon\Carbon::parse($m['transaction']['transaction_date'] ?? null)?->format('d/m') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-mono text-gray-700 dark:text-gray-300">
                                        {{ number_format(abs((float)($m['transaction']['amount'] ?? 0)), 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <x-filament::badge :color="$m['score'] >= 80 ? 'success' : ($m['score'] >= 60 ? 'warning' : 'gray')">
                                            {{ $m['score'] }}%
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if (count($matchResult['unmatched'] ?? []) > 0)
            <div class="mb-8">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">
                    &#10060; Transaksi Unmatched ({{ count($matchResult['unmatched']) }})
                </h4>
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Tgl</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Keterangan</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Jumlah</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($matchResult['unmatched'] as $um)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">{{ $um['statement']['date'] ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300 max-w-[200px] truncate">{{ Str::limit($um['statement']['description'] ?? '', 50) }}</td>
                                    <td class="px-3 py-2.5 text-right font-mono text-gray-900 dark:text-white">
                                        {{ number_format(abs((float)($um['statement']['amount'] ?? 0)), 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <x-filament::badge color="warning">
                                            {{ match($um['reason'] ?? '') {
                                                'amount_zero' => 'Amount 0',
                                                'low_score_0' => 'Tidak cocok',
                                                default => Str::limit($um['reason'] ?? 'no_match', 20),
                                            } }}
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-4">
            <x-filament::button wire:click="$set('step', 2)" color="gray">
                &larr; Kembali
            </x-filament::button>
            <x-filament::button wire:click="$set('step', 4)" color="primary">
                Lanjut ke Manual Match &rarr;
            </x-filament::button>
        </div>
    </div>

    @elseif ($step === 4)
    <div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Manual Matching</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Cocokkan transaksi yang tidak terdeteksi otomatis dengan memilih dari daftar transaksi bank yang tersedia.
        </p>

        @if (count($matchResult['unmatched'] ?? []) > 0)
            <div class="space-y-6">
                @foreach ($matchResult['unmatched'] as $index => $um)
                    @if ($um['skipped'] ?? false)
                        <div class="bg-gray-50 dark:bg-gray-800/30 border border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 opacity-60">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs text-gray-400 uppercase tracking-wider">Diskip</span>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mt-0.5">
                                        {{ Str::limit($um['statement']['description'] ?? 'Tanpa keterangan', 60) }}
                                    </p>
                                    <p class="text-xs text-gray-400">Rp {{ number_format(abs((float)($um['statement']['amount'] ?? 0)), 0, ',', '.') }}</p>
                                </div>
                                <x-filament::button wire:click="unskipUnmatched({{ $index }})" color="gray" size="sm">
                                    Batalkan Skip
                                </x-filament::button>
                            </div>
                        </div>
                    @else
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="lg:col-span-1">
                                    <span class="text-xs uppercase tracking-wider text-orange-600 dark:text-orange-400 font-semibold">Unmatched Statement</span>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                        {{ Str::limit($um['statement']['description'] ?? 'Tanpa keterangan', 80) }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $um['statement']['date'] ?? '-' }}</span>
                                        <span class="font-mono font-semibold text-gray-900 dark:text-white">
                                            Rp {{ number_format(abs((float)($um['statement']['amount'] ?? 0)), 0, ',', '.') }}
                                        </span>
                                        @if (!empty($um['statement']['reference']))
                                            <span>Ref: {{ $um['statement']['reference'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="lg:col-span-1">
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider block mb-1.5">
                                        Pilih Transaksi Bank
                                    </label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model.live="selectedBtxId">
                                            <option value="">-- Pilih Transaksi --</option>
                                            @foreach ($this->availableBankTransactions as $id => $label)
                                                <option value="{{ $id }}">{{ $label }}</option>
                                            @endforeach
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </div>

                                <div class="lg:col-span-1 flex items-end gap-2">
                                    <x-filament::button wire:click="manualMatch({{ $index }})" color="primary" size="sm" :disabled="!$selectedBtxId">
                                        Cocokkan
                                    </x-filament::button>
                                    <x-filament::button wire:click="skipUnmatched({{ $index }})" color="gray" size="sm">
                                        Skip
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-5xl mb-4">&#127881;</div>
                <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Semua transaksi sudah matched!</p>
                <p class="text-sm text-gray-400 mt-1">Tidak ada item yang perlu dicocokkan manual.</p>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-6">
            <x-filament::button wire:click="$set('step', 3)" color="gray">
                &larr; Kembali
            </x-filament::button>
            <x-filament::button wire:click="$set('step', 5)" color="primary">
                Lanjut ke Konfirmasi &rarr;
            </x-filament::button>
        </div>
    </div>

    @elseif ($step === 5)
    <div class="max-w-2xl">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Konfirmasi & Buat Rekonsiliasi</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Review ringkasan dan isi detail rekonsiliasi sebelum menyimpan.
        </p>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Periode Mulai</label>
                    <x-filament::input.wrapper class="mt-1">
                        <x-filament::input type="date" wire:model="periodStart" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Periode Akhir</label>
                    <x-filament::input.wrapper class="mt-1">
                        <x-filament::input type="date" wire:model="periodEnd" />
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Saldo Awal</label>
                    <x-filament::input.wrapper class="mt-1">
                        <x-filament::input type="number" wire:model="openingBalance" step="0.01" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Saldo Akhir (Sistem)</label>
                    <x-filament::input.wrapper class="mt-1">
                        <x-filament::input type="number" wire:model="closingBalance" step="0.01" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Saldo Rekening Koran</label>
                    <x-filament::input.wrapper class="mt-1">
                        <x-filament::input type="number" wire:model="statementBalance" step="0.01" />
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Catatan</label>
                <x-filament::input.wrapper class="mt-1">
                    <textarea wire:model="notes" rows="3" class="w-full border-0 bg-transparent text-sm focus:ring-0 resize-none" placeholder="Catatan rekonsiliasi..."></textarea>
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Matched</p>
                <p class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $matchResult['matched_count'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-1">Rp {{ number_format($this->getMatchedTotal(), 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Unmatched</p>
                <p class="text-xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $matchResult['unmatched_count'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-1">Rp {{ number_format($this->getUnmatchedTotal(), 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Saldo Sistem</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($closingBalance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Variance</p>
                <p class="text-xl font-bold mt-1 {{ $this->getVariance() == 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    Rp {{ number_format($this->getVariance(), 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-6">
            <x-filament::button wire:click="$set('step', 4)" color="gray">
                &larr; Kembali
            </x-filament::button>
            <x-filament::button wire:click="createReconciliation" color="success" size="lg">
                Buat Rekonsiliasi
            </x-filament::button>
        </div>

        @if ($createdReconciliationId)
            <div class="mt-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 text-center">
                <div class="text-4xl mb-3">&#9989;</div>
                <h4 class="text-lg font-bold text-green-700 dark:text-green-300">Rekonsiliasi Berhasil Dibuat!</h4>
                <p class="text-sm text-green-600 dark:text-green-400 mt-1">ID Rekonsiliasi: #{{ $createdReconciliationId }}</p>
                <div class="mt-4">
                    <a href="{{ \App\Filament\Resources\BankReconciliation\BankReconciliationResource::getUrl('index') }}"
                       class="text-sm font-medium text-primary-600 hover:underline">
                        Lihat Semua Rekonsiliasi &rarr;
                    </a>
                </div>
            </div>
        @endif
    </div>
    @endif
</div>

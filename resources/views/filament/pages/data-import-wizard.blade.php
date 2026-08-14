<div
    x-data="{
        step: @entangle('step'),
        dragging: false,
        downloadErrors(data) {
            const headers = ['Baris', 'Kolom', 'Pesan'];
            const lines = data.map(e => [e.row, e.field, e.message]);
            const csv = [headers, ...lines].map(r => r.map(c => '\"' + String(c ?? '').replace(/\"/g, '\"\"') + '\"').join(',')).join('\n');
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'laporan-error-import.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    }"
    class="p-4 sm:p-6 lg:p-8"
>
    @php
        $entityFields = $this->entityFields;
        $fieldOptions = [];
        foreach (($entityFields['required'] ?? []) as $f) {
            $fieldOptions[$f['name']] = $f['label'] . ' *';
        }
        foreach (($entityFields['optional'] ?? []) as $f) {
            $fieldOptions[$f['name']] = $f['label'];
        }
        $steps = [
            1 => 'Pilih Entitas',
            2 => 'Upload / Template',
            3 => 'Pemetaan Kolom',
            4 => 'Validasi',
            5 => 'Selesai',
        ];
    @endphp

    {{-- Step indicator --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 flex-wrap">
            @foreach ($steps as $i => $label)
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="step = {{ $i }}"
                        class="flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold border-2 transition-all duration-200"
                        :class="step >= {{ $i }}
                            ? 'bg-primary-600 border-primary-600 text-white shadow-md shadow-primary-200 dark:shadow-primary-900'
                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500'"
                    >
                        @if ($step > $i)
                            <x-heroicon-o-check class="w-4 h-4" />
                        @else
                            {{ $i }}
                        @endif
                    </button>
                    <span class="text-sm font-medium hidden sm:inline"
                        :class="step >= {{ $i }} ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'">
                        {{ $label }}
                    </span>
                </div>
                @if ($i < 5)
                    <div class="flex-1 h-0.5 max-w-[60px] rounded-full transition-colors duration-300"
                         :class="step > {{ $i }} ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700'"></div>
                @endif
            @endforeach
        </div>
        <div class="mt-4 h-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full bg-primary-500 transition-all duration-500 rounded-full" :style="'width: ' + ((step - 1) / 4 * 100) + '%'"></div>
        </div>
    </div>

    {{-- STEP 1: Select entity --}}
    @if ($step === 1)
    <div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Pilih Jenis Data</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Pilih entitas yang ingin diimport dari file CSV.</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($this->importableEntities as $key => $label)
                @php $cfg = $this->entities[$key] ?? []; @endphp
                <button
                    type="button"
                    wire:click="selectEntity('{{ $key }}')"
                    class="group relative flex flex-col items-center justify-center rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 transition-all duration-200 hover:border-primary-400 hover:-translate-y-1 hover:shadow-lg"
                >
                    <span class="text-4xl mb-3">{{ $cfg['icon'] ?? '📄' }}</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $label }}</span>
                    <span class="text-xs text-gray-400 mt-1">Import CSV</span>
                </button>
            @endforeach
        </div>

        @if (count($this->importLogs) > 0)
        <div class="mt-10">
            <h4 class="text-base font-bold text-gray-900 dark:text-white mb-4">Riwayat Import Terakhir</h4>
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Entitas</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">File</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Total</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Berhasil</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Gagal</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($this->importLogs as $log)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                    {{ $this->importableEntities[$log['entity_type']] ?? $log['entity_type'] }}
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ $log['filename'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ $log['total_rows'] }}</td>
                                <td class="px-4 py-2.5 text-right text-green-600 dark:text-green-400 font-semibold">{{ $log['success_count'] }}</td>
                                <td class="px-4 py-2.5 text-right text-red-600 dark:text-red-400 font-semibold">{{ $log['error_count'] }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <x-filament::badge :color="match($log['status']) {
                                        'completed' => 'success',
                                        'processing' => 'warning',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }">
                                        {{ $log['status'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 text-xs">
                                    {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- STEP 2: Template / Upload --}}
    @elseif ($step === 2)
    <div class="max-w-3xl">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Upload File CSV — {{ $this->entityFields['label'] ?? '' }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Download template terlebih dahulu, atau langsung upload file CSV Anda sendiri.
        </p>

        <div class="mb-6 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">Butuh format yang benar?</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Download template CSV dengan header kolom yang sudah sesuai.</p>
            </div>
            <a href="{{ route('import.template', $entity) }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-white dark:bg-gray-800 border border-primary-300 dark:border-primary-700 px-4 py-2 text-sm font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors shrink-0">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                Download Template
            </a>
        </div>

        <div
            class="border-2 border-dashed rounded-xl p-10 text-center transition-colors"
            :class="dragging ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-primary-400'"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="dragging = false"
        >
            <input type="file" wire:model="csvFile" accept=".csv,.txt" class="hidden" id="csvUpload" x-ref="fileInput">
            <label for="csvUpload" class="cursor-pointer block">
                <div class="text-5xl mb-4">📄</div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Seret & letakkan file CSV di sini, atau klik untuk memilih</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Format CSV/TXT · Maks 10MB</p>
            </label>
        </div>

        @if ($csvFile)
            <div class="mt-4 flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                <span class="text-green-600 dark:text-green-400 text-xl">&#10003;</span>
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-700 dark:text-green-300">{{ $csvFile->getClientOriginalName() }}</p>
                    <p class="text-xs text-green-500">{{ number_format($csvFile->getSize() / 1024, 1) }} KB</p>
                </div>
                <button type="button" wire:click="$set('csvFile', null)" class="text-xs text-gray-400 hover:text-red-500">Hapus</button>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-6">
            <x-filament::button wire:click="$set('step', 1)" color="gray">
                &larr; Kembali
            </x-filament::button>
            <x-filament::button wire:click="uploadCsv" color="primary" size="lg" :disabled="!$csvFile">
                Upload & Lanjut &rarr;
            </x-filament::button>
        </div>

        <div wire:loading wire:target="uploadCsv" class="mt-4 text-sm text-gray-500 dark:text-gray-400 animate-pulse">
            Memproses file CSV...
        </div>
    </div>

    {{-- STEP 3: Column mapping --}}
    @elseif ($step === 3)
    <div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Pemetaan Kolom</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Cocokkan setiap kolom CSV dengan field di sistem. Kolom bertanda <span class="text-red-500 font-semibold">*</span> wajib dipetakan.
        </p>

        <div class="mb-6 grid grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Total Baris</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalRows }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Total Kolom</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ count($headers) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Terpetakan Otomatis</p>
                <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ count(array_filter($mapping)) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Kolom CSV</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Contoh Data</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 w-[320px]">Field Tujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($headers as $index => $header)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $header }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-[240px] truncate">
                                {{ $preview[0][$header] ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-filament::input.wrapper>
                                    <x-filament::input.select wire:model="mapping.{{ $index }}">
                                        <option value="">— Abaikan kolom ini —</option>
                                        @foreach ($fieldOptions as $fieldName => $fieldLabel)
                                            <option value="{{ $fieldName }}">{{ $fieldLabel }}</option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-3 pt-6">
            <x-filament::button wire:click="$set('step', 2)" color="gray">
                &larr; Kembali
            </x-filament::button>
            <x-filament::button wire:click="autoSuggestMapping" color="gray">
                Pemetaan Otomatis
            </x-filament::button>
            <x-filament::button wire:click="validateAndPreview" color="primary" size="lg">
                Validasi & Pratinjau &rarr;
            </x-filament::button>
        </div>
    </div>

    {{-- STEP 4: Validation preview --}}
    @elseif ($step === 4)
    <div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Hasil Validasi</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Tinjau hasil validasi. Baris yang bermasalah akan dilewati saat import.
        </p>

        <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Total Baris</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $validation['total_rows'] ?? 0 }}</p>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 p-4">
                <p class="text-xs uppercase tracking-wider text-green-600 dark:text-green-400 font-medium">Valid</p>
                <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">{{ $validation['valid_rows'] ?? 0 }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-4">
                <p class="text-xs uppercase tracking-wider text-red-600 dark:text-red-400 font-medium">Bermasalah</p>
                <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">{{ $validation['error_rows'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">Total Kesalahan</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $validation['error_count'] ?? 0 }}</p>
            </div>
        </div>

        @if (!empty($validation['errors']))
            <div class="mb-6">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">
                    &#9888;&#65039; Detail Kesalahan ({{ $validation['error_count'] }})
                </h4>
                <div class="overflow-x-auto rounded-xl border border-red-200 dark:border-red-800 max-h-[400px] overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-red-50 dark:bg-red-900/20 sticky top-0">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Baris</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Kolom</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Pesan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($validation['errors'] as $err)
                                <tr class="hover:bg-red-50/50 dark:hover:bg-red-900/10">
                                    <td class="px-4 py-2.5 font-mono text-red-700 dark:text-red-300">#{{ $err['row'] }}</td>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 font-medium">{{ $err['field'] }}</td>
                                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ $err['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 text-center">
                <div class="text-4xl mb-3">&#127881;</div>
                <p class="text-lg font-medium text-green-700 dark:text-green-300">Semua baris valid!</p>
                <p class="text-sm text-green-600 dark:text-green-400 mt-1">Siap diimport tanpa kesalahan.</p>
            </div>
        @endif

        {{-- Preview data table --}}
        @if (!empty($preview))
            <div class="mb-6">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Pratinjau Data (5 baris pertama)</h4>
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                @foreach ($headers as $header)
                                    <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($preview as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                    @foreach ($headers as $header)
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300 max-w-[180px] truncate">{{ $row[$header] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-6">
            <x-filament::button wire:click="$set('step', 3)" color="gray">
                &larr; Kembali
            </x-filament::button>
            <x-filament::button wire:click="confirmImport" color="success" size="lg" :disabled="($validation['valid_rows'] ?? 0) === 0">
                Konfirmasi Import ({{ $validation['valid_rows'] ?? 0 }} baris)
            </x-filament::button>
        </div>
    </div>

    {{-- STEP 5: Result --}}
    @elseif ($step === 5)
    <div class="max-w-2xl">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Hasil Import</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Ringkasan proses import data.</p>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
            <div class="text-6xl mb-4">{{ ($importResult['success_count'] ?? 0) > 0 ? '&#9989;' : '&#10060;' }}</div>
            <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                {{ ($importResult['success_count'] ?? 0) > 0 ? 'Import Selesai' : 'Import Gagal' }}
            </h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $importResult['success_count'] ?? 0 }} baris berhasil diimport, {{ $importResult['error_count'] ?? 0 }} baris gagal.
            </p>

            <div class="mt-8 grid grid-cols-2 gap-4">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 p-5">
                    <p class="text-xs uppercase tracking-wider text-green-600 dark:text-green-400 font-medium">Berhasil</p>
                    <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-1">{{ $importResult['success_count'] ?? 0 }}</p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-5">
                    <p class="text-xs uppercase tracking-wider text-red-600 dark:text-red-400 font-medium">Gagal</p>
                    <p class="text-3xl font-bold text-red-700 dark:text-red-300 mt-1">{{ $importResult['error_count'] ?? 0 }}</p>
                </div>
            </div>

            @if (!empty($importResult['errors']))
                <div class="mt-6 text-left">
                    <button type="button" @click="downloadErrors(@js($importResult['errors'] ?? []))"
                        class="inline-flex items-center gap-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                        Download Laporan Error
                    </button>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 pt-6">
            <x-filament::button wire:click="resetWizard" color="gray">
                &larr; Import Lagi
            </x-filament::button>
        </div>
    </div>
    @endif
</div>

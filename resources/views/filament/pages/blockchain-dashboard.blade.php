<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['total_blocks'] ?? 0 }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Blocks</div>
            </div>
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['total_transactions'] ?? 0 }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Transaksi</div>
            </div>
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['latest_block'] ?? 0 }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Latest Block #</div>
            </div>
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-3xl font-bold {{ ($stats['chain_valid'] ?? false) ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ ($stats['chain_valid'] ?? false) ? 'Valid' : 'BROKEN' }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Chain Integrity</div>
            </div>
        </div>

        {{-- Transaction Types Breakdown --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Transaksi per Tipe</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach(($stats['transactions_by_type'] ?? []) as $type => $count)
                    <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $count }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ ucfirst(str_replace('_', ' ', $type)) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Document Verification Tool --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Verifikasi / Notarisasi Dokumen</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Dokumen</label>
                        <input type="file" wire:model="verifyFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-400" />
                        <div wire:loading wire:target="verifyFile" class="text-xs text-indigo-500 mt-1">Uploading...</div>
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="verifyDocument" class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Verifikasi
                        </button>
                        <button wire:click="notarizeDocument" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Notarisasi
                        </button>
                    </div>

                    @if(!empty($verifyResult))
                        <div class="mt-4 p-4 rounded-lg {{ ($verifyResult['is_verified'] ?? false) ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }}">
                            <div class="text-sm font-semibold {{ ($verifyResult['is_verified'] ?? false) ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                {{ ($verifyResult['is_verified'] ?? false) ? 'Dokumen Terverifikasi' : 'Verifikasi Gagal' }}
                            </div>
                            <div class="mt-2 space-y-1 text-xs">
                                @if(isset($verifyResult['original_hash']))
                                    <div class="text-gray-600 dark:text-gray-400">Original Hash: <code class="text-xs break-all">{{ $verifyResult['original_hash'] }}</code></div>
                                @endif
                                @if(isset($verifyResult['current_hash']))
                                    <div class="text-gray-600 dark:text-gray-400">Current Hash: <code class="text-xs break-all">{{ $verifyResult['current_hash'] }}</code></div>
                                @endif
                                @if(isset($verifyResult['notarized_at']))
                                    <div class="text-gray-600 dark:text-gray-400">Notarized: {{ $verifyResult['notarized_at'] }}</div>
                                @endif
                                @if(isset($verifyResult['tampered']) && $verifyResult['tampered'])
                                    <div class="text-red-600 dark:text-red-400 font-semibold">Dokumen telah diubah setelah notarisasi!</div>
                                @endif
                                @if(isset($verifyResult['message']))
                                    <div class="text-gray-600 dark:text-gray-400">{{ $verifyResult['message'] }}</div>
                                @endif
                                @if(isset($verifyResult['block_number']))
                                    <div class="text-gray-600 dark:text-gray-400">Block: #{{ $verifyResult['block_number'] }}</div>
                                @endif
                                @if(isset($verifyResult['transaction_hash']))
                                    <div class="text-gray-600 dark:text-gray-400">TX: <code class="text-xs break-all">{{ $verifyResult['transaction_hash'] }}</code></div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Certificate Verification Tool --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Verifikasi Sertifikat</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">UUID Sertifikat</label>
                        <input type="text" wire:model="verifyCertUuid" placeholder="Masukkan UUID sertifikat..." class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm px-3 py-2" />
                    </div>
                    <button wire:click="verifyCertificate" class="px-4 py-2 text-sm font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                        Verifikasi Sertifikat
                    </button>

                    @if(!empty($certVerifyResult))
                        <div class="mt-4 p-4 rounded-lg {{ ($certVerifyResult['is_valid'] ?? false) ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }}">
                            <div class="text-sm font-semibold {{ ($certVerifyResult['is_valid'] ?? false) ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                {{ ($certVerifyResult['is_valid'] ?? false) ? 'Sertifikat Valid' : 'Sertifikat Tidak Valid' }}
                            </div>
                            <div class="mt-2 space-y-1 text-xs">
                                @if(isset($certVerifyResult['issued_to']))
                                    <div class="text-gray-600 dark:text-gray-400">Diterbitkan untuk: <strong>{{ $certVerifyResult['issued_to'] }}</strong></div>
                                @endif
                                @if(isset($certVerifyResult['course']))
                                    <div class="text-gray-600 dark:text-gray-400">Kursus: {{ $certVerifyResult['course'] }}</div>
                                @endif
                                @if(isset($certVerifyResult['issued_date']))
                                    <div class="text-gray-600 dark:text-gray-400">Tanggal: {{ $certVerifyResult['issued_date'] }}</div>
                                @endif
                                @if(isset($certVerifyResult['certificate_number']))
                                    <div class="text-gray-600 dark:text-gray-400">No. Sertifikat: {{ $certVerifyResult['certificate_number'] }}</div>
                                @endif
                                @if(isset($certVerifyResult['block_number']))
                                    <div class="text-gray-600 dark:text-gray-400">Block: #{{ $certVerifyResult['block_number'] }}</div>
                                @endif
                                @if(isset($certVerifyResult['blockchain_tx']))
                                    <div class="text-gray-600 dark:text-gray-400">TX: <code class="text-xs break-all">{{ $certVerifyResult['blockchain_tx'] }}</code></div>
                                @endif
                                @if(isset($certVerifyResult['message']))
                                    <div class="text-gray-600 dark:text-gray-400">{{ $certVerifyResult['message'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Chain Health --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Integritas Blockchain</h3>
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full {{ ($stats['chain_valid'] ?? false) ? 'bg-emerald-500' : 'bg-red-500' }} animate-pulse"></div>
                <span class="text-sm {{ ($stats['chain_valid'] ?? false) ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                    {{ ($stats['chain_valid'] ?? false) ? 'Chain valid — semua block terverifikasi' : 'Chain BROKEN — integritas terganggu!' }}
                </span>
            </div>
            <div class="mt-4 text-xs text-gray-400 dark:text-gray-500">
                <p>Blockchain lokal menggunakan SHA-256 untuk hashing. Setiap block terhubung ke block sebelumnya melalui hash pointer. Verifikasi chain dilakukan otomatis setiap ada block baru.</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="space-y-6">
        @if (empty($backups))
            <x-filament::section>
                <div class="text-center py-8 text-gray-500">
                    <p>Belum ada file backup tersedia.</p>
                    <p class="text-sm mt-2">Klik "Buat Backup Sekarang" untuk membuat backup pertama.</p>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="p-3 font-semibold">Nama File</th>
                                <th class="p-3 font-semibold">Ukuran</th>
                                <th class="p-3 font-semibold">Tanggal</th>
                                <th class="p-3 font-semibold">Tipe</th>
                                <th class="p-3 font-semibold">Status</th>
                                <th class="p-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($backups as $backup)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="p-3 font-mono text-xs">{{ $backup['filename'] }}</td>
                                    <td class="p-3">{{ $backup['size_formatted'] }}</td>
                                    <td class="p-3">{{ date('d M Y H:i', $backup['date']) }}</td>
                                    <td class="p-3">
                                        <span @class([
                                            'px-2 py-1 rounded text-xs font-medium',
                                            'bg-blue-100 text-blue-700' => $backup['type'] === 'manual',
                                            'bg-green-100 text-green-700' => $backup['type'] === 'auto',
                                        ])>
                                            {{ $backup['type'] === 'manual' ? 'Manual' : 'Otomatis' }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <span @class([
                                            'px-2 py-1 rounded text-xs font-medium',
                                            'bg-green-100 text-green-700' => $backup['status'] === 'success',
                                            'bg-red-100 text-red-700' => $backup['status'] === 'failed',
                                        ])>
                                            {{ $backup['status'] === 'success' ? 'Sukses' : 'Gagal' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <div class="flex gap-2 justify-end">
                                            <button wire:click="downloadBackup('{{ $backup['filename'] }}')"
                                                class="text-primary-600 hover:text-primary-800 text-xs font-medium">
                                                Download
                                            </button>
                                            <button wire:click="uploadToCloud('{{ $backup['filename'] }}')"
                                                class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                Upload Cloud
                                            </button>
                                            <button
                                                wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                                wire:confirm="Hapus backup {{ $backup['filename'] }}?"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>

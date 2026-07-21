<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Klasifikasi Dokumen AI</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Auto-classify & extract data dari dokumen dengan AI vision
                </p>
            </div>
        </div>

        <div class="flex gap-2 border-b pb-2">
            <x-filament::button wire:click="$set('activeTab', 'classify')" color="{{ $activeTab === 'classify' ? 'primary' : 'gray' }}" size="sm" outlined>
                Klasifikasi Tunggal
            </x-filament::button>
            <x-filament::button wire:click="$set('activeTab', 'batch')" color="{{ $activeTab === 'batch' ? 'primary' : 'gray' }}" size="sm" outlined>
                Batch Klasifikasi
            </x-filament::button>
        </div>

        @if($activeTab === 'classify')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-6">
                    <h3 class="font-semibold mb-4">Upload Dokumen</h3>
                    {{ $this->form }}
                    <div class="mt-4">
                        <x-filament::button wire:click="classifyUploaded" color="primary" :loading="$isProcessing">
                            Klasifikasikan
                        </x-filament::button>
                    </div>
                </div>

                <div class="space-y-4">
                    @if(!empty($classificationResult))
                        @if(isset($classificationResult['error']))
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 text-red-700 dark:text-red-300">
                                {{ $classificationResult['error'] }}
                            </div>
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-xl border p-6">
                                <h3 class="font-semibold mb-3">Hasil Klasifikasi</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">Tipe Dokumen</span>
                                        <span class="font-bold text-lg">{{ $classificationResult['document_label'] ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">Confidence</span>
                                        <span class="font-bold" style="color: {{ $this->getConfidenceColor($classificationResult['confidence'] ?? 0) }}">
                                            {{ $classificationResult['confidence'] ?? 0 }}%
                                            ({{ $this->getConfidenceLabel($classificationResult['confidence'] ?? 0) }})
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">Folder Saran</span>
                                        <span class="font-medium">{{ $classificationResult['suggested_folder'] ?? '-' }}</span>
                                    </div>
                                    @if(!empty($classificationResult['reasoning']))
                                    <div class="text-xs text-gray-400 italic">{{ $classificationResult['reasoning'] }}</div>
                                    @endif
                                </div>
                            </div>

                            @if(!empty($extractedData) && !isset($extractedData['error']))
                            <div class="bg-white dark:bg-gray-800 rounded-xl border p-6">
                                <h3 class="font-semibold mb-3">Data Terekstrak</h3>
                                <div class="space-y-2">
                                    @foreach($extractedData as $key => $value)
                                    <div class="flex justify-between text-sm border-b pb-1 last:border-0">
                                        <span class="text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                        <span class="font-medium">{{ $value ?? '-' }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        @else
            <div class="space-y-4">
                <div class="flex gap-3">
                    <x-filament::button wire:click="runBatchClassify" color="primary" :loading="$isProcessing">
                        Scan & Klasifikasikan Semua
                    </x-filament::button>
                    <x-filament::button wire:click="autoFileAll" color="gray" :loading="$isProcessing" outlined>
                        Auto-File Semua
                    </x-filament::button>
                </div>

                @if(!empty($batchResult))
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                        <p class="text-xs text-gray-500">Total Diproses</p>
                        <p class="text-2xl font-bold">{{ $batchResult['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                        <p class="text-xs text-green-500">Berhasil</p>
                        <p class="text-2xl font-bold text-green-600">{{ $batchResult['classified'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                        <p class="text-xs text-red-500">Gagal</p>
                        <p class="text-2xl font-bold text-red-600">{{ ($batchResult['total'] ?? 0) - ($batchResult['classified'] ?? 0) }}</p>
                    </div>
                </div>

                @php $bResults = $batchResult['results'] ?? []; @endphp
                @if(!empty($bResults))
                <div class="bg-white dark:bg-gray-800 rounded-xl border overflow-hidden">
                    <div class="p-4 border-b"><h3 class="font-semibold">Detail Batch</h3></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-500 dark:text-gray-400">
                                    <th class="p-3">File</th>
                                    <th class="p-3">Tipe</th>
                                    <th class="p-3 text-center">Confidence</th>
                                    <th class="p-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($bResults, 0, 30) as $r)
                                <tr class="border-t">
                                    <td class="p-3 text-xs max-w-[200px] truncate">{{ $r['file_name'] ?? '-' }}</td>
                                    <td class="p-3">{{ $r['document_label'] ?? $r['document_type'] ?? '-' }}</td>
                                    <td class="p-3 text-center">{{ $r['confidence'] ?? '-' }}%</td>
                                    <td class="p-3">
                                        @if(($r['status'] ?? '') === 'success')
                                        <span class="text-green-600 text-xs font-bold">OK</span>
                                        @else
                                        <span class="text-red-600 text-xs">{{ $r['message'] ?? 'Error' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>

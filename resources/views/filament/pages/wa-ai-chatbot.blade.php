<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Toggle master + status bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Chatbot AI WhatsApp
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Balasan otomatis bertenaga AI dengan konteks data ERP (pesanan, invoice, tiket).
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if ($connectionOk !== null)
                    <span @class([
                        'inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full',
                        'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $connectionOk,
                        'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400' => !$connectionOk,
                    ])>
                        <span @class(['w-2 h-2 rounded-full', 'bg-success-500' => $connectionOk, 'bg-danger-500' => !$connectionOk])></span>
                        {{ $connectionResult }}
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
            {{-- LEFT: Configuration --}}
            <div class="xl:col-span-3 space-y-6">
                <x-filament::section>
                    <x-slot name="heading">Pengaturan Dasar</x-slot>
                    <x-slot name="description">Pilih provider AI dan aktifkan mode AI vs template.</x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label @class([
                            'flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition',
                            'border-primary-400 bg-primary-50 dark:bg-primary-500/10' => $isAiEnabled,
                            'border-gray-200 dark:border-gray-700 hover:border-primary-400' => !$isAiEnabled,
                        ])>
                            <input type="radio" wire:model.live="isAiEnabled" value="1" class="mt-1 accent-primary-600">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Mode AI</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Balasan dihasilkan AI dengan konteks ERP</div>
                            </div>
                        </label>
                        <label @class([
                            'flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition',
                            'border-primary-400 bg-primary-50 dark:bg-primary-500/10' => !$isAiEnabled,
                            'border-gray-200 dark:border-gray-700 hover:border-primary-400' => $isAiEnabled,
                        ])>
                            <input type="radio" wire:model.live="isAiEnabled" value="0" class="mt-1 accent-primary-600">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Mode Template</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Balasan statis berbasis kata kunci (atur di Auto Reply WA)</div>
                            </div>
                        </label>
                    </div>

                    @if ($isAiEnabled)
                        <div class="mt-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">AI Provider</label>
                                    <select wire:model="aiProviderId"
                                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                                        <option value="">— Pilih Provider —</option>
                                        @foreach ($providers as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Aktifkan auto-reply</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Template Prompt AI</label>
                                <textarea wire:model="promptTemplate" rows="6"
                                    placeholder="Anda adalah asisten virtual untuk {company}..."
                                    class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm font-mono"></textarea>
                            </div>

                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Variabel tersedia:</div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($variableHints as $var => $hint)
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-md bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300 font-mono cursor-pointer"
                                            title="{{ $hint }}"
                                            wire:click="insertVariable('{{ $var }}')">
                                            {{ $var }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Pesan Fallback (jika AI gagal / eskalasi)</label>
                                <textarea wire:model="fallbackMessage" rows="2"
                                    placeholder="Maaf, pertanyaan Anda memerlukan bantuan tim kami..."
                                    class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"></textarea>
                            </div>
                        </div>
                    @endif
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Konfigurasi Intent</x-slot>
                    <x-slot name="description">Aktifkan/nonaktifkan intent dan custom prompt per intent.</x-slot>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 pr-3">Intent</th>
                                    <th class="py-2 pr-3 w-24">Aktif</th>
                                    <th class="py-2">Prompt Kustom</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($intentConfig as $intent => $intentSettings)
                                    <tr class="align-top">
                                        <td class="py-3 pr-3">
                                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $this->getIntentLabels()[$intent] ?? $intent }}</span>
                                            <div class="text-xs text-gray-400 font-mono">{{ $intent }}</div>
                                        </td>
                                        <td class="py-3 pr-3">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model="intentConfig.{{ $intent }}.enabled"
                                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                            </label>
                                        </td>
                                        <td class="py-3">
                                            <input type="text" wire:model="intentConfig.{{ $intent }}.prompt"
                                                placeholder="(opsional) Instruksi khusus..."
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Aturan Eskalasi ke Manusia</x-slot>
                    <x-slot name="description">Kapan chatbot harus menyerahkan ke agent manusia.</x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ambang Keyakinan Minimum
                                <span class="text-xs text-gray-400">(di bawah ini → eskalasi)</span>
                            </label>
                            <input type="number" step="0.05" min="0" max="1" wire:model="escalationRules.confidence_threshold"
                                class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Maks. Fallback Beruntun</label>
                            <input type="number" min="1" wire:model="escalationRules.max_consecutive_fallback"
                                class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Intent yang selalu di-eskalasi</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($this->getEscalatableIntentLabels() as $value => $label)
                                <label class="inline-flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-primary-400">
                                    <input type="checkbox" wire:model="escalationRules.escalate_intents"
                                        value="{{ $value }}" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Kata kunci eskalasi (pisahkan dengan koma)</label>
                        <textarea wire:model="escalationKeywordsInput" rows="2"
                            placeholder="contoh: marah, hubungi manager, penipuan, komplain berat"
                            class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"></textarea>
                    </div>
                </x-filament::section>
            </div>

            {{-- RIGHT: Test chat + logs --}}
            <div class="xl:col-span-2 space-y-6">
                <x-filament::section class="flex flex-col h-full">
                    <x-slot name="heading">Panel Uji Chat</x-slot>
                    <x-slot name="description">Coba kirim pesan dan lihat respons AI.</x-slot>

                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Nomor HP simulasi</label>
                            <input type="text" wire:model="testPhone"
                                class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                        </div>

                        <div class="flex-1 min-h-[320px] max-h-[480px] overflow-y-auto rounded-xl bg-gray-50 dark:bg-gray-800/50 p-4 space-y-3 border border-gray-200 dark:border-gray-700">
                            @if (empty($chatHistory))
                                <div class="flex flex-col items-center justify-center h-full text-center py-16">
                                    <span class="text-3xl mb-2">🤖</span>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada percakapan.</p>
                                    <p class="text-xs text-gray-400">Ketik pesan di bawah untuk mulai menguji.</p>
                                </div>
                            @else
                                @foreach ($chatHistory as $msg)
                                    @if (($msg['role'] ?? '') === 'user')
                                        <div class="flex justify-end">
                                            <div class="max-w-[80%] rounded-2xl rounded-br-sm bg-primary-600 text-white px-4 py-2.5 text-sm shadow-sm">
                                                <div class="whitespace-pre-wrap break-words">{{ $msg['content'] }}</div>
                                                <div class="text-[10px] text-primary-200 text-right mt-1">{{ $msg['at'] ?? '' }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex justify-start">
                                            <div class="max-w-[80%] rounded-2xl rounded-bl-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm">
                                                <div class="whitespace-pre-wrap break-words">{{ $msg['content'] }}</div>
                                                @if (!empty($msg['meta']))
                                                    <div class="text-[10px] text-gray-400 dark:text-gray-500 text-right mt-1">{{ $msg['meta'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            @if ($isTesting)
                                <div class="flex justify-start">
                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 px-4 py-2.5">
                                        <span class="animate-spin inline-block w-4 h-4 border-2 border-gray-300 border-t-primary-500 rounded-full"></span>
                                        AI sedang mengetik...
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <input type="text" wire:model="testMessage" wire:keydown.enter="sendTestMessage"
                                placeholder="Ketik pesan dan tekan Enter..."
                                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                            <button type="button" wire:click="sendTestMessage"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium transition disabled:opacity-50"
                                wire:loading.attr="disabled">
                                Kirim
                            </button>
                            <button type="button" wire:click="clearChat"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Bersihkan
                            </button>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Log Percakapan AI</x-slot>
                    <x-slot name="description">{{ count($recentConversations) }} percakapan terakhir dengan intent terdeteksi.</x-slot>

                    @if (empty($recentConversations))
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada percakapan AI.</p>
                    @else
                        <div class="space-y-2 max-h-[360px] overflow-y-auto">
                            @foreach ($recentConversations as $conv)
                                <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-sm text-gray-800 dark:text-gray-200 truncate">{{ $conv['name'] }}</span>
                                            <span class="text-xs text-gray-400 font-mono">{{ $conv['phone'] }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $conv['last_message'] }}</div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="inline-flex text-xs px-2 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                            {{ $conv['intent'] }}
                                        </span>
                                        <div class="text-[10px] text-gray-400 mt-1">{{ $conv['at'] }} · {{ round($conv['confidence'] * 100) }}%</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>

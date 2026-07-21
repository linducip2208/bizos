<div class="flex h-full bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    {{-- Left Sidebar: Accounts & Folders --}}
    <div class="w-60 border-r border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col flex-shrink-0">
        {{-- Account Selector --}}
        <div class="p-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Akun Email</span>
                <button wire:click="showAddAccount" class="text-indigo-500 hover:text-indigo-600 text-lg leading-none" title="Tambah Akun">
                    <x-heroicon-o-plus-circle class="w-4 h-4" />
                </button>
            </div>
            @foreach($accounts as $acc)
                <button
                    wire:click="$set('selectedAccountId', {{ $acc->id }})"
                    class="w-full text-left px-3 py-2 rounded-lg text-xs font-medium mb-1 transition-colors
                        {{ $selectedAccountId === $acc->id ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}"
                >
                    {{ $acc->name ?: $acc->email }}
                </button>
            @endforeach
        </div>

        {{-- Folders --}}
        <div class="flex-1 overflow-y-auto scrollbar-thin p-2">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 px-2 py-1">Folder</div>
            @foreach($folders as $folder)
                <button
                    wire:click="$set('currentFolder', '{{ $folder }}')"
                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium mb-0.5 transition-colors flex items-center gap-2
                        {{ $currentFolder === $folder ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                >
                    @if(str_contains(strtoupper($folder), 'INBOX'))
                        <x-heroicon-o-inbox-arrow-down class="w-4 h-4 flex-shrink-0" />
                    @elseif(str_contains(strtoupper($folder), 'SENT'))
                        <x-heroicon-o-paper-airplane class="w-4 h-4 flex-shrink-0" />
                    @elseif(str_contains(strtoupper($folder), 'DRAFT'))
                        <x-heroicon-o-pencil class="w-4 h-4 flex-shrink-0" />
                    @elseif(str_contains(strtoupper($folder), 'TRASH') || str_contains(strtoupper($folder), 'SPAM'))
                        <x-heroicon-o-trash class="w-4 h-4 flex-shrink-0" />
                    @else
                        <x-heroicon-o-folder class="w-4 h-4 flex-shrink-0" />
                    @endif
                    <span class="truncate">{{ $folder }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Center: Message List --}}
    <div class="w-[380px] border-r border-gray-200 dark:border-gray-700 flex flex-col flex-shrink-0">
        {{-- Search & Compose Header --}}
        <div class="p-3 border-b border-gray-200 dark:border-gray-700 space-y-2">
            <button wire:click="openCompose" class="w-full flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
                <x-heroicon-o-pencil-square class="w-4 h-4" />
                Tulis Email
            </button>
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                    wire:model.live.debounce.300ms="searchQuery"
                    type="text"
                    placeholder="Cari email..."
                    class="w-full pl-9 pr-3 py-2 bg-gray-100 dark:bg-gray-700 border-0 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                />
            </div>
        </div>

        {{-- Message List --}}
        <div class="flex-1 overflow-y-auto scrollbar-thin">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-700/50">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                    {{ $currentFolder }} ({{ $totalMessages }})
                </span>
                <button wire:click="loadMessages" class="text-gray-400 hover:text-indigo-500">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                </button>
            </div>

            @if($isLoading)
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <x-heroicon-o-arrow-path class="w-6 h-6 animate-spin mb-2" />
                    <span class="text-xs">Memuat...</span>
                </div>
            @elseif(empty($messages))
                <div class="flex flex-col items-center justify-center py-16 text-center px-4">
                    <x-heroicon-o-inbox class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada pesan</p>
                </div>
            @else
                @foreach($messages as $msg)
                    <div
                        wire:click="selectMessage('{{ $msg['uid'] }}')"
                        class="px-4 py-3 border-b border-gray-100 dark:border-gray-700/50 cursor-pointer transition-colors
                            {{ $selectedMessageUid === ($msg['uid'] ?? '') ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-2 border-l-indigo-500' : 'hover:bg-gray-50 dark:hover:bg-gray-800' }}
                            {{ ($msg['is_read'] ?? true) ? '' : 'font-semibold' }}"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[13px] {{ ($msg['is_read'] ?? true) ? 'text-gray-600 dark:text-gray-400' : 'text-gray-900 dark:text-gray-100' }} truncate max-w-[200px]">
                                {{ $msg['from_name'] ?: $msg['from_email'] ?? 'Unknown' }}
                            </span>
                            <span class="text-[10px] text-gray-400 flex-shrink-0">
                                @php
                                    $ts = $msg['date_ts'] ?? strtotime($msg['date'] ?? 'now');
                                    $diff = time() - $ts;
                                @endphp
                                @if($diff < 86400)
                                    {{ date('H:i', $ts) }}
                                @elseif($diff < 604800)
                                    {{ \Carbon\Carbon::parse($msg['date'] ?? 'now')->translatedFormat('D') }}
                                @else
                                    {{ date('d/m', $ts) }}
                                @endif
                            </span>
                        </div>
                        <div class="text-[12px] {{ ($msg['is_read'] ?? true) ? 'text-gray-500 dark:text-gray-500' : 'text-gray-800 dark:text-gray-200' }} truncate">
                            {{ $msg['subject'] ?? '(Tanpa Subjek)' }}
                        </div>
                        @if(!($msg['is_read'] ?? true))
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mt-1.5"></div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Pagination --}}
        @if($lastPage > 1)
            <div class="flex items-center justify-center gap-1 p-2 border-t border-gray-200 dark:border-gray-700 text-xs">
                <button wire:click="setPage({{ $currentPage - 1 }})" @if($currentPage <= 1) disabled @endif class="px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-30">Sebelumnya</button>
                <span class="text-gray-500 px-2">{{ $currentPage }} / {{ $lastPage }}</span>
                <button wire:click="setPage({{ $currentPage + 1 }})" @if($currentPage >= $lastPage) disabled @endif class="px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-30">Berikutnya</button>
            </div>
        @endif
    </div>

    {{-- Right: Email Detail --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        @if($selectedMessage)
            <div class="flex-1 overflow-y-auto">
                {{-- Detail Header --}}
                <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-gray-900 dark:text-gray-100 truncate max-w-md">
                            {{ $selectedMessage['subject'] ?? '(Tanpa Subjek)' }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-1">
                        <button wire:click="openCompose('reply')" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500" title="Balas">
                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                        </button>
                        <button wire:click="convertToLead" class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500" title="Konversi ke Lead">
                            <x-heroicon-o-user-plus class="w-4 h-4" />
                        </button>
                        <button wire:click="convertToTicket" class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 text-amber-500" title="Konversi ke Tiket">
                            <x-heroicon-o-ticket class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {{-- From/To Info --}}
                <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700/50 space-y-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $selectedMessage['from_name'] ?: $selectedMessage['from_email'] }}</div>
                            <div class="text-xs text-gray-500">{{ $selectedMessage['from_email'] }}</div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $selectedMessage['date'] ?? '' }}</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        <span class="text-gray-400">ke:</span> {{ $selectedMessage['to'] ?? '' }}
                        @if(!empty($selectedMessage['cc']))
                            <span class="text-gray-400 ml-2">cc:</span> {{ $selectedMessage['cc'] }}
                        @endif
                    </div>
                </div>

                {{-- Link Suggestions --}}
                @if(!empty($linkSuggestions))
                    <div class="px-5 py-2.5 bg-amber-50 dark:bg-amber-900/10 border-b border-amber-200 dark:border-amber-800">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">Tautkan ke record?</span>
                            <button wire:click="$set('linkSuggestions', [])" class="text-amber-400 hover:text-amber-600 text-xs">
                                <x-heroicon-o-x-mark class="w-3 h-3" />
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                            @foreach($linkSuggestions as $suggestion)
                                <button wire:click="linkEmail('{{ addslashes('App\\Models\\' . $suggestion['model']) }}', {{ $suggestion['id'] }})" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white dark:bg-gray-700 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                                    {{ $suggestion['model'] }}: {{ $suggestion['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Body --}}
                <div class="p-5 email-body">
                    @if(!empty($selectedMessage['body_html']))
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            {!! $selectedMessage['body_html'] !!}
                        </div>
                    @elseif(!empty($selectedMessage['body_text']))
                        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                            {{ $selectedMessage['body_text'] }}
                        </div>
                    @else
                        <p class="text-sm text-gray-400 italic">(Tidak ada konten)</p>
                    @endif
                </div>

                {{-- Attachments --}}
                @if(!empty($selectedMessage['attachments']))
                    <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-xs font-semibold text-gray-500 mb-2">Lampiran ({{ count($selectedMessage['attachments']) }})</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($selectedMessage['attachments'] as $att)
                                <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-xs">
                                    <x-heroicon-o-paper-clip class="w-3.5 h-3.5 text-gray-400" />
                                    <span class="text-gray-600 dark:text-gray-400 truncate max-w-[150px]">{{ $att['filename'] ?? 'attachment' }}</span>
                                    <span class="text-gray-400">{{ number_format(($att['size'] ?? 0) / 1024, 1) }} KB</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-center px-6">
                <x-heroicon-o-envelope class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">Pilih email untuk membaca</h3>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Klik email di daftar sebelah kiri</p>
            </div>
        @endif
    </div>

    {{-- Compose Modal --}}
    @if($showCompose)
        <div class="fixed inset-0 z-50 flex items-start justify-center pt-10" x-data @keydown.escape.wire="closeCompose">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeCompose"></div>
            <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden mx-4" @click.stop>
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Tulis Email</h3>
                    <button wire:click="closeCompose" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-5 space-y-3 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1 block">Ke</label>
                        <input wire:model="composeData.to" type="email" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" placeholder="email@contoh.com" required>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="text-xs font-medium text-gray-500 mb-1 block">CC</label>
                            <input wire:model="composeData.cc" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                        </div>
                        <div class="flex-1">
                            <label class="text-xs font-medium text-gray-500 mb-1 block">BCC</label>
                            <input wire:model="composeData.bcc" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1 block">Subjek</label>
                        <input wire:model="composeData.subject" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" required>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1 block">Pesan</label>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden">
                            <textarea
                                wire:model="composeData.body_html"
                                rows="10"
                                class="w-full px-3 py-3 bg-gray-50 dark:bg-gray-700 border-0 text-sm focus:ring-0 resize-none"
                                placeholder="Tulis email Anda di sini..."
                            ></textarea>
                            <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-600 text-xs text-gray-400">
                                <x-heroicon-o-information-circle class="w-3.5 h-3.5" />
                                <span>Anda bisa menulis dalam HTML. Gunakan &lt;br&gt; untuk baris baru.</span>
                            </div>
                        </div>
                    </div>

                    @if(!empty($attachmentUploads))
                        <div class="flex flex-wrap gap-2">
                            @foreach($attachmentUploads as $i => $f)
                                <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs">
                                    <x-heroicon-o-paper-clip class="w-3 h-3 text-gray-400" />
                                    <span class="text-gray-600 dark:text-gray-400">{{ $f->getClientOriginalName() }}</span>
                                    <button wire:click="removeAttachment({{ $i }})" class="text-red-400 hover:text-red-600">
                                        <x-heroicon-o-x-mark class="w-3 h-3" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2">
                        <input type="file" wire:model="attachmentUploads" multiple class="hidden" id="compose-attachments" />
                        <label for="compose-attachments" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                            <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                            Lampirkan
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="closeCompose" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">Batal</button>
                        <button wire:click="sendEmail" class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">
                            <span wire:loading.remove wire:target="sendEmail">Kirim</span>
                            <span wire:loading wire:target="sendEmail">Mengirim...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Account Modal --}}
    @if(!is_null($editingAccountId) || $errors->has('accountForm.*'))
        {{-- This would be a simpler check; for now the add account form is triggered by showAddAccount --}}
    @endif
</div>

@push('styles')
<style>
    .scrollbar-thin::-webkit-scrollbar { width: 5px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 5px; }
    .email-body img { max-width: 100%; height: auto; }
    .prose :where(a):not(:where([class~="not-prose"] *)) { color: #4f46e5; }
</style>
@endpush

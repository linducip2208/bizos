<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-heroicon-o-envelope class="w-4 h-4 text-indigo-500" />
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                Email Timeline
                @if($contextName)
                    <span class="font-normal text-gray-400">— {{ $contextName }}</span>
                @endif
            </h3>
        </div>
        <button wire:click="load" class="text-gray-400 hover:text-indigo-500 transition-colors" title="Refresh">
            <x-heroicon-o-arrow-path wire:loading.class="animate-spin" class="w-4 h-4" />
        </button>
    </div>

    @if($isLoading)
        <div class="flex items-center justify-center py-8 text-gray-400">
            <svg class="animate-spin w-5 h-5 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span class="text-sm">Memuat timeline...</span>
        </div>
    @elseif(empty($emails))
        <div class="flex flex-col items-center justify-center py-10 text-center px-4">
            <x-heroicon-o-inbox class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada email</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Email dari/ke alamat ini akan muncul di sini</p>
        </div>
    @else
        <div class="overflow-y-auto max-h-[500px] scrollbar-thin">
            @foreach($emails as $email)
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700/50 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="flex items-start gap-3">
                        {{-- Direction Icon --}}
                        <div class="flex-shrink-0 mt-0.5">
                            @if($email['is_sent'])
                                <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                    <x-heroicon-o-arrow-up-right class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                            @else
                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                    <x-heroicon-o-arrow-down-left class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $email['is_sent'] ? 'Dikirim ke' : 'Dari' }}: {{ $email['is_sent'] ? $email['to_email'] : ($email['from_name'] ?: $email['from_email']) }}
                                    </span>
                                    @if(!empty($email['has_attachments']))
                                        <x-heroicon-o-paper-clip class="w-3 h-3 text-gray-400" />
                                    @endif
                                    @if(!$email['is_read'])
                                        <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 flex-shrink-0 ml-2">{{ $email['email_date'] ?? '' }}</span>
                            </div>
                            <div class="text-[13px] font-medium text-gray-700 dark:text-gray-300 truncate mb-0.5">
                                {{ $email['subject'] ?? '(Tanpa Subjek)' }}
                            </div>
                            @if(!empty($email['body_preview']))
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $email['body_preview'] }}</p>
                            @endif

                            {{-- Linked Records --}}
                            @if(!empty($email['linked_records']))
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($email['linked_records'] as $linked)
                                        <a href="{{ $linked['url'] }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">
                                            {{ $linked['model'] }}: {{ $linked['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
    .scrollbar-thin::-webkit-scrollbar { width: 5px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 5px; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

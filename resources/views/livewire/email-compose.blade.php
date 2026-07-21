<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-start justify-center pt-10" x-data @keydown.escape.wire="close">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="close"></div>
            <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden mx-4 animate-scale-in" @click.stop>
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base">Tulis Email</h3>
                    <button wire:click="close" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Form --}}
                <div class="p-5 space-y-3 max-h-[65vh] overflow-y-auto scrollbar-thin">
                    {{-- Account Selector --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1 block">Dari</label>
                        <select wire:model="accountId" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                            <option value="">-- Pilih Akun --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name ?: $acc->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1 block">Ke <span class="text-red-400">*</span></label>
                        <input wire:model="form.to" type="email" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" placeholder="penerima@contoh.com" required>
                        @error('form.to') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 mb-1 block">CC</label>
                            <input wire:model="form.cc" type="text" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" placeholder="cc@contoh.com">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 mb-1 block">BCC</label>
                            <input wire:model="form.bcc" type="text" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" placeholder="bcc@contoh.com">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1 block">Subjek <span class="text-red-400">*</span></label>
                        <input wire:model="form.subject" type="text" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" placeholder="Subjek email" required>
                        @error('form.subject') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1 block">Isi Pesan</label>
                        <textarea
                            wire:model="form.body"
                            rows="10"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 resize-none"
                            placeholder="Tulis pesan Anda..."
                        ></textarea>
                    </div>

                    {{-- Attachments --}}
                    @if(!empty($attachments))
                        <div class="flex flex-wrap gap-2">
                            @foreach($attachments as $i => $f)
                                <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs group">
                                    <x-heroicon-o-paper-clip class="w-3.5 h-3.5 text-gray-400" />
                                    <span class="text-gray-600 dark:text-gray-400 max-w-[180px] truncate">{{ $f->getClientOriginalName() }}</span>
                                    <button wire:click="removeAttachment({{ $i }})" class="text-gray-400 hover:text-red-500 transition-colors">
                                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2">
                        <input type="file" wire:model="attachments" multiple class="hidden" id="compose-attachment-input" />
                        <label for="compose-attachment-input" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                            <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                            Lampirkan File
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="close" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">Batal</button>
                        <button wire:click="send" wire:loading.attr="disabled" class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors flex items-center gap-2">
                            <span wire:loading.remove wire:target="send">Kirim Email</span>
                            <span wire:loading wire:target="send">
                                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Mengirim...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .animate-scale-in {
        animation: compose-scale-in 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes compose-scale-in {
        0% { transform: scale(0.95) translateY(-10px); opacity: 0; }
        100% { transform: scale(1) translateY(0); opacity: 1; }
    }
    .scrollbar-thin::-webkit-scrollbar { width: 5px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 5px; }
</style>
@endpush

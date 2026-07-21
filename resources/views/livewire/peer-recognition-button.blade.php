<div>
    <button wire:click="$dispatch('openRecognitionModal', { userId: {{ $userId }}, userName: '{{ $userName }}' })"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-900/50 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        Beri Kudos
    </button>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.4)">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            @click.away="showModal = false">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Beri Kudos ke {{ $userName }}</h3>
                <button wire:click="close" class="text-gray-400 hover:text-gray-600 p-1">&times;</button>
            </div>

            @if($sent)
                <div class="px-6 py-12 text-center">
                    <div class="text-4xl mb-4">&#x1F389;</div>
                    <div class="text-lg font-semibold text-emerald-600 mb-2">Kudos Terkirim!</div>
                    <p class="text-gray-500 text-sm">{{ $userName }} akan menerima notifikasi.</p>
                    <button wire:click="close" class="mt-4 px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm hover:bg-gray-200 transition">Tutup</button>
                </div>
            @else
                <div class="px-6 py-4 space-y-4">
                    @error('recognitionBadge') <div class="text-xs text-red-500">{{ $message }}</div> @enderror

                    <div>
                        <label class="block text-sm font-medium mb-2">Pilih Badge</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($badges as $key => $label)
                                <button wire:click="$set('recognitionBadge', '{{ $key }}')"
                                    class="px-3 py-2 rounded-lg text-sm border-2 transition text-left
                                    {{ $recognitionBadge === $key
                                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-400'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Pesan</label>
                        <textarea wire:model="recognitionMessage" rows="3" placeholder="Kenapa kamu memberikan kudos?" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        @error('recognitionMessage') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex gap-2 justify-end pt-2">
                        <button wire:click="close"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button wire:click="send"
                            class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-sm font-medium transition">
                            Kirim Kudos
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

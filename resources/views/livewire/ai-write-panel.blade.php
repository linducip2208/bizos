<div x-data="{}"
     x-show="$wire.isOpen"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-hidden"
     style="display: none;"
     x-init="$watch('$wire.isOpen', val => { if (val) document.body.style.overflow = 'hidden'; else document.body.style.overflow = ''; })">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" wire:click="close"></div>

    {{-- Panel --}}
    <div x-show="$wire.isOpen"
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white dark:bg-stone-900 shadow-2xl flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-stone-200 dark:border-stone-700 shrink-0">
            <div class="flex items-center gap-3">
                <span class="text-xl">&#10024;</span>
                <h2 class="text-lg font-bold text-stone-900 dark:text-white">AI Tulis</h2>
            </div>
            <button wire:click="close"
                    class="p-2 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            {{-- Tone selector --}}
            <div>
                <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Gaya Penulisan</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button"
                            wire:click="$set('tone', 'formal')"
                            @class([
                                'px-3 py-2 text-sm rounded-lg border transition-all text-left',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $tone === 'formal',
                                'border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-400 hover:border-stone-300' => $tone !== 'formal',
                            ])>
                        <span class="block font-semibold">Formal</span>
                        <span class="block text-xs opacity-70">Profesional</span>
                    </button>
                    <button type="button"
                            wire:click="$set('tone', 'casual')"
                            @class([
                                'px-3 py-2 text-sm rounded-lg border transition-all text-left',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $tone === 'casual',
                                'border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-400 hover:border-stone-300' => $tone !== 'casual',
                            ])>
                        <span class="block font-semibold">Santai</span>
                        <span class="block text-xs opacity-70">Ramah</span>
                    </button>
                    <button type="button"
                            wire:click="$set('tone', 'persuasive')"
                            @class([
                                'px-3 py-2 text-sm rounded-lg border transition-all text-left',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $tone === 'persuasive',
                                'border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-400 hover:border-stone-300' => $tone !== 'persuasive',
                            ])>
                        <span class="block font-semibold">Persuasif</span>
                        <span class="block text-xs opacity-70">Meyakinkan</span>
                    </button>
                    <button type="button"
                            wire:click="$set('tone', 'empathetic')"
                            @class([
                                'px-3 py-2 text-sm rounded-lg border transition-all text-left',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $tone === 'empathetic',
                                'border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-400 hover:border-stone-300' => $tone !== 'empathetic',
                            ])>
                        <span class="block font-semibold">Empatik</span>
                        <span class="block text-xs opacity-70">Pengertian</span>
                    </button>
                </div>
            </div>

            {{-- Language selector --}}
            <div>
                <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Bahasa</label>
                <div class="flex gap-2">
                    <button type="button"
                            wire:click="$set('language', 'id')"
                            @class([
                                'px-4 py-2 text-sm rounded-lg border transition-all',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $language === 'id',
                                'border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-400' => $language !== 'id',
                            ])>
                        Bahasa Indonesia
                    </button>
                    <button type="button"
                            wire:click="$set('language', 'en')"
                            @class([
                                'px-4 py-2 text-sm rounded-lg border transition-all',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $language === 'en',
                                'border-stone-200 dark:border-stone-600 text-stone-600 dark:text-stone-400' => $language !== 'en',
                            ])>
                        English
                    </button>
                </div>
            </div>

            {{-- Mode selector --}}
            <div>
                <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Mode</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            wire:click="$set('mode', 'generate'); $set('generatedText', '')"
                            @class([
                                'px-3 py-1.5 text-xs rounded-full border transition-all font-medium',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $mode === 'generate',
                                'border-stone-200 dark:border-stone-600 text-stone-500' => $mode !== 'generate',
                            ])>
                        Tulis Baru
                    </button>
                    <button type="button"
                            wire:click="$set('mode', 'rewrite'); $set('generatedText', '')"
                            @class([
                                'px-3 py-1.5 text-xs rounded-full border transition-all font-medium',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $mode === 'rewrite',
                                'border-stone-200 dark:border-stone-600 text-stone-500' => $mode !== 'rewrite',
                            ])>
                        Tulis Ulang
                    </button>
                    <button type="button"
                            wire:click="$set('mode', 'summarize'); $set('generatedText', '')"
                            @class([
                                'px-3 py-1.5 text-xs rounded-full border transition-all font-medium',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $mode === 'summarize',
                                'border-stone-200 dark:border-stone-600 text-stone-500' => $mode !== 'summarize',
                            ])>
                        Ringkas
                    </button>
                    <button type="button"
                            wire:click="$set('mode', 'fix'); $set('generatedText', '')"
                            @class([
                                'px-3 py-1.5 text-xs rounded-full border transition-all font-medium',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $mode === 'fix',
                                'border-stone-200 dark:border-stone-600 text-stone-500' => $mode !== 'fix',
                            ])>
                        Perbaiki Grammar
                    </button>
                    <button type="button"
                            wire:click="$set('mode', 'translate'); $set('generatedText', '')"
                            @class([
                                'px-3 py-1.5 text-xs rounded-full border transition-all font-medium',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $mode === 'translate',
                                'border-stone-200 dark:border-stone-600 text-stone-500' => $mode !== 'translate',
                            ])>
                        Terjemahkan
                    </button>
                    <button type="button"
                            wire:click="$set('mode', 'expand'); $set('generatedText', '')"
                            @class([
                                'px-3 py-1.5 text-xs rounded-full border transition-all font-medium',
                                'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $mode === 'expand',
                                'border-stone-200 dark:border-stone-600 text-stone-500' => $mode !== 'expand',
                            ])>
                        Kembangkan
                    </button>
                </div>
            </div>

            {{-- Prompt input --}}
            <div>
                <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">
                    @if($mode === 'generate') Tulis tentang apa?
                    @elseif($mode === 'rewrite') Teks yang ingin ditulis ulang
                    @elseif($mode === 'summarize') Teks yang ingin diringkas
                    @elseif($mode === 'fix') Teks yang ingin diperbaiki
                    @elseif($mode === 'translate') Teks yang ingin diterjemahkan
                    @elseif($mode === 'expand') Teks yang ingin dikembangkan
                    @endif
                </label>
                <textarea wire:model.live="prompt"
                          rows="4"
                          class="w-full rounded-xl border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 text-stone-900 dark:text-white px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                          placeholder="Ketik di sini..."></textarea>
                @error('prompt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Context info --}}
            @if($contextInfo)
            <div class="bg-stone-50 dark:bg-stone-800 rounded-xl p-3 border border-stone-200 dark:border-stone-700">
                <p class="text-xs font-semibold text-stone-500 dark:text-stone-400 mb-1">Konteks Halaman</p>
                <p class="text-xs text-stone-600 dark:text-stone-300">{{ $contextInfo }}</p>
            </div>
            @endif

            {{-- Generate button --}}
            <button wire:click="{{ $mode === 'generate' ? 'generate' : ($mode === 'rewrite' ? 'rewrite(\''.addslashes($prompt).'\')' : ($mode === 'summarize' ? 'summarize(\''.addslashes($prompt).'\')' : ($mode === 'fix' ? 'fixGrammar(\''.addslashes($prompt).'\')' : ($mode === 'translate' ? 'translate(\''.addslashes($prompt).'\')' : 'expand(\''.addslashes($prompt).'\')')))) }}"
                    wire:loading.attr="disabled"
                    class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl font-semibold text-sm hover:from-indigo-700 hover:to-violet-700 disabled:opacity-50 transition-all shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="generate,summarize,rewrite,fixGrammar,translate,expand">&#10024; Generate</span>
                <span wire:loading wire:target="generate,summarize,rewrite,fixGrammar,translate,expand">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>

            {{-- Error message --}}
            @if($errorMessage)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3">
                <p class="text-sm text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
            </div>
            @endif

            {{-- Generated text --}}
            @if($generatedText)
            <div class="bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-2 bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                    <span class="text-xs font-semibold text-stone-500">Hasil Generate</span>
                    <div class="flex items-center gap-1">
                        <button wire:click="$set('mode', 'generate')"
                                class="px-2 py-1 text-xs text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors">
                            Generate Lagi
                        </button>
                    </div>
                </div>
                <div class="p-4 text-sm text-stone-700 dark:text-stone-300 whitespace-pre-wrap leading-relaxed max-h-60 overflow-y-auto">
                    {{ $generatedText }}
                </div>
                <div class="flex gap-2 px-4 py-3 bg-stone-50 dark:bg-stone-800 border-t border-stone-200 dark:border-stone-700">
                    <button wire:click="insert"
                            class="flex-1 py-2 px-4 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors">
                        Sisipkan ke Textarea
                    </button>
                    <button onclick="navigator.clipboard.writeText(@js($generatedText))"
                            class="py-2 px-3 bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300 rounded-lg text-sm hover:bg-stone-200 dark:hover:bg-stone-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

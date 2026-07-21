<div>
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4" x-data>
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" wire:click="close"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-700"
             @click.away="if(!$event.target.closest('button')) @this.close()">

            {{-- Header --}}
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg">Voice Commander</h2>
                        <p class="text-xs text-gray-500">Perintah suara Bahasa Indonesia</p>
                    </div>
                </div>
                <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-5">
                @if($errorMessage)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3 text-sm text-red-700 dark:text-red-300">
                    {{ $errorMessage }}
                </div>
                @endif

                {{-- Record Button --}}
                <div class="flex flex-col items-center gap-4">
                    <button wire:click="toggleRecording"
                            class="relative w-20 h-20 rounded-full flex items-center justify-center transition-all duration-300
                                   @if($isRecording) bg-red-500 scale-110 @else bg-indigo-500 hover:bg-indigo-600 @endif">
                        <div class="absolute inset-0 rounded-full animate-ping opacity-20 @if($isRecording) bg-red-500 @else bg-indigo-500 @endif" 
                             style="animation-duration: 1.5s"></div>
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                  d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    </button>

                    <p class="text-sm text-gray-500">
                        @if($isRecording)
                            <span class="text-red-500 font-medium animate-pulse">Merekam...</span>
                        @else
                            Klik mikrofon untuk mulai merekam
                        @endif
                    </p>
                </div>

                {{-- Upload --}}
                <div class="text-center">
                    <p class="text-xs text-gray-400 mb-2">atau upload file audio</p>
                    <input type="file" wire:model="audioFile" accept="audio/*" class="hidden" id="audioUpload">
                    <label for="audioUpload" 
                           class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-sm text-gray-500 hover:border-indigo-400 hover:text-indigo-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Pilih File Audio
                    </label>
                    @if($audioFile)
                    <p class="text-xs text-green-600 mt-1">{{ $audioFile->getClientOriginalName() }}</p>
                    @endif
                    <div wire:loading wire:target="audioFile" class="mt-2 text-sm text-indigo-600">
                        Memproses audio...
                    </div>
                </div>

                {{-- OR: text input --}}
                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t dark:border-gray-600"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white dark:bg-gray-800 px-3 text-xs text-gray-400">atau ketik</span></div>
                </div>

                <div>
                    <textarea wire:model="transcript"
                              rows="2"
                              placeholder="Ketik perintah suara... contoh: Buat task follow up client deadline besok"
                              class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:text-white"
                    ></textarea>
                    <button wire:click="submitText" wire:loading.attr="disabled"
                            class="mt-2 w-full py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Proses Perintah
                    </button>
                </div>

                {{-- Result --}}
                @if($parsedCommand)
                <div class="space-y-3">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 mb-1">Perintah Dikenali</p>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold text-white" style="background-color: {{ $this->getCommandColor($parsedCommand['command_type']) }}">
                                {{ $this->getCommandLabel($parsedCommand['command_type']) }}
                            </span>
                        </div>
                    </div>

                    @if($executionResult)
                    <div class="rounded-xl p-4 @if($executionResult['success']) bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 @else bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 @endif">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">
                                @if($executionResult['success']) &#9989; @else &#9888; @endif
                            </span>
                            <div>
                                <p class="font-medium text-sm">{{ $executionResult['message'] }}</p>
                                @if(!empty($executionResult['link']))
                                <a href="{{ $executionResult['link'] }}" 
                                   class="inline-block mt-2 text-xs text-indigo-600 hover:underline font-medium">
                                    Buka &#8594;
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Footer tips --}}
            <div class="px-6 pb-4">
                <p class="text-xs text-gray-400">
                    Tips: "Buat task follow up client deadline besok" | "Approve cuti Budi" | "Cek stok Laptop Asus"
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Floating mic button --}}
    <button wire:click="open"
            class="fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full bg-indigo-600 text-white shadow-lg hover:bg-indigo-700 hover:shadow-xl hover:scale-105 transition-all duration-200 flex items-center justify-center group"
            title="Voice Commander">
        <div class="absolute inset-0 rounded-full animate-ping opacity-25 bg-indigo-600" style="animation-duration: 2s"></div>
        <svg class="w-6 h-6 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                  d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
        </svg>
        <span class="absolute -top-8 right-0 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
            Voice Commander
        </span>
    </button>
</div>

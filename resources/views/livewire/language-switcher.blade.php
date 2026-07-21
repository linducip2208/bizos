<div class="flex items-center gap-2" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        <span class="text-base">{{ $availableLanguages[$currentLocale] ?? strtoupper($currentLocale) }}</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="open" @click.outside="open = false" x-transition
        class="absolute top-full right-0 mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-[160px] z-50">
        @foreach ($availableLanguages as $code => $label)
            <button wire:click="switchLanguage('{{ $code }}')"
                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 {{ $currentLocale === $code ? 'font-semibold text-primary-600' : 'text-gray-700 dark:text-gray-300' }}">
                @if ($currentLocale === $code)
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @else
                    <span class="w-4"></span>
                @endif
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>

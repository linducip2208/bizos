{{-- Locale Switcher for Filament Topbar --}}
@php
    $currentLocale = app()->getLocale();
    $locales = [
        'en' => ['label' => 'EN', 'flag' => '', 'name' => 'English'],
        'id' => ['label' => 'ID', 'flag' => '', 'name' => 'Bahasa Indonesia'],
    ];
@endphp
<div x-data="{ open: false }" class="relative flex items-center">
    <button
        x-on:click="open = !open"
        class="flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        title="{{ __('Language') }}"
    >
        <span class="font-semibold uppercase">{{ $locales[$currentLocale]['label'] ?? strtoupper($currentLocale) }}</span>
        <x-heroicon-o-globe-alt class="w-4 h-4" />
    </button>

    <div
        x-show="open"
        x-on:click.outside="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
        style="display: none;"
    >
        <div class="px-2 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Language') }}</div>
        @foreach ($locales as $code => $info)
            <a
                href="{{ route('locale.switch', $code) }}"
                class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors {{ $currentLocale === $code ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}"
            >
                <span class="text-lg">{{ $info['flag'] }}</span>
                <span>{{ $info['name'] }}</span>
                @if ($currentLocale === $code)
                    <x-heroicon-o-check class="w-4 h-4 ml-auto text-indigo-500" />
                @endif
            </a>
        @endforeach
    </div>
</div>

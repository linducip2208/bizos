<div class="space-y-4">
    <div class="flex items-center gap-3">
        <span class="text-xl">{{ $allReady ? '✅' : '⚠️' }}</span>
        <span class="font-semibold {{ $statusColor }}">{{ $statusText }}</span>
    </div>
    <div class="grid grid-cols-3 gap-3 text-sm">
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
            <div class="text-gray-500 dark:text-gray-400 text-xs mb-1">manifest.json</div>
            <div class="font-medium text-gray-900 dark:text-white">{{ $manifestCheck }} {{ $manifestSize }}</div>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
            <div class="text-gray-500 dark:text-gray-400 text-xs mb-1">Service Worker</div>
            <div class="font-medium text-gray-900 dark:text-white">{{ $swCheck }} {{ $swSize }}</div>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
            <div class="text-gray-500 dark:text-gray-400 text-xs mb-1">PWA Register</div>
            <div class="font-medium text-gray-900 dark:text-white">{{ $registerCheck }}</div>
        </div>
    </div>
    @if(!$allReady)
        <p class="text-xs text-amber-600 dark:text-amber-400">
            Jalankan <code class="bg-amber-50 dark:bg-amber-900/30 px-1 rounded">php artisan vendor:publish --tag=filament-assets</code> untuk memastikan aset PWA terpublikasi.
        </p>
    @endif
</div>

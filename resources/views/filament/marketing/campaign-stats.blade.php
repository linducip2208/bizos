<div class="p-2 space-y-1 text-sm">
    @foreach ($stats as $label => $value)
        <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700">
            <span class="text-gray-500 dark:text-gray-400">{{ $label }}</span>
            <span class="font-semibold">{{ $value }}</span>
        </div>
    @endforeach
</div>

@props(['textareaId' => '', 'contextInfo' => ''])

<button type="button"
    onclick="Livewire.dispatch('openAiWritePanel', { textareaId: '{{ $textareaId }}', context: '{!! addslashes($contextInfo) !!}' })"
    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-all border border-indigo-200 dark:border-indigo-800">
    <span>&#10024;</span>
    AI Tulis
</button>

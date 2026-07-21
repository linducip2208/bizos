{{-- sprint-task-card.blade.php --}}
@php
    $task = $sprintTask['task'] ?? [];
    $assignees = $sprintTask['task']['assignees'] ?? [];
    $taskId = $sprintTask['task_id'] ?? $sprintTask['id'];
    $taskTitle = $task['title'] ?? 'Tugas #' . $taskId;
    $taskStatus = $sprintTask['status'] ?? 'todo';
    $taskPriority = $task['priority'] ?? 'medium';
@endphp

<div class="sprint-card bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-3 cursor-grab active:cursor-grabbing shadow-sm"
     draggable="true"
     x-on:dragstart="event.dataTransfer.setData('sprintTaskId', '{{ $sprintTask['id'] }}')">
    <div class="flex items-start justify-between gap-2 mb-2">
        <a href="/admin/tasks/{{ $taskId }}/edit" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-2 flex-1">
            {{ $taskTitle }}
        </a>
        <button wire:click="removeTaskFromSprint({{ $sprintTask['id'] }})"
                class="text-gray-400 hover:text-red-500 transition flex-shrink-0"
                title="Hapus dari sprint">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
        @php
            $prioClass = match($taskPriority) {
                'high', 'urgent' => 'text-red-600 dark:text-red-400',
                'medium' => 'text-yellow-600 dark:text-yellow-400',
                default => 'text-gray-400'
            };
        @endphp
        <span class="{{ $prioClass }} flex items-center gap-0.5">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
            {{ ucfirst($taskPriority) }}
        </span>

        @if(!empty($assignees))
            <div class="flex -space-x-1 ml-auto">
                @foreach(array_slice($assignees, 0, 3) as $assignee)
                    <div class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-[9px] font-bold text-indigo-600 dark:text-indigo-400 ring-2 ring-white dark:ring-gray-900"
                         title="{{ $assignee['first_name'] ?? '' }}">
                        {{ strtoupper(substr($assignee['first_name'] ?? '?', 0, 1)) }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $project->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $project->start_date?->format('d M Y') }} - {{ $project->end_date?->format('d M Y') }}
                    · Progress {{ $project->progress_percent ?? 0 }}%
                </p>
            </div>
            <div class="flex gap-2">
                @foreach(['Day', 'Week', 'Month', 'Quarter'] as $mode)
                    <button wire:click="setViewMode('{{ $mode }}')"
                            class="px-3 py-1.5 text-sm rounded-lg font-medium transition
                            {{ $viewMode === $mode ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $mode === 'Day' ? 'Hari' : ($mode === 'Week' ? 'Minggu' : ($mode === 'Month' ? 'Bulan' : 'Triwulan')) }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Gantt Chart Container --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div id="gantt-chart" style="min-height: 400px;"></div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded bg-gray-400"></div>
                <span class="text-gray-600 dark:text-gray-400">Belum Mulai</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded bg-blue-500"></div>
                <span class="text-gray-600 dark:text-gray-400">Sedang Dikerjakan</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded bg-orange-500"></div>
                <span class="text-gray-600 dark:text-gray-400">Review</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded bg-green-500"></div>
                <span class="text-gray-600 dark:text-gray-400">Selesai</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-4 h-4" style="background: repeating-linear-gradient(45deg, #6366f1, #6366f1 2px, #a5b4fc 2px, #a5b4fc 4px); border-radius: 3px;"></div>
                <span class="text-gray-600 dark:text-gray-400">Milestone</span>
            </div>
        </div>
    </div>

    {{-- Frappe Gantt --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">
    <script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initGantt();

            Livewire.on('task-date-updated', () => {
                initGantt();
            });

            Livewire.hook('element.updated', (el) => {
                if (el.closest('#gantt-chart')) {
                    initGantt();
                }
            });
        });

        function initGantt() {
            const container = document.getElementById('gantt-chart');
            if (!container) return;
            container.innerHTML = '';

            const tasks = @json($tasks);
            const milestones = @json($milestones);

            const ganttTasks = [];

            function flattenTasks(taskList, parentId) {
                taskList.forEach(task => {
                    const ganttTask = {
                        id: task.id,
                        name: task.name,
                        start: task.start,
                        end: task.end,
                        progress: task.progress || 0,
                        custom_class: task.custom_class || '',
                        dependencies: task.dependencies || [],
                    };
                    if (parentId) ganttTask.parent_id = parentId;
                    ganttTasks.push(ganttTask);

                    if (task.children && task.children.length > 0) {
                        flattenTasks(task.children, task.id);
                    }
                });
            }

            flattenTasks(tasks, null);

            @if(!empty($milestones))
            @foreach($milestones as $ms)
            ganttTasks.push({
                id: 'ms-{{ $ms['id'] }}',
                name: '🏁 {{ $ms['name'] }}',
                start: '{{ $ms['target_date'] }}',
                end: '{{ $ms['target_date'] }}',
                progress: {{ $ms['status'] === 'completed' ? 100 : ($ms['status'] === 'in_progress' ? 50 : 0) }},
                custom_class: {{ $ms['status'] === 'completed' ? "'bar-green'" : "'bar-gray'" }},
                dependencies: [],
            });
            @endforeach
            @endif

            if (ganttTasks.length === 0) {
                container.innerHTML = '<div class="flex items-center justify-center h-40 text-gray-400 dark:text-gray-500 italic">Belum ada tugas. Tambahkan tugas terlebih dahulu.</div>';
                return;
            }

            const viewMode = '{{ strtolower($viewMode) }}';

            const gantt = new Gantt('#gantt-chart', ganttTasks, {
                view_mode: viewMode,
                date_format: 'YYYY-MM-DD',
                language: 'id',
                on_date_change: function(task, start, end) {
                    if (String(task.id).startsWith('ms-')) return;
                    const taskId = parseInt(task.id);
                    const startDate = moment(start).format('YYYY-MM-DD');
                    const endDate = moment(end).format('YYYY-MM-DD');
                    @this.call('updateTaskDate', taskId, startDate, endDate);
                },
                on_click: function(task) {
                    if (String(task.id).startsWith('ms-')) return;
                    window.location.href = '/admin/tasks/' + task.id + '/edit';
                },
                custom_popup_html: function(task) {
                    const endDate = task._end ? moment(task._end).format('DD MMM YYYY') : '-';
                    const assignees = task.assignees || '';
                    let html = '<div class="details-container">';
                    html += '<h5>' + task.name + '</h5>';
                    html += '<p>' + Math.round(task.progress) + '% selesai</p>';
                    html += '<p><strong>Mulai:</strong> ' + moment(task._start).format('DD MMM YYYY') + '</p>';
                    html += '<p><strong>Selesai:</strong> ' + endDate + '</p>';
                    if (assignees) {
                        html += '<p><strong>PIC:</strong> ' + assignees + '</p>';
                    }
                    html += '</div>';
                    return html;
                },
                bar_height: 30,
                bar_corner_radius: 4,
                arrow_curve: 5,
                padding: 18,
            });

            gantt.$svg.querySelectorAll('.bar-label').forEach(function(label) {
                let text = label.textContent.trim();
                if (text.length > 20) {
                    label.textContent = text.substring(0, 17) + '...';
                }
            });
        }
    </script>

    <style>
        #gantt-chart .gantt .bar-wrapper .bar { border-radius: 3px; }
        #gantt-chart .gantt .bar-label { font-size: 12px; }
        #gantt-chart .gantt .grid-header { fill: #f3f4f6; }
        .dark #gantt-chart .gantt .grid-header { fill: #374151; }
        .dark #gantt-chart .gantt .grid-row { fill: #1f2937; }
        .dark #gantt-chart text { fill: #d1d5db; }
        .dark #gantt-chart .gantt .bar-wrapper .bar { fill: #6366f1; }
        #gantt-chart .bar-green .bar-progress { fill: #10b981; }
        #gantt-chart .bar-green .bar { fill: #6ee7b7; }
        #gantt-chart .bar-blue .bar-progress { fill: #3b82f6; }
        #gantt-chart .bar-blue .bar { fill: #93c5fd; }
        #gantt-chart .bar-orange .bar-progress { fill: #f97316; }
        #gantt-chart .bar-orange .bar { fill: #fdba74; }
        #gantt-chart .bar-gray .bar-progress { fill: #9ca3af; }
        #gantt-chart .bar-gray .bar { fill: #d1d5db; }
        .gantt .today-highlight { fill: #ef4444; }
        .details-container { padding: 10px; min-width: 200px; }
        .details-container h5 { margin: 0 0 6px; font-size: 14px; font-weight: 600; }
        .details-container p { margin: 2px 0; font-size: 12px; }
    </style>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="space-y-6" x-data="{ showCreateSprint: false, selectedSprintId: {{ $activeSprint?->id ?? 0 }} }">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sprint: {{ $project->name }}</h1>
                @if($activeSprint)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $activeSprint->name ?? 'Active Sprint' }}
                        · {{ $activeSprint->start_date?->format('d M Y') }} - {{ $activeSprint->end_date?->format('d M Y') }}
                        · {{ round((\Carbon\Carbon::now()->diffInDays($activeSprint->end_date, false))) }} hari tersisa
                    </p>
                @endif
            </div>
        </div>

        {{-- Sprint Selector --}}
        <div class="flex gap-2 flex-wrap">
            @foreach($sprints as $sprint)
                <button wire:click="$set('activeSprint', \App\Models\Sprint::find({{ $sprint['id'] }})); $wire.loadData()"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ ($activeSprint && $activeSprint->id == $sprint['id']) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    {{ $sprint['name'] }}
                    <span class="text-xs ml-1 opacity-75">{{ match($sprint['status']) {'active' => '● Aktif', 'completed' => '✓ Selesai', default => '○ Planning'} }}</span>
                </button>
            @endforeach
        </div>

        @if($activeSprint)
            {{-- Sprint Goal --}}
            @if($activeSprint->goal)
                <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">Tujuan Sprint</span>
                    </div>
                    <p class="text-sm text-indigo-600 dark:text-indigo-400">{{ $activeSprint->goal }}</p>
                </div>
            @endif

            {{-- Sprint Actions --}}
            <div class="flex gap-2">
                @if($activeSprint->status === 'planning')
                    <button wire:click="startSprint({{ $activeSprint->id }})"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-500 transition">
                        Mulai Sprint
                    </button>
                @endif
                @if($activeSprint->status === 'active')
                    <button wire:click="completeSprint({{ $activeSprint->id }})"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-500 transition">
                        Selesaikan Sprint
                    </button>
                @endif
            </div>

            {{-- Sprint Board --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- To Do --}}
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 p-3 min-h-[200px]"
                     x-data="{ over: false }"
                     x-on:dragover.prevent="over = true"
                     x-on:dragleave.prevent="over = false"
                     x-on:drop.prevent="over = false; $wire.moveTaskStatus(parseInt($event.dataTransfer.getData('sprintTaskId')), 'todo')"
                     :class="{ 'ring-2 ring-blue-400': over }">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-gray-400"></div>
                            <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-200">To Do</h3>
                        </div>
                        <span class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full">{{ count($todoTasks) }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($todoTasks as $sprintTask)
                            @include('filament.partials.sprint-task-card', ['sprintTask' => $sprintTask])
                        @endforeach
                        @if(empty($todoTasks))
                            <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-6">Seret tugas ke sini</p>
                        @endif
                    </div>
                </div>

                {{-- In Progress --}}
                <div class="bg-blue-50 dark:bg-blue-900/10 rounded-xl border border-blue-200 dark:border-blue-800 p-3 min-h-[200px]"
                     x-data="{ over: false }"
                     x-on:dragover.prevent="over = true"
                     x-on:dragleave.prevent="over = false"
                     x-on:drop.prevent="over = false; $wire.moveTaskStatus(parseInt($event.dataTransfer.getData('sprintTaskId')), 'in_progress')"
                     :class="{ 'ring-2 ring-blue-500': over }">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                            <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-200">In Progress</h3>
                        </div>
                        <span class="text-xs bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full">{{ count($inProgressTasks) }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($inProgressTasks as $sprintTask)
                            @include('filament.partials.sprint-task-card', ['sprintTask' => $sprintTask])
                        @endforeach
                        @if(empty($inProgressTasks))
                            <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-6">Seret tugas ke sini</p>
                        @endif
                    </div>
                </div>

                {{-- Review --}}
                <div class="bg-orange-50 dark:bg-orange-900/10 rounded-xl border border-orange-200 dark:border-orange-800 p-3 min-h-[200px]"
                     x-data="{ over: false }"
                     x-on:dragover.prevent="over = true"
                     x-on:dragleave.prevent="over = false"
                     x-on:drop.prevent="over = false; $wire.moveTaskStatus(parseInt($event.dataTransfer.getData('sprintTaskId')), 'review')"
                     :class="{ 'ring-2 ring-orange-500': over }">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-orange-500"></div>
                            <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-200">Review</h3>
                        </div>
                        <span class="text-xs bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-400 px-2 py-0.5 rounded-full">{{ count($reviewTasks) }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($reviewTasks as $sprintTask)
                            @include('filament.partials.sprint-task-card', ['sprintTask' => $sprintTask])
                        @endforeach
                        @if(empty($reviewTasks))
                            <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-6">Seret tugas ke sini</p>
                        @endif
                    </div>
                </div>

                {{-- Done --}}
                <div class="bg-green-50 dark:bg-green-900/10 rounded-xl border border-green-200 dark:border-green-800 p-3 min-h-[200px]"
                     x-data="{ over: false }"
                     x-on:dragover.prevent="over = true"
                     x-on:dragleave.prevent="over = false"
                     x-on:drop.prevent="over = false; $wire.moveTaskStatus(parseInt($event.dataTransfer.getData('sprintTaskId')), 'done')"
                     :class="{ 'ring-2 ring-green-500': over }">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
                            <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-200">Done</h3>
                        </div>
                        <span class="text-xs bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 px-2 py-0.5 rounded-full">{{ count($doneTasks) }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($doneTasks as $sprintTask)
                            @include('filament.partials.sprint-task-card', ['sprintTask' => $sprintTask])
                        @endforeach
                        @if(empty($doneTasks))
                            <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-6">Seret tugas ke sini</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Backlog (Add Tasks) --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Backlog (Tersedia untuk Sprint)
                </h3>
                @if(count($allTasks) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($allTasks as $task)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2.5 border border-gray-200 dark:border-gray-700">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $task['title'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $task['status'] ?? 'todo' }}</p>
                                </div>
                                <button wire:click="addTaskToSprint({{ $activeSprint->id }}, {{ $task['id'] }})"
                                        class="ml-2 px-2 py-1 text-xs bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-900/60 font-medium transition flex-shrink-0">
                                    + Tambah
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">Semua tugas sudah ada di sprint.</p>
                @endif
            </div>

            {{-- Burndown Chart --}}
            @if(!empty($burndownData['labels']))
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Burndown Chart</h3>
                    <div class="w-full" style="height: 300px;">
                        <canvas id="burndownChart"></canvas>
                    </div>
                </div>
            @endif

            {{-- Velocity --}}
            @if(!empty($velocityData['sprints']))
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Velocity Sprint</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Rata-rata velocity: <strong>{{ $velocityData['average_velocity'] }}</strong> tugas per sprint</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-2 px-3 text-xs font-medium text-gray-500 uppercase">Sprint</th>
                                    <th class="text-center py-2 px-3 text-xs font-medium text-gray-500 uppercase">Total Tugas</th>
                                    <th class="text-center py-2 px-3 text-xs font-medium text-gray-500 uppercase">Selesai</th>
                                    <th class="text-center py-2 px-3 text-xs font-medium text-gray-500 uppercase">Completion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($velocityData['sprints'] as $vs)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                        <td class="py-2 px-3 text-gray-900 dark:text-white font-medium">{{ $vs['sprint_name'] }}</td>
                                        <td class="py-2 px-3 text-center text-gray-600 dark:text-gray-400">{{ $vs['total_tasks'] }}</td>
                                        <td class="py-2 px-3 text-center font-bold text-gray-900 dark:text-white">{{ $vs['completed_tasks'] }}</td>
                                        <td class="py-2 px-3 text-center">
                                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $vs['completion_rate'] >= 80 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                                {{ $vs['completion_rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum ada Sprint</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Buat sprint pertama untuk memulai manajemen sprint.</p>
            </div>
        @endif
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initBurndownChart();
            Livewire.hook('element.updated', () => {
                setTimeout(initBurndownChart, 200);
            });
        });

        function initBurndownChart() {
            const canvas = document.getElementById('burndownChart');
            if (!canvas) return;

            const existingChart = Chart.getChart(canvas);
            if (existingChart) existingChart.destroy();

            const labels = @json($burndownData['labels'] ?? []);
            const idealData = @json($burndownData['ideal'] ?? []);
            const actualData = @json($burndownData['actual'] ?? []);

            if (labels.length === 0) return;

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Ideal',
                            data: idealData,
                            borderColor: '#9ca3af',
                            borderDash: [6, 4],
                            borderWidth: 2,
                            pointRadius: 0,
                            fill: false,
                            tension: 0.1,
                        },
                        {
                            label: 'Aktual',
                            data: actualData,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointBackgroundColor: '#6366f1',
                            fill: true,
                            tension: 0.3,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Tugas Tersisa' },
                            ticks: { stepSize: 1 },
                        },
                        x: {
                            title: { display: true, text: 'Tanggal' },
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                    }
                }
            });
        }
    </script>

    <style>
        .sprint-card { transition: all 0.2s ease; }
        .sprint-card:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    </style>
</x-filament-panels::page>

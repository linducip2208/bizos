<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Manajer</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Tim, absensi, tugas, approval, dan cuti — {{ now()->translatedFormat('d F Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-full">
                    Tim: {{ $teamSize }} anggota
                </span>
            </div>
        </div>

        {{-- Top Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="flex flex-col p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20">
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Hadir Hari Ini</span>
                <span class="text-xl font-extrabold text-emerald-900 dark:text-emerald-200 mt-1">{{ $attendanceToday }}</span>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $attendanceLate }} terlambat</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20">
                <span class="text-xs font-medium text-red-600 dark:text-red-400">Tidak Hadir</span>
                <span class="text-xl font-extrabold text-red-900 dark:text-red-200 mt-1">{{ $attendanceAbsent }}</span>
                <span class="text-[11px] text-red-600 dark:text-red-400 mt-0.5">Tanpa keterangan</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Approval</span>
                <span class="text-xl font-extrabold text-amber-900 dark:text-amber-200 mt-1">{{ $pendingApprovals }}</span>
                <span class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5">Menunggu aksi</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Tugas Minggu Ini</span>
                <span class="text-xl font-extrabold text-blue-900 dark:text-blue-200 mt-1">{{ $tasksDueThisWeek }}</span>
                <span class="text-[11px] {{ $tasksOverdue > 0 ? 'text-red-600' : 'text-blue-600' }} mt-0.5">{{ $tasksOverdue }} overdue</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20">
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400">Cuti Pending</span>
                <span class="text-xl font-extrabold text-purple-900 dark:text-purple-200 mt-1">{{ $pendingLeaves }}</span>
                <span class="text-[11px] text-purple-600 dark:text-purple-400 mt-0.5">{{ $tasksCompleted }} tugas selesai</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20">
                <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">Proyek</span>
                <span class="text-xl font-extrabold text-indigo-900 dark:text-indigo-200 mt-1">{{ collect($projectStatus)->sum('count') }}</span>
                <span class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-0.5">Total semua status</span>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Attendance Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Kehadiran Tim (7 Hari)</h2>
                <div class="h-72">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            {{-- Project Status --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Status Proyek</h2>
                <div class="h-72">
                    <canvas id="projectChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Pending Approvals --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Approval Menunggu</h2>
                    <a href="{{ url('/admin/approval-requests') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto">
                    @foreach($approvalItems as $item)
                        <a href="{{ url('/admin/approval-requests/' . $item['id']) }}"
                           class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                <x-heroicon-o-clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['title'] }}</p>
                                <p class="text-xs text-gray-500">{{ $item['requester_name'] }} &middot; {{ $item['created_at'] }}</p>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 uppercase font-medium">{{ $item['module'] }}</span>
                        </a>
                    @endforeach
                    @if(empty($approvalItems))
                        <p class="p-4 text-sm text-gray-400 text-center">Tidak ada approval menunggu</p>
                    @endif
                </div>
            </div>

            {{-- Leave Requests --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Cuti Pending</h2>
                    <a href="{{ url('/admin/leaves') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto">
                    @foreach($recentLeaves as $leave)
                        <a href="{{ url('/admin/leaves/' . $leave['id']) }}"
                           class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                                <x-heroicon-o-calendar class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $leave['employee_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $leave['leave_type'] }} &middot; {{ $leave['start_date'] }} - {{ $leave['end_date'] }}</p>
                            </div>
                            <span class="text-xs font-medium text-purple-600">{{ $leave['total_days'] }} hari</span>
                        </a>
                    @endforeach
                    @if(empty($recentLeaves))
                        <p class="p-4 text-sm text-gray-400 text-center">Tidak ada cuti menunggu</p>
                    @endif
                </div>
            </div>

            {{-- Team Performance --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Performa Tim</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Anggota</th>
                                <th class="text-center p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Posisi</th>
                                <th class="text-center p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tugas Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teamPerformance as $member)
                                <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="p-4 font-medium text-gray-900 dark:text-white">{{ $member['name'] }}</td>
                                    <td class="p-4 text-center text-gray-500 text-xs">{{ $member['position'] }}</td>
                                    <td class="p-4 text-center">
                                        <span class="text-xs font-bold {{ ($member['total_tasks'] ?? 0) > 0 && ($member['completed_tasks'] / max($member['total_tasks'], 1) * 100) >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $member['completed_tasks'] }}/{{ $member['total_tasks'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            @if(empty($teamPerformance))
                                <tr><td colspan="3" class="p-4 text-center text-gray-400">Belum ada data tim</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Project Status Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Status Proyek</h2>
                </div>
                <div class="p-5 space-y-3">
                    @foreach($projectStatus as $p)
                        @php
                            $colors = [
                                'planning' => 'blue',
                                'active' => 'amber',
                                'on_hold' => 'orange',
                                'completed' => 'emerald',
                                'cancelled' => 'red',
                                'archived' => 'gray',
                            ];
                            $c = $colors[$p['status']] ?? 'indigo';
                            $statusLabels = [
                                'planning' => 'Perencanaan',
                                'active' => 'Dalam Pengerjaan',
                                'on_hold' => 'Ditunda',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                'archived' => 'Arsip',
                            ];
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-{{ $c }}-500"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $statusLabels[$p['status']] ?? ucfirst($p['status']) }}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $p['count'] }}</span>
                        </div>
                    @endforeach
                    @if(empty($projectStatus))
                        <p class="text-sm text-gray-400 text-center py-6">Belum ada proyek</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(75,85,99,0.2)' : 'rgba(209,213,219,0.5)';

        var attCtx = document.getElementById('attendanceChart');
        if (attCtx) {
            new Chart(attCtx, {
                type: 'line',
                data: {
                    labels: @json($attendanceChartLabels),
                    datasets: [{
                        label: 'Hadir',
                        data: @json($attendanceChartData),
                        borderColor: 'rgba(99,102,241,1)',
                        backgroundColor: 'rgba(99,102,241,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(99,102,241,1)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: textColor } }
                    },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: textColor } },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: textColor, stepSize: 1 }
                        }
                    }
                }
            });
        }

        var projCtx = document.getElementById('projectChart');
        if (projCtx) {
            var projLabels = @json(collect($projectStatus)->map(fn($p) => ['planning'=>'Perencanaan','active'=>'Dalam Pengerjaan','on_hold'=>'Ditunda','completed'=>'Selesai','cancelled'=>'Dibatalkan','archived'=>'Arsip'][$p['status']] ?? ucfirst($p['status']))->values()->toArray());
            var projData = @json(collect($projectStatus)->pluck('count')->values()->toArray());
            new Chart(projCtx, {
                type: 'doughnut',
                data: {
                    labels: projLabels,
                    datasets: [{
                        data: projData,
                        backgroundColor: ['rgba(59,130,246,0.7)', 'rgba(245,158,11,0.7)', 'rgba(249,115,22,0.7)', 'rgba(16,185,129,0.7)', 'rgba(239,68,68,0.7)', 'rgba(156,163,175,0.7)'],
                        borderColor: isDark ? 'rgba(31,41,55,0.8)' : 'rgba(255,255,255,0.8)',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { color: textColor, padding: 12, font: { size: 11 } } }
                    }
                }
            });
        }
    });
    </script>
    @endpush
</x-filament-panels::page>

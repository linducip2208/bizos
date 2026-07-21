<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Welcome Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Selamat datang kembali, {{ auth()->user()->name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ now()->translatedFormat('l, d F Y') }} &middot; Semangat bekerja! 🚀
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full">
                    <kbd class="font-mono text-[11px] px-1 py-0.5 rounded bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600">Ctrl+K</kbd> Command Palette
                </span>
            </div>
        </div>

        {{-- Stats Overview Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Pending Approvals --}}
            <a href="{{ url('/admin/approval-requests') }}"
               class="relative flex flex-col p-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 hover:shadow-md hover:border-amber-300 dark:hover:border-amber-700 transition-all group">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-800/40 flex items-center justify-center">
                        <x-heroicon-o-clipboard-document-check class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="text-xs font-medium text-amber-700 dark:text-amber-400">Approval</span>
                </div>
                <span class="text-2xl font-extrabold text-amber-900 dark:text-amber-200">{{ number_format($stats['pending_approvals'] ?? 0) }}</span>
                <span class="text-[11px] text-amber-600 dark:text-amber-500 mt-0.5">Menunggu persetujuan</span>
            </a>

            {{-- Tasks Due --}}
            <a href="{{ url('/admin/tasks') }}"
               class="relative flex flex-col p-4 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all group">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-800/40 flex items-center justify-center">
                        <x-heroicon-o-list-bullet class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <span class="text-xs font-medium text-blue-700 dark:text-blue-400">Tugas</span>
                </div>
                <span class="text-2xl font-extrabold text-blue-900 dark:text-blue-200">{{ number_format($stats['today_tasks'] ?? 0) }}</span>
                <span class="text-[11px] text-blue-600 dark:text-blue-500 mt-0.5">Tugas hari ini</span>
            </a>

            {{-- Unread Notifications --}}
            <a href="{{ url('/admin/notifications') }}"
               class="relative flex flex-col p-4 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 hover:shadow-md hover:border-red-300 dark:hover:border-red-700 transition-all group">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-800/40 flex items-center justify-center">
                        <x-heroicon-o-bell-alert class="w-4 h-4 text-red-600 dark:text-red-400" />
                    </div>
                    <span class="text-xs font-medium text-red-700 dark:text-red-400">Notifikasi</span>
                </div>
                <span class="text-2xl font-extrabold text-red-900 dark:text-red-200">{{ number_format($stats['unread_notifications'] ?? 0) }}</span>
                <span class="text-[11px] text-red-600 dark:text-red-500 mt-0.5">Belum dibaca</span>
            </a>

            {{-- Open Tickets --}}
            <a href="{{ url('/admin/tickets') }}"
               class="relative flex flex-col p-4 rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 hover:shadow-md hover:border-purple-300 dark:hover:border-purple-700 transition-all group">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-800/40 flex items-center justify-center">
                        <x-heroicon-o-ticket class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <span class="text-xs font-medium text-purple-700 dark:text-purple-400">Tiket</span>
                </div>
                <span class="text-2xl font-extrabold text-purple-900 dark:text-purple-200">{{ number_format($stats['open_tickets'] ?? 0) }}</span>
                <span class="text-[11px] text-purple-600 dark:text-purple-500 mt-0.5">Tiket terbuka</span>
            </a>

            {{-- Today Revenue --}}
            <a href="{{ url('/admin/pos-transactions') }}"
               class="relative flex flex-col p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 transition-all group">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-800/40 flex items-center justify-center">
                        <x-heroicon-o-banknotes class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Revenue</span>
                </div>
                <span class="text-2xl font-extrabold text-emerald-900 dark:text-emerald-200">Rp{{ number_format(($stats['today_revenue'] ?? 0) / 1000, 0) }}K</span>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-500 mt-0.5">Pendapatan hari ini</span>
            </a>

            {{-- Pending Invoices --}}
            <a href="{{ url('/admin/invoices') }}"
               class="relative flex flex-col p-4 rounded-xl border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 hover:shadow-md hover:border-orange-300 dark:hover:border-orange-700 transition-all group">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-800/40 flex items-center justify-center">
                        <x-heroicon-o-document-text class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                    </div>
                    <span class="text-xs font-medium text-orange-700 dark:text-orange-400">Faktur</span>
                </div>
                <span class="text-2xl font-extrabold text-orange-900 dark:text-orange-200">{{ number_format($stats['pending_invoices'] ?? 0) }}</span>
                <span class="text-[11px] text-orange-600 dark:text-orange-500 mt-0.5">Faktur jatuh tempo</span>
            </a>
        </div>

        {{-- Two-column layout: Main content + Sidebar --}}
        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Left column (span 2) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Quick Actions Grid --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Aksi Cepat</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @php
                            $colorClasses = [
                                'indigo' => [
                                    'hover' => 'hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10',
                                    'bg' => 'bg-indigo-100 dark:bg-indigo-900/30',
                                    'text' => 'text-indigo-600 dark:text-indigo-400',
                                ],
                                'blue' => [
                                    'hover' => 'hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/50 dark:hover:bg-blue-900/10',
                                    'bg' => 'bg-blue-100 dark:bg-blue-900/30',
                                    'text' => 'text-blue-600 dark:text-blue-400',
                                ],
                                'emerald' => [
                                    'hover' => 'hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10',
                                    'bg' => 'bg-emerald-100 dark:bg-emerald-900/30',
                                    'text' => 'text-emerald-600 dark:text-emerald-400',
                                ],
                                'amber' => [
                                    'hover' => 'hover:shadow-md hover:border-amber-300 dark:hover:border-amber-700 hover:bg-amber-50/50 dark:hover:bg-amber-900/10',
                                    'bg' => 'bg-amber-100 dark:bg-amber-900/30',
                                    'text' => 'text-amber-600 dark:text-amber-400',
                                ],
                                'violet' => [
                                    'hover' => 'hover:shadow-md hover:border-violet-300 dark:hover:border-violet-700 hover:bg-violet-50/50 dark:hover:bg-violet-900/10',
                                    'bg' => 'bg-violet-100 dark:bg-violet-900/30',
                                    'text' => 'text-violet-600 dark:text-violet-400',
                                ],
                                'rose' => [
                                    'hover' => 'hover:shadow-md hover:border-rose-300 dark:hover:border-rose-700 hover:bg-rose-50/50 dark:hover:bg-rose-900/10',
                                    'bg' => 'bg-rose-100 dark:bg-rose-900/30',
                                    'text' => 'text-rose-600 dark:text-rose-400',
                                ],
                            ];
                        @endphp
                        @foreach($quickActions as $action)
                            @php $c = $colorClasses[$action['color']] ?? $colorClasses['indigo']; @endphp
                            <a href="{{ $action['url'] }}"
                               class="group flex flex-col gap-2 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 {{ $c['hover'] }} transition-all duration-200">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg {{ $c['bg'] }} flex items-center justify-center group-hover:scale-110 transition-transform">
                                        @php
                                            $iconName = str_replace('heroicon-o-', '', $action['icon']);
                                        @endphp
                                        <x-dynamic-component :component="'heroicon-o-' . $iconName" class="w-4 h-4 {{ $c['text'] }}" />
                                    </div>
                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $action['label'] }}</span>
                                </div>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">{{ $action['description'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Pending Approvals --}}
                @if(!empty($pendingApprovals))
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Approval Menunggu</h2>
                            <a href="{{ url('/admin/approval-requests') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua</a>
                        </div>
                        <div class="space-y-2">
                            @foreach($pendingApprovals as $approval)
                                <a href="{{ url('/admin/approval-requests/' . ($approval['id'] ?? '#')) }}"
                                   class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $approval['approvable_type'] ?? 'Request' }} #{{ $approval['id'] ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $approval['requester']['name'] ?? 'Unknown' }} &middot; {{ \Carbon\Carbon::parse($approval['created_at'] ?? now())->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 font-medium">Menunggu</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Today's Tasks --}}
                @if(!empty($todayTasks))
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Tugas Hari Ini</h2>
                            <a href="{{ url('/admin/tasks') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua</a>
                        </div>
                        <div class="space-y-2">
                            @foreach($todayTasks as $task)
                                <a href="{{ url('/admin/tasks/' . ($task['id'] ?? '#')) }}"
                                   class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $task['title'] ?? 'Untitled Task' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $task['project']['name'] ?? 'No Project' }} &middot; Due {{ \Carbon\Carbon::parse($task['due_date'] ?? now())->translatedFormat('d M') }}
                                        </p>
                                    </div>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-medium">{{ $task['status'] ?? 'pending' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- Right sidebar --}}
            <div class="space-y-6">

                {{-- Recently Viewed --}}
                @if(!empty($recentlyViewed))
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3">Terbaru Dilihat</h2>
                        <div class="space-y-1.5">
                            @foreach(array_slice($recentlyViewed, 0, 8) as $recent)
                                <a href="{{ $recent['resource_url'] ?? '#' }}"
                                   class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                    <x-heroicon-o-clock class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 group-hover:text-indigo-500 flex-shrink-0" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $recent['resource_label'] ?? 'Unknown' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Favorites --}}
                @if(!empty($favorites))
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3">
                            <x-heroicon-o-star class="w-4 h-4 inline-block text-amber-400 mr-1" /> Favorit
                        </h2>
                        <div class="space-y-1.5">
                            @foreach($favorites as $fav)
                                <a href="{{ $fav['resource_url'] ?? '#' }}"
                                   class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                    <div class="w-7 h-7 rounded-md bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-bookmark class="w-3.5 h-3.5 text-indigo-500" />
                                    </div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate group-hover:text-indigo-600">{{ $fav['resource_label'] ?? 'Unknown' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- AI Insight Card --}}
                @if($aiModuleActive)
                    <div class="bg-gradient-to-br from-fuchsia-50 to-indigo-50 dark:from-fuchsia-900/20 dark:to-indigo-900/20 rounded-xl border border-fuchsia-200 dark:border-fuchsia-800 p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-fuchsia-500 to-indigo-500 flex items-center justify-center">
                                <x-heroicon-o-sparkles class="w-4 h-4 text-white" />
                            </div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">AI Insight</h2>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-3">
                            Gunakan AI Assistant untuk analisis bisnis, pembuatan laporan, dan rekomendasi cerdas.
                        </p>
                        <a href="{{ url('/admin/ai-conversations') }}"
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-fuchsia-600 dark:text-fuchsia-400 hover:text-fuchsia-700 dark:hover:text-fuchsia-300 transition-colors">
                            Buka AI Assistant
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </a>
                    </div>
                @endif

                {{-- Shortcuts --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3">Shortcut Keyboard</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Command Palette</span>
                            <kbd class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-xs font-mono text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600">Ctrl + K</kbd>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">App Launcher</span>
                            <kbd class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-xs font-mono text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600">Ctrl + B</kbd>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Global Search</span>
                            <kbd class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-xs font-mono text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600">Ctrl + /</kbd>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-filament-panels::page>

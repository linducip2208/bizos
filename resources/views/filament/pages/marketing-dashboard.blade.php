<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::section>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Kampanye</div>
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $campaignStats['total_campaigns'] }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $campaignStats['active_campaigns'] }} aktif</div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Terkirim</div>
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($campaignStats['total_sent']) }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($campaignStats['total_opened']) }} dibuka</div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Open Rate</div>
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($campaignStats['avg_open_rate'], 1) }}%</div>
                <div class="text-xs text-gray-400 mt-1">{{ number_format($campaignStats['avg_click_rate'], 1) }}% click rate</div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Landing Page</div>
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $landingPageStats['published_pages'] }}</div>
                <div class="text-xs text-gray-400 mt-1">dari {{ $landingPageStats['total_pages'] }} total</div>
            </x-filament::section>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-filament::section>
                <x-slot name="heading">Funnel Lead</x-slot>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Total Lead</span>
                            <span class="font-semibold">{{ $leadFunnel['total'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-primary-500 h-2.5 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-danger-500 inline-block"></span> Hot</span>
                            <span class="font-semibold">{{ $leadFunnel['hot'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-danger-500 h-2.5 rounded-full" style="width: {{ $leadFunnel['total'] > 0 ? round(($leadFunnel['hot'] / $leadFunnel['total']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-warning-500 inline-block"></span> Warm</span>
                            <span class="font-semibold">{{ $leadFunnel['warm'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-warning-500 h-2.5 rounded-full" style="width: {{ $leadFunnel['total'] > 0 ? round(($leadFunnel['warm'] / $leadFunnel['total']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-info-500 inline-block"></span> Cold</span>
                            <span class="font-semibold">{{ $leadFunnel['cold'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-info-500 h-2.5 rounded-full" style="width: {{ $leadFunnel['total'] > 0 ? round(($leadFunnel['cold'] / $leadFunnel['total']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-gray-500">Lead Baru (7 hari)</span>
                            <div class="font-semibold">{{ $leadFunnel['new_this_week'] }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Lead Baru (30 hari)</span>
                            <div class="font-semibold">{{ $leadFunnel['new_this_month'] }}</div>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Aktivitas Lead Terbaru</x-slot>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse ($recentActivities as $activity)
                        <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="flex-shrink-0 mt-0.5">
                                @switch($activity['activity_type'])
                                    @case('email_opened')
                                        <x-heroicon-o-envelope-open class="w-4 h-4 text-primary-500" />
                                        @break
                                    @case('email_clicked')
                                        <x-heroicon-o-cursor-arrow-rays class="w-4 h-4 text-warning-500" />
                                        @break
                                    @case('page_visited')
                                        <x-heroicon-o-globe-alt class="w-4 h-4 text-info-500" />
                                        @break
                                    @case('form_submitted')
                                        <x-heroicon-o-clipboard-document-check class="w-4 h-4 text-success-500" />
                                        @break
                                    @case('deal_created')
                                        <x-heroicon-o-currency-dollar class="w-4 h-4 text-danger-500" />
                                        @break
                                    @default
                                        <x-heroicon-o-bell class="w-4 h-4 text-gray-400" />
                                @endswitch
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate">{{ $activity['lead_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $activity['activity_label'] }}</div>
                            </div>
                            <div class="text-xs text-gray-400 flex-shrink-0">{{ $activity['created_at'] }}</div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 py-8 text-sm">Belum ada aktivitas lead</div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>

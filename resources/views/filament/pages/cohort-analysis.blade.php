<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-end gap-2">
            <x-filament::button wire:click="loadData" size="sm" color="gray" icon="heroicon-o-arrow-path">
                Refresh
            </x-filament::button>
        </div>

        {{-- Employee Retention Cohort --}}
        <x-filament::section>
            <x-slot name="heading">Retensi Karyawan (Kohort per Bulan Masuk)</x-slot>
            <x-slot name="description">Persentase karyawan yang bertahan per bulan sejak bergabung</x-slot>

            @php $empCohorts = $employeeCohort['cohorts'] ?? []; $empMax = $employeeCohort['max_periods'] ?? 0; @endphp
            @if(!empty($empCohorts))
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2 text-left font-semibold sticky left-0 bg-white dark:bg-gray-900 z-10">Kohort</th>
                                <th class="p-2 text-right">Masuk</th>
                                @for($m = 0; $m < $empMax; $m++)
                                    <th class="p-2 text-center">Bln {{ $m }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empCohorts as $row)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="p-2 font-medium sticky left-0 bg-white dark:bg-gray-900 z-10">{{ $row['cohort'] }}</td>
                                    <td class="p-2 text-right font-semibold">{{ $row['hired'] }}</td>
                                    @for($m = 0; $m < $empMax; $m++)
                                        @php $val = $row['month_' . $m] ?? null; @endphp
                                        <td class="p-2 text-center" style="background-color: {{ $this->cohortColor($val) }}">
                                            <span class="font-mono {{ is_null($val) ? 'text-gray-300' : ($val >= 80 ? 'text-white' : ($val >= 50 ? 'text-gray-800' : 'text-gray-700')) }}">
                                                {{ is_null($val) ? '-' : $val . '%' }}
                                            </span>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-gray-400">Data karyawan tidak tersedia.</div>
            @endif
        </x-filament::section>

        {{-- Customer Retention Cohort --}}
        <x-filament::section>
            <x-slot name="heading">Retensi Pelanggan (Kohort per Bulan Akuisisi)</x-slot>
            <x-slot name="description">Persentase pelanggan yang melakukan repeat purchase per bulan</x-slot>

            @php $custCohorts = $customerCohort['cohorts'] ?? []; $custMax = $customerCohort['max_periods'] ?? 0; @endphp
            @if(!empty($custCohorts))
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2 text-left font-semibold sticky left-0 bg-white dark:bg-gray-900 z-10">Kohort</th>
                                <th class="p-2 text-right">Akuisisi</th>
                                @for($m = 1; $m <= $custMax; $m++)
                                    <th class="p-2 text-center">Bln {{ $m }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($custCohorts as $row)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="p-2 font-medium sticky left-0 bg-white dark:bg-gray-900 z-10">{{ $row['cohort'] }}</td>
                                    <td class="p-2 text-right font-semibold">{{ $row['acquired'] }}</td>
                                    @for($m = 1; $m <= $custMax; $m++)
                                        @php $val = $row['month_' . $m] ?? null; @endphp
                                        <td class="p-2 text-center" style="background-color: {{ $this->cohortColor($val) }}">
                                            <span class="font-mono {{ is_null($val) ? 'text-gray-300' : ($val >= 80 ? 'text-white' : ($val >= 50 ? 'text-gray-800' : 'text-gray-700')) }}">
                                                {{ is_null($val) ? '-' : $val . '%' }}
                                            </span>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-gray-400">Data pelanggan tidak tersedia.</div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>

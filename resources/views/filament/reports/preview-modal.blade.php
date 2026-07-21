@if(isset($data) && $data->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead>
                <tr class="bg-stone-100 dark:bg-stone-800">
                    @foreach(array_keys((array) $data->first()) as $header)
                        <th class="px-3 py-2 text-left font-semibold text-stone-700 dark:text-stone-300 whitespace-nowrap">
                            {{ ucwords(str_replace('_', ' ', $header)) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data->take(50) as $row)
                    <tr class="border-t border-stone-200 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800/50">
                        @foreach((array) $row as $value)
                            <td class="px-3 py-1.5 text-stone-600 dark:text-stone-400 whitespace-nowrap">
                                @if(is_numeric($value) && !is_int($value))
                                    {{ number_format($value, 2) }}
                                @elseif(is_numeric($value))
                                    {{ number_format($value) }}
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($data->count() > 50)
            <p class="text-xs text-stone-400 p-2">Menampilkan 50 dari {{ $data->count() }} baris.</p>
        @endif
    </div>
@else
    <p class="text-stone-400 p-4 text-sm">Tidak ada data untuk ditampilkan.</p>
@endif

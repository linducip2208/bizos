<div class="space-y-4 p-1">
    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-stone-500">Pengguna</div>
            <div class="mt-1 text-2xl font-bold text-stone-800">{{ $usage['users_count'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-stone-500">Karyawan</div>
            <div class="mt-1 text-2xl font-bold text-stone-800">{{ $usage['employees_count'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-stone-500">Storage</div>
            <div class="mt-1 text-2xl font-bold text-stone-800">{{ $usage['storage_used_mb'] ?? 0 }} MB</div>
        </div>
        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-stone-500">Transaksi (Bulan Ini)</div>
            <div class="mt-1 text-2xl font-bold text-stone-800">{{ $usage['transactions_this_month'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-stone-500">API Calls (Hari Ini)</div>
            <div class="mt-1 text-2xl font-bold text-stone-800">{{ $usage['api_calls_today'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-stone-500">Status</div>
            <div class="mt-1">
                @if($usage['is_suspended'] ?? false)
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                        Disuspend
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                        Aktif
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if($usage['subscription_end'] ?? null)
        <div class="rounded-xl border border-stone-200 bg-amber-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-amber-700">Langganan</div>
            <div class="mt-1 text-sm text-amber-800">
                Mulai: {{ $usage['subscription_start'] ?? '-' }} &mdash; Akhir: {{ $usage['subscription_end'] }}
            </div>
        </div>
    @endif
</div>

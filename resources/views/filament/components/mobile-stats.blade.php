<div class="grid grid-cols-2 gap-4 text-sm">
    <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
        <div class="text-amber-600 dark:text-amber-400 text-xs mb-1 font-medium">Action Pending</div>
        <div class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $offlinePending }}</div>
        <div class="text-xs text-amber-600/70 dark:text-amber-400/70 mt-1">Menunggu sinkronisasi</div>
    </div>
    <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
        <div class="text-red-600 dark:text-red-400 text-xs mb-1 font-medium">Action Gagal</div>
        <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $offlineFailed }}</div>
        <div class="text-xs text-red-600/70 dark:text-red-400/70 mt-1">Perlu perhatian</div>
    </div>
    <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800">
        <div class="text-indigo-600 dark:text-indigo-400 text-xs mb-1 font-medium">Pengguna Biometric</div>
        <div class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ $biometricUsers }}</div>
        <div class="text-xs text-indigo-600/70 dark:text-indigo-400/70 mt-1">Akun terdaftar</div>
    </div>
    <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
        <div class="text-emerald-600 dark:text-emerald-400 text-xs mb-1 font-medium">Perangkat Terdaftar</div>
        <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $biometricDevices }}</div>
        <div class="text-xs text-emerald-600/70 dark:text-emerald-400/70 mt-1">Total perangkat</div>
    </div>
</div>

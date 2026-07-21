<x-filament-panels::page>
    <div class="space-y-6">
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Email Inbox</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Konfigurasi email provider Anda di menu <strong>Integrasi</strong> untuk mengaktifkan inbox.
                </p>
                <div class="mt-6">
                    <a href="{{ url('/admin/integrations') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Buka Pengaturan Integrasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

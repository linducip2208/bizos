<x-filament-panels::page>
    <div class="px-6 py-6 max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-2">Generator Laporan ESG</h1>
        <p class="text-stone-500 mb-8">Generate laporan ESG sesuai kerangka standar: GRI, POJK 51, IFRS S1-S2, atau SASB.</p>

        @if($message)
        <div class="mb-6 p-4 rounded-xl {{ $success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
            {{ $message }}
        </div>
        @endif

        <div class="fi-section rounded-xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Periode</label>
                    <input type="month" wire:model="selectedPeriod" class="fi-input block w-full rounded-lg border-stone-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Kerangka Pelaporan</label>
                    <select wire:model="selectedFramework" class="fi-input block w-full rounded-lg border-stone-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="gri">GRI Standards (Global)</option>
                        <option value="pojk_51">POJK 51/2017 (Indonesia)</option>
                        <option value="ifrs_s1_s2">IFRS S1-S2 (ISSB)</option>
                        <option value="sasb">SASB Standards</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-stone-50 dark:bg-stone-800 rounded-lg p-3 text-center">
                    <div class="text-xs text-stone-400 mb-1">Cakupan 1</div>
                    <div class="font-bold text-sm">Emisi Langsung</div>
                </div>
                <div class="bg-stone-50 dark:bg-stone-800 rounded-lg p-3 text-center">
                    <div class="text-xs text-stone-400 mb-1">Cakupan 2</div>
                    <div class="font-bold text-sm">Listrik</div>
                </div>
                <div class="bg-stone-50 dark:bg-stone-800 rounded-lg p-3 text-center">
                    <div class="text-xs text-stone-400 mb-1">Cakupan 3</div>
                    <div class="font-bold text-sm">Rantai Nilai</div>
                </div>
                <div class="bg-stone-50 dark:bg-stone-800 rounded-lg p-3 text-center">
                    <div class="text-xs text-stone-400 mb-1">Skor ESG</div>
                    <div class="font-bold text-sm">Grading</div>
                </div>
            </div>

            <button wire:click="generateReport" wire:loading.attr="disabled"
                class="fi-btn fi-btn-primary w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold">
                <span wire:loading.remove>Generate Laporan ESG</span>
                <span wire:loading>Menghitung & Generate...</span>
            </button>
        </div>

        <div class="mt-8 text-sm text-stone-400">
            <p>Laporan akan disimpan dalam format PDF dan tersedia di halaman ini untuk diunduh. Data dihitung real-time dari:</p>
            <ul class="list-disc ml-5 mt-1 space-y-0.5">
                <li>Data BBM kendaraan (VehicleFuelLog)</li>
                <li>Data konsumsi listrik (EnergyReading)</li>
                <li>Data pengadaan (PurchaseOrder)</li>
                <li>Data limbah & air (WasteRecord, WaterUsage)</li>
                <li>Data karyawan (Employee)</li>
                <li>Data kepatuhan (DataBreach, DpiaAssessment, IsoRisk)</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Preview --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h3 class="font-semibold text-stone-800 mb-3">Pratinjau Tema</h3>
            @if ($previewImage)
                <img src="{{ $previewImage }}" alt="Theme Preview" class="w-full max-w-lg rounded-lg border border-stone-200">
            @endif
        </div>

        {{-- Presets --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h3 class="font-semibold text-stone-800 mb-3">Preset Tema</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach ($presets as $key => $preset)
                    <button wire:click="applyPreset('{{ $key }}')"
                        class="p-3 rounded-lg border border-stone-200 hover:border-indigo-300 hover:shadow-sm transition text-left">
                        <div class="flex gap-1.5 mb-2">
                            <div class="w-5 h-5 rounded-full" style="background:{{ $preset['primary_color'] }}"></div>
                            <div class="w-5 h-5 rounded-full" style="background:{{ $preset['secondary_color'] }}"></div>
                            <div class="w-5 h-5 rounded-full" style="background:{{ $preset['accent_color'] }}"></div>
                        </div>
                        <p class="text-xs font-semibold text-stone-700">{{ $preset['name'] }}</p>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Theme Configuration Form --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <form wire:submit="saveTheme">
                {{ $this->form }}

                <div class="flex gap-3 mt-6 pt-4 border-t border-stone-200">
                    <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                        Terapkan Theme
                    </button>
                    <button type="button" wire:click="resetTheme"
                        class="px-6 py-2.5 bg-stone-100 text-stone-700 rounded-lg text-sm font-semibold hover:bg-stone-200 transition">
                        Reset ke Default
                    </button>
                    <button type="button" wire:click="exportTheme"
                        class="px-6 py-2.5 bg-stone-100 text-stone-700 rounded-lg text-sm font-semibold hover:bg-stone-200 transition">
                        Export Theme
                    </button>
                </div>
            </form>

            {{-- CSS Preview --}}
            <div class="mt-6 pt-4 border-t border-stone-200">
                <h4 class="font-semibold text-stone-800 mb-2">CSS yang Dihasilkan</h4>
                @php
                    $service = app(\App\Services\ThemeBuilderService::class);
                    $css = $service->generateCss($themeConfig ?? []);
                @endphp
                <pre class="bg-stone-900 text-green-400 p-4 rounded-lg text-xs overflow-auto max-h-96">{{ $css }}</pre>
            </div>
        </div>
    </div>
</x-filament-panels::page>

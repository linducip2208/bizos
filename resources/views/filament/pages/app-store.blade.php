<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tabs --}}
        <div class="flex gap-2 border-b border-stone-200 pb-3 mb-4">
            <button wire:click="$set('activeTab', 'browse')"
                class="px-4 py-2 text-sm font-semibold rounded-t-lg transition
                    {{ $activeTab === 'browse' ? 'bg-white text-indigo-600 border border-b-white border-stone-200 -mb-px' : 'text-stone-500 hover:text-stone-700' }}">
                Jelajahi Aplikasi
            </button>
            <button wire:click="$set('activeTab', 'installed')"
                class="px-4 py-2 text-sm font-semibold rounded-t-lg transition
                    {{ $activeTab === 'installed' ? 'bg-white text-indigo-600 border border-b-white border-stone-200 -mb-px' : 'text-stone-500 hover:text-stone-700' }}">
                Aplikasi Saya ({{ count($installedIds) }})
            </button>
            <button wire:click="$set('activeTab', 'featured')"
                class="px-4 py-2 text-sm font-semibold rounded-t-lg transition
                    {{ $activeTab === 'featured' ? 'bg-white text-indigo-600 border border-b-white border-stone-200 -mb-px' : 'text-stone-500 hover:text-stone-700' }}">
                Unggulan
            </button>
        </div>

        {{-- App Detail Modal --}}
        @if ($selectedApp && $selectedAppId)
            <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-indigo-50 to-violet-50 border-b border-stone-200">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            @if ($selectedApp->icon)
                                <img src="{{ $selectedApp->icon }}" alt="{{ $selectedApp->name }}" class="w-12 h-12 rounded-xl">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-lg font-bold">
                                    {{ strtoupper(substr($selectedApp->name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <h2 class="text-xl font-bold text-stone-900">{{ $selectedApp->name }}</h2>
                                <p class="text-sm text-stone-500">oleh {{ $selectedApp->developer }} &middot; v{{ $selectedApp->version }}</p>
                            </div>
                        </div>
                        <button wire:click="closeAppDetail" class="text-stone-400 hover:text-stone-600">&times;</button>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-stone-700">{{ $selectedApp->description }}</p>

                    @if ($selectedApp->features)
                        <div>
                            <h3 class="font-semibold text-stone-800 mb-2">Fitur</h3>
                            <ul class="grid grid-cols-2 gap-2">
                                @foreach ((array) $selectedApp->features as $feature => $desc)
                                    <li class="flex items-start gap-2 text-sm text-stone-600">
                                        <span class="text-indigo-500 mt-0.5">&#10003;</span>
                                        <span><strong>{{ $feature }}:</strong> {{ $desc }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex items-center gap-3 pt-3 border-t border-stone-100">
                        <span class="text-xl font-bold text-indigo-600">{{ $selectedApp->priceLabel() }}</span>
                        <span class="text-sm text-stone-400">&middot; {{ number_format($selectedApp->total_installs) }} install</span>
                        <span class="text-sm text-stone-400">&middot; rating {{ number_format($selectedApp->rating, 1) }}/5.0</span>
                    </div>

                    <div class="flex gap-2 pt-2">
                        @if ($isInstalled($selectedApp->id))
                            <button wire:click="uninstallApp({{ $selectedApp->id }})"
                                class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-100 transition">
                                Uninstall
                            </button>
                        @else
                            <button wire:click="installApp({{ $selectedApp->id }})"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                                @if ($selectedApp->price_type === 'free')
                                    Install Gratis
                                @else
                                    Beli & Install
                                @endif
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Browse Tab --}}
        @if ($activeTab === 'browse')
            <div class="flex gap-3 mb-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari aplikasi..."
                    class="flex-1 px-4 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                <select wire:model.live="selectedCategory"
                    class="px-4 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($apps as $app)
                    <div wire:click="browseApp({{ $app->id }})"
                        class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md hover:border-indigo-200 transition cursor-pointer group">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($app->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-stone-900 group-hover:text-indigo-600 transition truncate">{{ $app->name }}</h3>
                                    @if ($isInstalled($app->id))
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Installed</span>
                                    @endif
                                </div>
                                <p class="text-xs text-stone-500 mt-1">{{ $app->developer }} &middot; v{{ $app->version }}</p>
                                <p class="text-sm text-stone-600 mt-2 line-clamp-2">{{ Str::limit($app->description, 100) }}</p>
                                <div class="flex items-center gap-2 mt-3">
                                    <span class="text-sm font-bold text-indigo-600">{{ $app->priceLabel() }}</span>
                                    <span class="text-xs text-stone-400">&middot; {{ number_format($app->total_installs) }} install</span>
                                    <span class="text-xs text-stone-400">&middot; {{ number_format($app->rating, 1) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-16 text-center text-stone-400">
                        <p class="text-lg">Tidak ada aplikasi ditemukan.</p>
                        <p class="text-sm mt-1">Coba ganti kategori atau kata kunci pencarian.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- Installed Tab --}}
        @if ($activeTab === 'installed')
            @forelse ($installedApps as $install)
                <div class="bg-white rounded-xl border border-stone-200 p-5 mb-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-stone-900">{{ $install->app->name ?? 'App #' . $install->marketplace_app_id }}</h3>
                            <p class="text-sm text-stone-500">v{{ $install->installed_version }}
                                @if ($install->app && version_compare($install->app->version, $install->installed_version, '>'))
                                    <span class="text-amber-600 font-semibold ml-2">Update tersedia: v{{ $install->app->version }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-stone-400 mt-1">Status: {{ $install->status }}</p>
                        </div>
                        <div class="flex gap-2">
                            @if ($install->needsUpdate())
                                <button wire:click="updateApp({{ $install->marketplace_app_id }})"
                                    class="px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-sm font-semibold hover:bg-amber-100 transition">
                                    Update
                                </button>
                            @endif
                            <button wire:click="uninstallApp({{ $install->marketplace_app_id }})"
                                class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-100 transition">
                                Uninstall
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-stone-400">
                    <p class="text-lg">Belum ada aplikasi terinstall.</p>
                    <p class="text-sm mt-1">Jelajahi App Store untuk menemukan aplikasi yang cocok.</p>
                </div>
            @endforelse
        @endif

        {{-- Featured Tab --}}
        @if ($activeTab === 'featured')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($featuredApps as $app)
                    <div wire:click="browseApp({{ $app->id }})"
                        class="bg-gradient-to-br from-indigo-50 to-violet-50 rounded-xl border border-indigo-100 p-6 hover:shadow-lg hover:border-indigo-300 transition cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                {{ strtoupper(substr($app->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-stone-900 text-lg">{{ $app->name }}</h3>
                                    <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-semibold">Featured</span>
                                </div>
                                <p class="text-sm text-stone-500">{{ $app->developer }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-stone-600 mt-4 line-clamp-2">{{ $app->description }}</p>
                        <p class="text-lg font-bold text-indigo-600 mt-3">{{ $app->priceLabel() }}</p>
                    </div>
                @empty
                    <div class="col-span-2 py-16 text-center text-stone-400">
                        <p class="text-lg">Tidak ada aplikasi unggulan saat ini.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</x-filament-panels::page>

<div class="min-h-screen grid lg:grid-cols-2">
    <div class="login-left-panel relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-900 p-10 xl:p-14 flex flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.4) 0%, transparent 50%), radial-gradient(circle at 80% 70%, rgba(255,255,255,0.3) 0%, transparent 50%);"></div>

        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-violet-500/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-80 h-80 rounded-full bg-indigo-400/20 blur-3xl"></div>

        <div class="absolute -bottom-16 -right-16 text-[16rem] opacity-[0.06] animate-float-slow select-none" style="animation-delay: 0s;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1" width="1em" height="1em">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
        </div>

        <div class="relative">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-white group">
                <span class="text-3xl transition-transform group-hover:scale-110">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="36" height="36">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </span>
                <span class="font-display font-bold text-2xl tracking-tight">{{ config('app.name', 'BizOS') }}</span>
            </a>
        </div>

        <div class="relative text-white max-w-lg">
            <h2 class="font-display text-4xl xl:text-5xl font-bold leading-tight mb-4 animate-fade-slide" style="animation-delay: 0.15s;">
                Sistem Operasi Bisnis All-in-One
            </h2>
            <p class="text-indigo-200 text-lg leading-relaxed mb-10 max-w-md animate-fade-slide" style="animation-delay: 0.3s;">
                Kelola HRM, Finance, CRM, Project Management, POS, dan 30+ modul bisnis dalam satu dashboard terpadu.
            </p>

            <div class="grid grid-cols-3 gap-3 max-w-md animate-fade-slide" style="animation-delay: 0.45s;">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10 hover:bg-white/15 transition-colors">
                    <div class="text-2xl mb-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mx-auto"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="text-xs font-semibold text-indigo-200">HRM</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10 hover:bg-white/15 transition-colors">
                    <div class="text-2xl mb-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mx-auto"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div class="text-xs font-semibold text-indigo-200">Finance</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10 hover:bg-white/15 transition-colors">
                    <div class="text-2xl mb-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mx-auto"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </div>
                    <div class="text-xs font-semibold text-indigo-200">CRM & Sales</div>
                </div>
            </div>
        </div>

        <div class="relative text-indigo-300/60 text-xs">
            &copy; {{ date('Y') }} {{ config('app.name', 'BizOS') }} &middot; Powered by Laravel
        </div>
    </div>

    <div class="login-right-panel flex items-center justify-center p-8 lg:p-14 xl:p-20">
        <div class="w-full max-w-md animate-fade-slide" style="animation-delay: 0.15s;">
            <h1 class="font-display text-4xl font-bold text-stone-900 mb-1.5">Masuk ke BizOS</h1>
            <p class="text-stone-500 mb-8">
                Belum punya akun? <span class="text-stone-400">Hubungi admin perusahaan Anda</span>
            </p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-red-500 shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <div>
                            <div class="text-sm font-semibold text-red-800">Login gagal</div>
                            <p class="text-sm text-red-600 mt-0.5">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit="authenticate" class="space-y-5">
                <div>
                    <label for="fi-login-email" class="block text-sm font-semibold text-stone-700 mb-1.5">Email</label>
                    <input
                        type="email"
                        id="fi-login-email"
                        wire:model="data.email"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm text-stone-900 placeholder:text-stone-400 focus:border-indigo-500 focus:ring-[3px] focus:ring-indigo-500/15 outline-none transition-shadow"
                        placeholder="nama@perusahaan.com"
                        required
                        autofocus
                        autocomplete="email"
                    >
                    @error('data.email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fi-login-password" class="block text-sm font-semibold text-stone-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            id="fi-login-password"
                            wire:model="data.password"
                            class="w-full rounded-xl border border-stone-300 pl-4 pr-12 py-3 text-sm text-stone-900 placeholder:text-stone-400 focus:border-indigo-500 focus:ring-[3px] focus:ring-indigo-500/15 outline-none transition-shadow"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button
                            type="button"
                            onclick="(function(){var p=document.getElementById('fi-login-password');var i=this.querySelector('svg');if(p.type==='password'){p.type='text';i.innerHTML='<path d=\"M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z\"/><circle cx=\"12\" cy=\"12\" r=\"3\"/><line x1=\"1\" y1=\"1\" x2=\"23\" y2=\"23\"/>'}else{p.type='password';i.innerHTML='<path d=\"M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z\"/><circle cx=\"12\" cy=\"12\" r=\"3\"/>'}}).call(this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors p-1"
                            title="Tampilkan password"
                            tabindex="-1"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('data.password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" wire:model="data.remember" class="rounded border-stone-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span class="text-sm text-stone-600">Ingat saya</span>
                    </label>
                    @if(filament()->hasPasswordReset())
                    <a href="{{ filament()->getRequestPasswordResetUrl() }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
                        Lupa password?
                    </a>
                    @endif
                </div>

                <button type="submit" class="login-form-heading w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white py-3 px-6 text-sm font-semibold shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-violet-700 hover:shadow-xl hover:shadow-indigo-500/30 hover:-translate-y-px active:translate-y-0 transition-all duration-200">
                    Masuk
                </button>
            </form>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-stone-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-4 text-stone-400 font-medium">atau</span>
                </div>
            </div>

            <div class="bg-stone-50 border border-stone-200 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-indigo-600"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span class="font-semibold text-stone-800 text-sm">Akun Demo</span>
                </div>
                <div class="space-y-1.5 text-stone-600 text-xs font-mono leading-relaxed">
                    <div class="flex items-center justify-between py-1 border-b border-stone-100">
                        <span class="font-bold text-stone-700">Super Admin</span>
                        <span class="text-stone-500">admin@bizos.test / password</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-stone-100">
                        <span class="font-bold text-stone-700">HR Manager</span>
                        <span class="text-stone-500">hr@bizos.test / password</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-stone-100">
                        <span class="font-bold text-stone-700">Finance</span>
                        <span class="text-stone-500">finance@bizos.test / password</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-stone-100">
                        <span class="font-bold text-stone-700">Manager</span>
                        <span class="text-stone-500">manager@bizos.test / password</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-stone-100">
                        <span class="font-bold text-stone-700">Kasir</span>
                        <span class="text-stone-500">kasir@bizos.test / password</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="font-bold text-stone-700">Staff</span>
                        <span class="text-stone-500">staff@bizos.test / password</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-400 hover:text-stone-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Kembali ke beranda
                </a>
            </div>
        </div>
    </div>
</div>

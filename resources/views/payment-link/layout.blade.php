<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pembayaran Faktur')</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet"/>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif'],mono:['JetBrains Mono','monospace']}}}};</script>
    @yield('head')
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen flex flex-col">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center text-white font-bold">
                    @yield('brand-initial', 'B')
                </div>
                <div class="leading-tight">
                    <div class="font-bold text-slate-900">@yield('brand-name', config('app.name', 'BizOS'))</div>
                    <div class="text-xs text-slate-400">Pembayaran Faktur Online</div>
                </div>
            </div>
            <div class="text-xs text-slate-400 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Aman &amp; Terenkripsi
            </div>
        </div>
    </header>

    <main class="flex-1 w-full max-w-3xl mx-auto px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-5">
            <p class="text-center text-xs text-slate-400">
                Butuh bantuan? Hubungi @yield('brand-name', config('app.name', 'BizOS')) melalui kontak yang tertera pada faktur.
            </p>
        </div>
    </footer>
</body>
</html>

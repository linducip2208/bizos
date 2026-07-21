<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Pelanggan') - BizOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                },
            },
        };
    </script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span class="text-lg font-bold text-gray-900">Portal Pelanggan</span>
                </div>
                @auth
                <div class="flex items-center space-x-4">
                    <a href="{{ route('portal.dashboard') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition">Dashboard</a>
                    <a href="{{ route('portal.invoices') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition">Invoice</a>
                    <a href="{{ route('portal.tickets.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition">Tiket</a>
                    <span class="text-sm text-gray-400">{{ Auth::user()->email }}</span>
                    <form action="{{ route('portal.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700 transition">Keluar</button>
                    </form>
                </div>
                @endauth
                @guest
                <div class="flex items-center">
                    <a href="{{ route('portal.login') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Masuk</a>
                </div>
                @endguest
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-sm text-gray-400">&copy; {{ date('Y') }} BizOS. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

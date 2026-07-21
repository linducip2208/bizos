<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Klien') - BizOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet"/>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif'],mono:['JetBrains Mono','monospace']}}}};</script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="flex justify-between h-16">
            <div class="flex items-center space-x-3"><svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg><span class="text-lg font-bold text-gray-900">Portal Klien</span></div>
            @auth('client')
            <div class="flex items-center space-x-4"><a href="{{ route('client.dashboard') }}" class="text-sm text-gray-600 hover:text-blue-600 transition">Dashboard</a><a href="{{ route('client.invoices') }}" class="text-sm text-gray-600 hover:text-blue-600 transition">Invoice</a><a href="{{ route('client.deals') }}" class="text-sm text-gray-600 hover:text-blue-600 transition">Deal</a><a href="{{ route('client.tickets') }}" class="text-sm text-gray-600 hover:text-blue-600 transition">Tiket</a><span class="text-sm text-gray-400 hidden sm:inline">{{ Auth::guard('client')->user()->name }}</span><form action="{{ route('client.logout') }}" method="POST" class="inline">@csrf<button class="text-sm text-red-500 hover:text-red-700">Keluar</button></form></div>
            @else
            <div class="flex items-center gap-3"><a href="{{ route('client.register') }}" class="text-sm text-blue-600 hover:text-blue-800">Daftar</a><a href="{{ route('client.login') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Masuk</a></div>
            @endauth
        </div></div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">@yield('content')</main>
    <footer class="bg-white border-t border-gray-200 mt-auto"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"><p class="text-center text-sm text-gray-400">&copy; {{ date('Y') }} BizOS</p></div></footer>
</body>
</html>

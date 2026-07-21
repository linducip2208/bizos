<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Supplier') - BizOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet"/>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif'],mono:['JetBrains Mono','monospace']}}}};</script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    <span class="text-lg font-bold text-gray-900">Portal Supplier</span>
                </div>
                @auth('supplier')
                <div class="flex items-center space-x-4">
                    <a href="{{ route('supplier.dashboard') }}" class="text-sm text-gray-600 hover:text-teal-600 transition">Dashboard</a>
                    <a href="{{ route('supplier.po.index') }}" class="text-sm text-gray-600 hover:text-teal-600 transition">Purchase Order</a>
                    <span class="text-sm text-gray-400 hidden sm:inline">{{ Auth::guard('supplier')->user()->name }}</span>
                    <form action="{{ route('supplier.logout') }}" method="POST" class="inline">@csrf<button class="text-sm text-red-500 hover:text-red-700">Keluar</button></form>
                </div>
                @else
                <div class="flex items-center"><a href="{{ route('supplier.login') }}" class="text-sm text-teal-600 hover:text-teal-800 font-medium">Masuk</a></div>
                @endauth
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">@yield('content')</main>
    <footer class="bg-white border-t border-gray-200 mt-auto"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"><p class="text-center text-sm text-gray-400">&copy; {{ date('Y') }} BizOS</p></div></footer>
</body>
</html>

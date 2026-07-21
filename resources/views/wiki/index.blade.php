<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiki - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/wiki" class="font-bold text-xl text-indigo-700">{{ config('app.name') }} Wiki</a>
            <a href="/admin" class="text-sm text-gray-500 hover:text-indigo-600 transition">Kembali ke Admin</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
        <aside class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-4 sticky top-4">
                <h3 class="font-semibold text-sm text-gray-500 uppercase tracking-wider mb-3">Kategori</h3>
                <ul class="space-y-1">
                    @foreach ($tree as $cat)
                        <li class="text-sm">
                            <a href="/wiki" class="block px-3 py-1.5 rounded-lg hover:bg-gray-100 transition font-medium text-gray-700">{{ $cat['name'] }}</a>
                            @if (!empty($cat['children']))
                                <ul class="ml-3 mt-1 space-y-0.5">
                                    @foreach ($cat['children'] as $child)
                                        <li><a href="/wiki" class="block px-3 py-1 rounded-lg hover:bg-gray-100 text-xs text-gray-500 transition">{{ $child['name'] }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <h3 class="font-semibold text-sm text-gray-500 uppercase tracking-wider mb-3 mt-6">Populer</h3>
                <ul class="space-y-1">
                    @foreach ($popular as $pop)
                        <li><a href="/wiki/{{ $pop->slug }}" class="block px-3 py-1.5 rounded-lg hover:bg-gray-100 text-sm text-gray-600 transition truncate">{{ $pop->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <div class="lg:col-span-3">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Semua Halaman Wiki</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($pages as $page)
                    <a href="/wiki/{{ $page->slug }}" class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition block">
                        <span class="text-xs text-indigo-600 font-medium">{{ $page->category?->name ?? 'Umum' }}</span>
                        <h2 class="font-semibold text-lg text-gray-900 mt-1">{{ $page->title }}</h2>
                        <p class="text-sm text-gray-500 mt-2">{{ Str::limit(strip_tags($page->content), 100) }}</p>
                        <div class="text-xs text-gray-400 mt-3">{{ $page->published_at?->format('d M Y') }} &middot; {{ $page->view_count }} dilihat</div>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $pages->links() }}
            </div>
        </div>
    </main>
</body>
</html>

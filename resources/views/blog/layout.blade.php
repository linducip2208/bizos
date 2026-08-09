<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoMeta['title'] ?? 'Blog — BizOS' }}</title>
    <meta name="description" content="{{ $seoMeta['description'] ?? '' }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] ?? url()->current() }}">
    <meta property="og:type" content="{{ isset($post) ? 'article' : 'website' }}">
    <meta property="og:title" content="{{ $seoMeta['og_title'] ?? $seoMeta['title'] ?? '' }}">
    <meta property="og:description" content="{{ $seoMeta['og_description'] ?? $seoMeta['description'] ?? '' }}">
    <meta property="og:url" content="{{ $seoMeta['canonical'] ?? url()->current() }}">
    @if(!empty($seoMeta['og_image']))
    <meta property="og:image" content="{{ $seoMeta['og_image'] }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoMeta['og_title'] ?? $seoMeta['title'] ?? '' }}">
    <meta name="twitter:description" content="{{ $seoMeta['og_description'] ?? $seoMeta['description'] ?? '' }}">
    @if(!empty($seoMeta['jsonld']))
    <script type="application/ld+json">{!! json_encode($seoMeta['jsonld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        pre, code, .font-mono { font-family: 'JetBrains Mono', monospace; }
        html { scroll-behavior: smooth; }
        .blog-content h2 { font-size: 1.5rem; font-weight: 700; margin-top: 2rem; margin-bottom: 0.75rem; color: #1e293b; }
        .blog-content h3 { font-size: 1.25rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.5rem; color: #334155; }
        .blog-content p { margin-bottom: 1rem; line-height: 1.8; color: #475569; }
        .blog-content ul, .blog-content ol { margin-bottom: 1rem; padding-left: 1.5rem; color: #475569; }
        .blog-content li { margin-bottom: 0.25rem; line-height: 1.7; }
        .blog-content a { color: #4f46e5; text-decoration: underline; }
        .blog-content img { border-radius: 12px; max-width: 100%; height: auto; margin: 1.5rem 0; }
        .blog-content blockquote { border-left: 4px solid #6366f1; padding-left: 1rem; margin: 1.5rem 0; color: #64748b; font-style: italic; }
        .blog-content pre { background: #1e293b; color: #e2e8f0; padding: 1.25rem; border-radius: 10px; overflow-x: auto; margin: 1rem 0; }
        .blog-content code { font-size: 0.875rem; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

<header class="border-b border-slate-200 bg-white/80 backdrop-blur-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 font-bold text-slate-800 text-lg no-underline">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-7 h-7 text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>BizOS</span>
                </a>
                <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-indigo-600 no-underline">Blog</a>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('/docs') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 no-underline transition-colors">Dokumentasi</a>
                <a href="{{ url('/admin/login') }}" class="text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 no-underline px-4 py-2 rounded-lg transition-colors">
                    Login Admin
                </a>
            </div>
        </div>
    </div>
</header>

<main class="flex-1">
    @yield('content')
</main>

<footer class="bg-slate-900 text-slate-400 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Produk</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/docs') }}" class="hover:text-white transition-colors no-underline">Dokumentasi</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors no-underline">Blog</a></li>
                    <li><a href="{{ url('/best-hrm-software') }}" class="hover:text-white transition-colors no-underline">Fitur HRM</a></li>
                    <li><a href="{{ url('/best-accounting-software-indonesia') }}" class="hover:text-white transition-colors no-underline">Fitur Akuntansi</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Akses</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/admin/login') }}" class="hover:text-white transition-colors no-underline">Admin Login</a></li>
                    <li><a href="{{ url('/sitemap.xml') }}" class="hover:text-white transition-colors no-underline">Sitemap</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Perusahaan</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition-colors no-underline">Beranda</a></li>
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Kontak</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 mt-10 pt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} BizOS — Business Operating System. Seluruh hak cipta dilindungi.
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>

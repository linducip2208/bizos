<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $seoMeta = $seo ?? $seoMeta ?? []; @endphp
    <title>{{ $seoMeta['title'] ?? 'BizOS — Business Operating System' }}</title>
    <meta name="description" content="{{ $seoMeta['description'] ?? '' }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] ?? url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seoMeta['og_title'] ?? $seoMeta['title'] ?? '' }}">
    <meta property="og:description" content="{{ $seoMeta['og_description'] ?? $seoMeta['description'] ?? '' }}">
    <meta property="og:url" content="{{ $seoMeta['og_url'] ?? url()->current() }}">
    @if(!empty($seoMeta['og_image']))
    <meta property="og:image" content="{{ $seoMeta['og_image'] }}">
    @endif
    <meta name="twitter:card" content="{{ $seoMeta['twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seoMeta['og_title'] ?? $seoMeta['title'] ?? '' }}">
    <meta name="twitter:description" content="{{ $seoMeta['og_description'] ?? $seoMeta['description'] ?? '' }}">
    @if(!empty($seoMeta['og_image']))
    <meta name="twitter:image" content="{{ $seoMeta['og_image'] }}">
    @endif
    @if(!empty($seoMeta['jsonld']))
    <script type="application/ld+json">{!! json_encode($seoMeta['jsonld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        pre, code, .font-mono { font-family: 'JetBrains Mono', monospace; }
        .browser-mock {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 1px 3px rgba(0,0,0,0.08);
            background: #1e1e2e;
        }
        .browser-mock-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #1e1e2e;
        }
        .browser-mock-dot { width: 12px; height: 12px; border-radius: 50%; }
        .browser-mock-url {
            flex: 1;
            background: #2d2d3f;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 12px;
            color: #94a3b8;
            font-family: 'JetBrains Mono', monospace;
        }
        .browser-mock-body { background: #ffffff; }
        .browser-mock-body img { display: block; width: 100%; }
        html { scroll-behavior: smooth; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>

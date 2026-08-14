<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Payment Gateway — BizOS</title>
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
<body class="bg-gray-50 font-sans text-gray-900 antialiased min-h-screen">
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span class="text-sm font-semibold text-gray-700">BizOS · Uji Payment Gateway</span>
                </div>
                <a href="{{ url('/admin') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Kembali ke Admin</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Uji Koneksi Payment Gateway</h1>
            <p class="text-gray-500 mt-1">Uji koneksi gateway yang sudah dikonfigurasi di menu <span class="font-medium">🔗 Integrations → Payment Gateway</span>.</p>
        </div>

        @if (isset($results))
            <div class="mb-8 rounded-xl border p-5 @if(!empty($results['success'])) bg-emerald-50 border-emerald-200 @else bg-red-50 border-red-200 @endif">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-lg">@if(!empty($results['success'])) ✅ @else ❌ @endif</span>
                    <span class="font-semibold @if(!empty($results['success'])) text-emerald-700 @else text-red-700 @endif">
                        {{ $results['name'] ?? ($results['gateway'] ?? 'Gateway') }}
                    </span>
                </div>
                <p class="text-sm @if(!empty($results['success'])) text-emerald-700 @else text-red-700 @endif">{{ $results['message'] ?? 'Tanpa pesan' }}</p>
                @if(isset($results['http_status']))
                    <p class="text-xs text-gray-500 mt-1">HTTP Status: {{ $results['http_status'] }}</p>
                @endif
            </div>
        @endif

        @if(empty($gateways))
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-500">
                <div class="text-4xl mb-3">🔌</div>
                <p class="font-medium text-gray-700">Belum ada gateway yang dikonfigurasi.</p>
                <p class="text-sm mt-1">Tambahkan gateway lewat menu <span class="font-medium">🔗 Integrations → Payment Gateway</span>.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($gateways as $gateway)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900">{{ $gateway['name'] }}</h3>
                                    <span class="text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">{{ $gateway['gateway_type'] }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $gateway['base_url'] ?? 'URL bawaan gateway' }}</p>
                            </div>
                            <span class="text-xs font-medium @if(!empty($gateway['is_configured'])) text-emerald-600 @else text-amber-600 @endif">
                                @if(!empty($gateway['is_configured'])) ● Terkonfigurasi @else ● Perlu kredensial @endif
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1">
                            @foreach(array_slice($gateway['methods'] ?? [], 0, 6) as $m)
                                <span class="text-[11px] px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ $m['label'] }}</span>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('payment-gateway.test.run', $gateway['gateway_type']) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50" @if(empty($gateway['is_configured'])) disabled @endif>
                                Uji Koneksi
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 bg-white rounded-xl border border-gray-200 p-5">
                <h4 class="font-semibold text-gray-800 mb-2">🔔 URL Webhook</h4>
                <p class="text-sm text-gray-500">Daftarkan URL berikut di dashboard gateway masing-masing agar status pembayaran ter-update otomatis:</p>
                <ul class="mt-3 space-y-2 text-sm font-mono text-gray-700">
                    @foreach(['midtrans', 'xendit', 'stripe'] as $g)
                        <li class="bg-gray-50 rounded px-3 py-2">{{ url('/webhooks/payment/' . $g) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </main>
</body>
</html>

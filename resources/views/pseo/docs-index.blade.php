@extends('pseo._layout')

@section('content')

<header class="border-b border-slate-200 bg-white/80 backdrop-blur-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 font-bold text-slate-800 text-lg no-underline">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-7 h-7 text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span>BizOS</span>
            </a>
            <a href="{{ url('/admin/login') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 no-underline px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                Login Admin
            </a>
        </div>
    </div>
</header>

<nav class="sticky top-16 z-40 bg-white border-b border-slate-200 overflow-x-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-1 py-2 text-sm font-medium whitespace-nowrap">
            <a href="#demo-accounts" class="px-3 py-1.5 rounded-md hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors no-underline">Akun Demo</a>
            <a href="#struktur-menu" class="px-3 py-1.5 rounded-md hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors no-underline">Struktur Menu</a>
            <a href="#tutorial" class="px-3 py-1.5 rounded-md hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors no-underline">Tutorial</a>
            <a href="#fitur" class="px-3 py-1.5 rounded-md hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors no-underline">Fitur</a>
            @foreach($features as $f)
            <a href="#fitur-{{ \Illuminate\Support\Str::slug($f['group']) }}" class="px-2.5 py-1.5 rounded-md hover:bg-indigo-50 text-slate-500 hover:text-indigo-700 transition-colors no-underline text-xs">{{ $f['group'] }}</a>
            @endforeach
            <a href="#cta" class="px-3 py-1.5 rounded-md bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors no-underline">Mulai</a>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-20">

    {{-- Page Header --}}
    <section class="text-center max-w-3xl mx-auto">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight mb-4">Dokumentasi BizOS</h1>
        <p class="text-lg text-slate-600 leading-relaxed">Panduan lengkap menggunakan BizOS: tutorial langkah demi langkah, demo account, struktur menu, dan penjelasan setiap fitur. 150+ fitur dalam satu platform.</p>
    </section>

    {{-- Demo Accounts --}}
    <section id="demo-accounts">
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900">Akun Demo</h2>
            <p class="text-slate-500 mt-1">Gunakan akun berikut untuk mencoba BizOS sesuai role Anda.</p>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 text-left">
                        <th class="px-5 py-3.5 font-semibold text-slate-700 uppercase text-xs tracking-wider">Role</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-700 uppercase text-xs tracking-wider">Email</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-700 uppercase text-xs tracking-wider">Password</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-700 uppercase text-xs tracking-wider">Cakupan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($demoAccounts as $account)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ $account['role'] }}</td>
                        <td class="px-5 py-3"><code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">{{ $account['email'] }}</code></td>
                        <td class="px-5 py-3"><code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">{{ $account['password'] }}</code></td>
                        <td class="px-5 py-3 text-slate-600 text-xs leading-relaxed max-w-xs">{{ $account['scope'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-center">
            <a href="{{ url('/admin/login') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200 no-underline text-sm">
                Login ke Admin Panel
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" clip-rule="evenodd"/></svg>
            </a>
        </div>
    </section>

    {{-- Struktur Menu --}}
    <section id="struktur-menu">
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900">Struktur Menu Admin</h2>
            <p class="text-slate-500 mt-1">Semua navigation group dan resource yang tersedia di BizOS.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($menuStructure as $group => $items)
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
                <h3 class="font-bold text-slate-800 mb-3 text-sm uppercase tracking-wider text-indigo-600">{{ $group }}</h3>
                <ul class="space-y-1.5">
                    @foreach($items as $item)
                    <li class="text-sm text-slate-600 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 flex-shrink-0"></span>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Tutorial Step by Step --}}
    <section id="tutorial">
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900">Tutorial Langkah demi Langkah</h2>
            <p class="text-slate-500 mt-1">Ikuti 38 langkah berikut untuk setup dan mengoperasikan BizOS dari awal hingga mahir — dari setup perusahaan hingga laporan.</p>
        </div>
        <div class="space-y-12">
            @foreach($tutorial as $fase => $steps)
            <div>
                <h3 class="text-xl font-bold text-indigo-700 mb-5 pb-2 border-b-2 border-indigo-100">{{ $fase }}</h3>
                <div class="space-y-4">
                    @foreach($steps as $step)
                    <div class="flex gap-4 bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm">{{ $step['step'] }}</div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-1">{{ $step['title'] }}</h4>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Fitur Lengkap --}}
    <section id="fitur">
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900">Fitur Lengkap</h2>
            <p class="text-slate-500 mt-1">Semua 122 menu BizOS — setiap resource dengan screenshot dan deskripsi.</p>
        </div>

        @php
            $groupedFeatures = [];
            foreach ($features as $f) {
                $groupedFeatures[$f['group']][] = $f;
            }
            $groupOrder = ['Login & Dashboard', 'Master Data'];
        @endphp

        <div class="space-y-14">
            @foreach($groupedFeatures as $group => $items)
            <div id="fitur-{{ \Illuminate\Support\Str::slug($group) }}">
                <h3 class="text-xl font-bold text-indigo-700 mb-5 pb-2 border-b-2 border-indigo-100 sticky top-[calc(4rem+4rem)] bg-white/95 backdrop-blur-sm z-30 py-2">{{ $group }} <span class="text-sm font-normal text-slate-400">({{ count($items) }} menu)</span></h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($items as $f)
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 flex items-center gap-2 border-b border-gray-200 dark:border-gray-600">
                            <div class="flex gap-1.5 flex-shrink-0">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="flex-1 min-w-0 mx-auto bg-white dark:bg-gray-600 rounded-full px-3 py-0.5 text-[10px] text-gray-400 dark:text-gray-300 font-mono text-center truncate">{{ \Illuminate\Support\Str::slug($f['title']) }}.bizos.id</div>
                        </div>
                        <div class="aspect-[16/10] bg-gray-50 dark:bg-gray-900 overflow-hidden">
                            <img src="{{ url('/marketing/screens/' . $f['screenshot']) }}" alt="{{ $f['title'] }} - BizOS" class="w-full h-full object-contain">
                        </div>
                        <div class="p-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $f['title'] }}</h4>
                            <p class="text-gray-500 dark:text-gray-400 text-xs mt-1 leading-relaxed">{{ $f['description'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section id="cta" class="bg-gradient-to-br from-indigo-600 via-purple-600 to-violet-700 rounded-2xl p-10 sm:p-14 text-center text-white shadow-xl shadow-indigo-200/50">
        <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">Siap Mencoba BizOS?</h2>
        <p class="text-indigo-100 text-lg mb-8 max-w-xl mx-auto">Gunakan akun demo di atas untuk eksplorasi semua fitur. Tidak perlu install — langsung dari browser.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/admin/login') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-white text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors shadow-lg no-underline">
                Login Admin Panel
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" clip-rule="evenodd"/></svg>
            </a>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 transition-colors no-underline">
                Kembali ke Beranda
            </a>
        </div>
    </section>

</main>

@include('pseo._footer')

@endsection

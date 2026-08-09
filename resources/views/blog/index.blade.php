@extends('blog.layout')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight mb-3">
            {{ isset($category) ? 'Kategori: ' . $category->name : 'Blog BizOS' }}
        </h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
            {{ isset($category) ? ($category->description ?: 'Artikel dalam kategori ' . $category->name) : 'Tips bisnis, HRM, akuntansi, CRM, dan teknologi untuk bisnis Indonesia.' }}
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1">
            <form action="{{ route('blog.index') }}" method="GET" class="mb-8">
                <div class="relative max-w-md">
                    <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Cari artikel..."
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none text-sm transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </form>

            @if($posts->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                <article class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col">
                    @if($post->featured_image)
                    <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/9] overflow-hidden bg-slate-100">
                        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                    </a>
                    @else
                    <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/9] bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="w-12 h-12 text-white/60"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </a>
                    @endif
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-center gap-2 mb-2">
                            @if($post->category)
                            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 no-underline hover:bg-indigo-200 transition-colors">
                                {{ $post->category->name }}
                            </a>
                            @endif
                        </div>
                        <a href="{{ route('blog.show', $post->slug) }}" class="no-underline group">
                            <h2 class="font-bold text-slate-900 text-lg leading-snug mb-2 group-hover:text-indigo-600 transition-colors">
                                {{ $post->title }}
                            </h2>
                        </a>
                        @if($post->excerpt)
                        <p class="text-sm text-slate-600 leading-relaxed mb-3 flex-1">
                            {{ Str::limit($post->excerpt, 120) }}
                        </p>
                        @endif
                        <div class="flex items-center justify-between text-xs text-slate-500 mt-auto pt-3 border-t border-slate-100">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z"/></svg>
                                {{ $post->author?->name ?? 'BizOS' }}
                            </span>
                            <span>{{ $post->published_at?->format('d M Y') }}</span>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
            @else
            <div class="text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="w-16 h-16 text-slate-300 mx-auto mb-4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <h3 class="text-lg font-semibold text-slate-500 mb-2">Tidak ada artikel ditemukan</h3>
                <p class="text-slate-400 text-sm">Coba kata kunci lain atau kembali ke <a href="{{ route('blog.index') }}" class="text-indigo-600 no-underline">daftar blog</a>.</p>
            </div>
            @endif
        </div>

        <aside class="w-full lg:w-72 flex-shrink-0">
            <div class="sticky top-24 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="font-bold text-slate-800 mb-3 text-sm uppercase tracking-wider">Kategori</h3>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('blog.index') }}" class="flex items-center justify-between text-sm py-1.5 px-2 rounded-lg no-underline {{ !isset($category) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }} transition-colors">
                                Semua
                            </a>
                        </li>
                        @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('blog.category', $cat->slug) }}" class="flex items-center justify-between text-sm py-1.5 px-2 rounded-lg no-underline {{ isset($category) && $category->id === $cat->id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }} transition-colors">
                                {{ $cat->name }}
                                <span class="text-xs text-slate-400 ml-2">{{ $cat->posts_count }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl p-5 text-white">
                    <h3 class="font-bold mb-2 text-sm">Butuh Software Bisnis?</h3>
                    <p class="text-indigo-100 text-xs leading-relaxed mb-4">BizOS: HRM, Akuntansi, CRM, Project, POS, LMS — all-in-one untuk bisnis Indonesia.</p>
                    <a href="{{ url('/') }}" class="inline-block text-xs font-semibold bg-white text-indigo-700 px-4 py-2 rounded-lg no-underline hover:bg-indigo-50 transition-colors">Lihat Demo</a>
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
    {{-- Pagination styles --}}
    .fi-pagination { display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .fi-pagination-item { }
    .fi-pagination-item a, .fi-pagination-item span {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 2.5rem; height: 2.5rem; border-radius: 0.5rem;
        font-size: 0.875rem; font-weight: 500; transition: all 0.15s;
    }
    .fi-pagination-item a {
        color: #475569; background: white; border: 1px solid #e2e8f0;
    }
    .fi-pagination-item a:hover { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
    .fi-pagination-item.active span { background: #4f46e5; color: white; border-color: #4f46e5; }
    .fi-pagination-item.disabled span { color: #cbd5e1; background: #f8fafc; border-color: #e2e8f0; cursor: not-allowed; }
</style>

@if(method_exists($posts, 'links'))
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
    <nav class="flex items-center justify-center gap-1 flex-wrap">
        @if ($posts->onFirstPage())
            <span class="flex items-center justify-center min-w-[2.5rem] h-10 rounded-lg text-sm font-medium text-slate-300 bg-slate-50 border border-slate-200 cursor-not-allowed">
                &laquo;
            </span>
        @else
            <a href="{{ $posts->previousPageUrl() }}" rel="prev" class="flex items-center justify-center min-w-[2.5rem] h-10 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition-colors no-underline">
                &laquo;
            </a>
        @endif

        @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
            @if ($page == $posts->currentPage())
                <span class="flex items-center justify-center min-w-[2.5rem] h-10 rounded-lg text-sm font-semibold bg-indigo-600 text-white border border-indigo-600">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $url }}" class="flex items-center justify-center min-w-[2.5rem] h-10 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition-colors no-underline">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        @if ($posts->hasMorePages())
            <a href="{{ $posts->nextPageUrl() }}" rel="next" class="flex items-center justify-center min-w-[2.5rem] h-10 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700 transition-colors no-underline">
                &raquo;
            </a>
        @else
            <span class="flex items-center justify-center min-w-[2.5rem] h-10 rounded-lg text-sm font-medium text-slate-300 bg-slate-50 border border-slate-200 cursor-not-allowed">
                &raquo;
            </span>
        @endif
    </nav>
</div>
@endif

@endsection

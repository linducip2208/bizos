<div class="p-6 space-y-4">
    <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
        <div class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Header</div>
        <div class="text-lg font-bold text-stone-900">
            {{ $template->company?->name ?? config('app.name') }}
        </div>
    </div>

    <div class="rounded-xl border border-stone-200 p-4">
        <div class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Body</div>
        <div class="text-sm text-stone-700 whitespace-pre-wrap leading-relaxed">
            {{ $template->content }}
        </div>
    </div>

    <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
        <div class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Footer</div>
        <div class="text-xs text-stone-400">
            Template: {{ $template->name }} · Kategori: {{ $template->category }} · Bahasa: {{ $template->language }}
        </div>
    </div>

    <div class="flex gap-2 text-xs text-stone-400">
        <span class="rounded-full px-2 py-0.5 bg-{{ $template->status_color }}-100 text-{{ $template->status_color }}-700 font-semibold">
            {{ $template->status_label }}
        </span>
        @if($template->quality_score)
            <span class="rounded-full px-2 py-0.5 bg-blue-100 text-blue-700 font-semibold">
                Skor: {{ $template->quality_score }}
            </span>
        @endif
    </div>
</div>

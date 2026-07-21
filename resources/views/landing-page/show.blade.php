<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page['meta_title'] ?? $page['title'] }}</title>
    <meta name="description" content="{{ $page['meta_description'] ?? '' }}">
    <meta property="og:title" content="{{ $page['meta_title'] ?? $page['title'] }}">
    <meta property="og:description" content="{{ $page['meta_description'] ?? '' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">
    <div class="max-w-4xl mx-auto px-4 py-16">
        <h1 class="text-4xl font-bold mb-4">{{ $page['title'] }}</h1>

        @if(!empty($page['meta_description']))
            <p class="text-lg text-gray-600 mb-8">{{ $page['meta_description'] }}</p>
        @endif

        <div class="prose max-w-none mb-12">
            @if(!empty($page['content']))
                @foreach($page['content'] as $section => $content)
                    <div class="mb-8">
                        @if(is_string($section) && !is_numeric($section))
                            <h2 class="text-2xl font-semibold mb-3">{{ $section }}</h2>
                        @endif
                        <div>{!! is_array($content) ? nl2br(e(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) : nl2br(e($content)) !!}</div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500 italic">Konten belum tersedia.</p>
            @endif
        </div>

        @if(!empty($form))
            <div class="border border-gray-200 rounded-xl p-6 bg-gray-50">
                <h2 class="text-xl font-semibold mb-4">{{ $form['name'] }}</h2>
                @if(!empty($form['description']))
                    <p class="text-gray-600 mb-4">{{ $form['description'] }}</p>
                @endif
                <form method="POST" action="{{ url('/api/forms/' . $form['id'] . '/submit') }}" class="space-y-4">
                    @csrf
                    @foreach($form['fields'] ?? [] as $field)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $field['label'] ?? $field['field_name'] }}
                                @if(($field['required'] ?? false))
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            @if(($field['field_type'] ?? 'text') === 'textarea')
                                <textarea name="fields[{{ $field['id'] }}]" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm"
                                    {{ ($field['required'] ?? false) ? 'required' : '' }}></textarea>
                            @elseif(($field['field_type'] ?? 'text') === 'select')
                                <select name="fields[{{ $field['id'] }}]" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm"
                                    {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                    <option value="">Pilih...</option>
                                    @foreach(json_decode($field['options'] ?? '[]', true) as $option)
                                        <option value="{{ $option['value'] ?? $option }}">{{ $option['label'] ?? $option }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $field['field_type'] ?? 'text' }}" name="fields[{{ $field['id'] }}]"
                                    class="w-full border border-gray-300 rounded-lg p-2.5 text-sm"
                                    {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            @endif
                        </div>
                    @endforeach
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">
                        Kirim
                    </button>
                </form>
            </div>
        @endif
    </div>
</body>
</html>

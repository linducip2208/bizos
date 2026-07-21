<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>No-Code Studio — {{ config('app.name', 'BizOS') }}</title>
    @livewireStyles
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        [x-draggable] { cursor: grab; }
        [x-draggable]:active { cursor: grabbing; }
    </style>
</head>
<body class="h-full antialiased bg-stone-50">
    {{ $slot }}
    @livewireScripts
    @stack('scripts')
</body>
</html>

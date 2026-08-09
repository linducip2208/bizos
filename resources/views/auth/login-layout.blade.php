<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — BizOS</title>
    <meta name="description" content="Masuk ke BizOS — Sistem Operasi Bisnis All-in-One. HRM, Finance, CRM, Project Management dalam satu platform.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Masuk — BizOS">
    <meta property="og:description" content="Sistem Operasi Bisnis All-in-One">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Masuk — BizOS">
    <meta name="twitter:description" content="Sistem Operasi Bisnis All-in-One">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                },
            },
        }
    </script>
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        pre, code, .font-mono { font-family: 'JetBrains Mono', monospace; }
        html { scroll-behavior: smooth; }

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        @keyframes fadeSlideUp {
            0% { transform: translateY(40px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .animate-float-slow { animation: floatSlow 5s ease-in-out infinite; }
        .animate-fade-slide { animation: fadeSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; }

        @media (max-width: 1023px) {
            .login-left-panel { display: none; }
            .login-right-panel { padding: 2rem 1.5rem; }
        }

        @media (max-width: 640px) {
            .login-right-panel { padding: 1.5rem 1rem; }
            .login-form-heading { font-size: 1.5rem; }
        }
    </style>
    @filamentStyles
</head>
<body class="antialiased bg-white">
    {{ $slot }}
    @filamentScripts
</body>
</html>

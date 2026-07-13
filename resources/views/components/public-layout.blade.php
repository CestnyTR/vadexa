@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'system';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <header class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ config('company.brand', 'Vadexa') }}</a>
            <nav class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                <a href="{{ route('legal.about') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Hakkımızda</a>
                <a href="{{ route('legal.contact') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">İletişim</a>
                <a href="{{ route('login') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Giriş</a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            @isset($title)
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ $title }}</h1>
            @endisset

            <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 space-y-4">
                {{ $slot }}
            </div>
        </div>
    </main>

    @include('layouts.footer')
</body>
</html>

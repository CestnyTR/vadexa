<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Theme (inline to prevent FOUC) -->
        <script>
            (function() {
                const theme = localStorage.getItem('theme') || 'system';
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/" class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ config('app.name', 'Vadexa') }}
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>

            <div class="w-full sm:max-w-md mt-6 flex flex-col items-center gap-3">
                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                    <a href="{{ route('legal.about') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Hakkımızda</a>
                    <a href="{{ route('legal.contact') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">İletişim</a>
                    <a href="{{ route('legal.privacy') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Gizlilik</a>
                    <a href="{{ route('legal.distance-sales') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Mesafeli Satış</a>
                    <a href="{{ route('legal.delivery-returns') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Teslimat & İade</a>
                </div>
                <x-payment-badges />
            </div>
        </div>
    </body>
</html>

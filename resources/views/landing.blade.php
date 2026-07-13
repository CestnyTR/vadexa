<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('company.brand', 'Vadexa') }} — Alacak Takip & Tahsilat Hatırlatma</title>
    <meta name="description" content="Vadexa; müşteri, vadeli alacak ve tahsilat süreçlerini tek panelden yönetmenizi, vadesi gelen alacaklar için otomatik SMS ve e-posta hatırlatması göndermenizi sağlar.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'system';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (theme === 'system' && prefersDark)) document.documentElement.classList.add('dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100">

    <!-- Nav -->
    <header class="sticky top-0 z-20 backdrop-blur bg-white/80 dark:bg-gray-950/80 border-b border-gray-100 dark:border-gray-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ config('company.brand', 'Vadexa') }}</span>
            <nav class="flex items-center gap-2 sm:gap-4 text-sm">
                <a href="#ozellikler" class="hidden sm:inline text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-2">Özellikler</a>
                <a href="#fiyatlar" class="hidden sm:inline text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-2">Fiyatlar</a>
                <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 px-3 py-2">Giriş</a>
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 transition">Ücretsiz Başla</a>
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-indigo-50 to-white dark:from-indigo-950/30 dark:to-gray-950"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16 text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-medium px-3 py-1 mb-6">
                {{ config('plans.trial_days') }} gün ücretsiz deneme · Kredi kartı gerekmez
            </span>
            <h1 class="text-4xl sm:text-5xl font-bold tracking-tight text-gray-900 dark:text-white leading-tight">
                Alacaklarınızı takip edin,<br class="hidden sm:block">
                tahsilatı <span class="text-indigo-600 dark:text-indigo-400">otomatiğe</span> alın
            </h1>
            <p class="mt-6 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                Vadexa; müşteri, vadeli alacak ve görevlerinizi tek panelden yönetir. Vadesi gelen
                alacaklar için otomatik <strong>SMS</strong> ve <strong>e-posta</strong> hatırlatmaları
                göndererek tahsilat oranınızı yükseltir.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 transition">
                    Hemen Başla
                </a>
                <a href="#fiyatlar" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-semibold px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    Fiyatları Gör
                </a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="ozellikler" class="py-20 border-t border-gray-100 dark:border-gray-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-900 dark:text-white">Tahsilat için ihtiyacınız olan her şey</h2>
            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ([
                    ['💼', 'Müşteri & Alacak', 'Müşterilerinizi, vadeli ve taksitli alacaklarınızı tek yerden yönetin.'],
                    ['🔔', 'Otomatik Hatırlatma', 'Vadesi gelen alacaklar için otomatik SMS ve e-posta bildirimleri.'],
                    ['🔁', 'Taksit & Tekrar', 'Taksitli ve tekrar eden alacakları kolayca planlayın ve takip edin.'],
                    ['📊', 'Raporlar', 'Tahsilat oranı ve alacak yaşlandırma raporlarıyla nakit akışınızı görün.'],
                ] as [$icon, $title, $desc])
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
                        <div class="text-3xl">{{ $icon }}</div>
                        <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="fiyatlar" class="py-20 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-900 dark:text-white">Basit, şeffaf fiyatlandırma</h2>
            <p class="mt-3 text-center text-gray-600 dark:text-gray-400">Her plan {{ config('plans.trial_days') }} gün ücretsiz deneme ile başlar.</p>

            <div class="mt-12 grid md:grid-cols-3 gap-6">
                @foreach (config('plans.plans') as $key => $plan)
                    <div class="rounded-2xl border bg-white dark:bg-gray-900 p-6 flex flex-col
                        {{ $key === 'pro' ? 'border-indigo-500 ring-1 ring-indigo-500 shadow-lg' : 'border-gray-200 dark:border-gray-800' }}">
                        @if ($key === 'pro')
                            <span class="self-start rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-medium px-3 py-1 mb-3">En popüler</span>
                        @endif
                        <p class="font-semibold text-lg text-gray-900 dark:text-white">{{ $plan['name'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $plan['price'] == 0 ? 'Ücretsiz' : number_format($plan['price'], 0, ',', '.').' ₺' }}
                            @if ($plan['price'] > 0)<span class="text-sm font-normal text-gray-500">/ay</span>@endif
                        </p>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 dark:text-gray-400 flex-1">
                            <li class="flex gap-2"><span class="text-indigo-500">✓</span> {{ $plan['limits']['customers'] ?? 'Sınırsız' }} müşteri</li>
                            <li class="flex gap-2"><span class="text-indigo-500">✓</span> {{ $plan['limits']['users'] ?? 'Sınırsız' }} kullanıcı</li>
                            <li class="flex gap-2"><span class="text-indigo-500">✓</span> Aylık {{ $plan['limits']['sms_per_month'] ?? 'sınırsız' }} SMS</li>
                            <li class="flex gap-2"><span class="text-indigo-500">✓</span> {{ ($plan['limits']['mail_per_month'] ?? null) ? 'Aylık '.$plan['limits']['mail_per_month'] : 'Sınırsız' }} e-posta</li>
                        </ul>
                        <a href="{{ route('register') }}" class="mt-6 inline-flex justify-center items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition
                            {{ $key === 'pro' ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                            Başla
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Tahsilatınızı bugün hızlandırın</h2>
            <p class="mt-4 text-gray-600 dark:text-gray-400">Dakikalar içinde kaydolun, ilk hatırlatmanızı bugün gönderin.</p>
            <a href="{{ route('register') }}" class="mt-8 inline-flex items-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 transition">
                Ücretsiz Hesap Oluştur
            </a>
        </div>
    </section>

    @include('layouts.footer')
</body>
</html>

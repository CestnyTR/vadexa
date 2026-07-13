<footer class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid gap-6 sm:grid-cols-3 text-sm">
            <div>
                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ config('company.brand', 'Vadexa') }}</p>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    {{ config('company.legal_name') }}
                </p>
            </div>
            <div>
                <p class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Kurumsal</p>
                <ul class="space-y-1 text-gray-500 dark:text-gray-400">
                    <li><a href="{{ route('legal.about') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Hakkımızda</a></li>
                    <li><a href="{{ route('legal.contact') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">İletişim</a></li>
                </ul>
            </div>
            <div>
                <p class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Yasal</p>
                <ul class="space-y-1 text-gray-500 dark:text-gray-400">
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Gizlilik Politikası</a></li>
                    <li><a href="{{ route('legal.distance-sales') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Mesafeli Satış Sözleşmesi</a></li>
                    <li><a href="{{ route('legal.delivery-returns') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Teslimat ve İade Şartları</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-400 dark:text-gray-500">© {{ date('Y') }} {{ config('company.legal_name') }}. Tüm hakları saklıdır.</p>
            <x-payment-badges />
        </div>
    </div>
</footer>

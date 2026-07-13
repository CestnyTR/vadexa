<x-public-layout title="Mesafeli Satış Sözleşmesi">
    <p class="text-xs text-gray-400">Son güncelleme: {{ date('d.m.Y') }}</p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">1. Taraflar</h2>
    <p>
        İşbu sözleşme; bir tarafta hizmet sağlayıcı <strong>{{ config('company.legal_name') }}</strong>
        (Adres: {{ config('company.address') ?: '—' }}, E-posta: {{ config('company.email') }})
        ile diğer tarafta {{ config('company.brand') }} platformuna üye olan ALICI arasında,
        aşağıdaki koşullarla akdedilmiştir.
    </p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">2. Sözleşmenin Konusu</h2>
    <p>
        Sözleşmenin konusu, ALICI'nın {{ config('company.brand') }} üzerinden elektronik ortamda
        seçtiği abonelik planına ilişkin dijital hizmetin sunulması ve bedelinin ödenmesidir.
    </p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">3. Hizmet ve Ücret</h2>
    <p>
        Abonelik planları ve güncel fiyatları platformun <strong>Plan</strong> sayfasında yer alır.
        Ücretler KDV dahil olarak, seçilen plana göre aylık peşin tahsil edilir.
    </p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">4. Ödeme</h2>
    <p>
        Ödemeler, PCI-DSS uyumlu ödeme kuruluşu <strong>iyzico</strong> altyapısı ile kredi/banka
        kartı üzerinden güvenli olarak (SSL şifreli) alınır.
    </p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">5. Cayma Hakkı</h2>
    <p>
        Elektronik ortamda anında ifa edilen dijital hizmetlerde, mevzuat gereği cayma hakkı
        istisnaları saklıdır. İade koşulları için
        <a href="{{ route('legal.delivery-returns') }}" class="text-indigo-600 dark:text-indigo-400">Teslimat ve İade Şartları</a>
        sayfasına bakınız.
    </p>

    <p class="text-xs text-gray-400">
        Bu metin bir şablondur; yayına almadan önce hukuki danışmanlık alınarak güncellenmelidir.
    </p>
</x-public-layout>

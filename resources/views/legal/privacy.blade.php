<x-public-layout title="Gizlilik Politikası">
    <p class="text-xs text-gray-400">Son güncelleme: {{ date('d.m.Y') }}</p>

    <p>
        {{ config('company.legal_name') }} ("{{ config('company.brand') }}", "biz") olarak, 6698 sayılı
        Kişisel Verilerin Korunması Kanunu ("KVKK") kapsamında kişisel verilerinizin gizliliğine önem
        veriyoruz. Bu politika, hizmetlerimizi kullanırken hangi verileri topladığımızı ve nasıl
        işlediğimizi açıklar.
    </p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Toplanan Veriler</h2>
    <p>Ad, soyad, e-posta, telefon, firma bilgileri ve hizmetin sunulması için gerekli işlem verileri.</p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Verilerin İşlenme Amacı</h2>
    <p>
        Üyelik oluşturma, hizmetin sunulması, faturalama ve ödeme süreçleri, yasal yükümlülüklerin
        yerine getirilmesi ve müşteri desteği amaçlarıyla işlenir.
    </p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ödeme Verileri</h2>
    <p>
        Ödeme işlemleri, PCI-DSS uyumlu ödeme kuruluşu <strong>iyzico</strong> altyapısı üzerinden
        gerçekleştirilir. Kart bilgileriniz tarafımızca saklanmaz.
    </p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Haklarınız</h2>
    <p>
        KVKK'nın 11. maddesi kapsamındaki haklarınızı kullanmak için
        <a href="{{ route('legal.contact') }}" class="text-indigo-600 dark:text-indigo-400">İletişim</a>
        sayfamızdaki e-posta adresinden bize başvurabilirsiniz.
    </p>

    <p class="text-xs text-gray-400">
        Bu metin bir şablondur; yayına almadan önce hukuki danışmanlık alınarak firmanıza özel
        şekilde güncellenmelidir.
    </p>
</x-public-layout>

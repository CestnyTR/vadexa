<x-public-layout title="Teslimat ve İade Şartları">
    <p class="text-xs text-gray-400">Son güncelleme: {{ date('d.m.Y') }}</p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Teslimat (Hizmetin İfası)</h2>
    <p>
        {{ config('company.brand') }} bir dijital yazılım hizmetidir; fiziksel ürün gönderimi yoktur.
        Ödemenin onaylanmasının ardından seçtiğiniz plan <strong>anında</strong> hesabınıza tanımlanır
        ve hizmet erişiminiz derhal açılır.
    </p>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">İade ve İptal</h2>
    <p>
        Aboneliğinizi dilediğiniz zaman <strong>Plan</strong> sayfasından iptal edebilirsiniz. İptal
        durumunda mevcut fatura döneminin sonuna kadar hizmetten yararlanmaya devam edersiniz; sonraki
        dönem için ücret alınmaz.
    </p>
    <p>
        Yanlış veya mükerrer çekim gibi durumlarda, işlem tarihinden itibaren makul süre içinde
        <a href="{{ route('legal.contact') }}" class="text-indigo-600 dark:text-indigo-400">İletişim</a>
        sayfamızdan bize ulaşmanız hâlinde ödeme, aynı ödeme yöntemi üzerinden iade edilir.
    </p>

    <p class="text-xs text-gray-400">
        Bu metin bir şablondur; yayına almadan önce hukuki danışmanlık alınarak güncellenmelidir.
    </p>
</x-public-layout>

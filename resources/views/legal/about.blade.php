<x-public-layout title="Hakkımızda">
    <p>
        {{ config('company.brand', 'Vadexa') }}, işletmelerin müşteri, vadeli alacak ve tahsilat
        süreçlerini tek panelden yönetmesini sağlayan bir SaaS (yazılım hizmeti) platformudur.
        Vadesi gelen alacaklar için otomatik SMS ve e-posta hatırlatmaları göndererek tahsilat
        oranlarını yükseltmeyi amaçlar.
    </p>

    <p>
        Platform, {{ config('company.legal_name') }} tarafından işletilmektedir. Sunduğumuz hizmet,
        aylık abonelik modeliyle çalışır; farklı ihtiyaçlara yönelik Ücretsiz, Pro ve Business
        planları mevcuttur. Plan detaylarına ve fiyatlandırmaya uygulama içindeki
        <strong>Plan</strong> sayfasından ulaşabilirsiniz.
    </p>

    <p>
        Sorularınız ve talepleriniz için <a href="{{ route('legal.contact') }}" class="text-indigo-600 dark:text-indigo-400">İletişim</a>
        sayfamızdaki bilgilerden bize ulaşabilirsiniz.
    </p>
</x-public-layout>

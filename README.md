# Vadexa

Vadexa, KOBİ'ler için çok kiracılı (multi-tenant) bir **alacak takip ve tahsilat hatırlatma** uygulamasıdır. Müşterileri, vadeli ve taksitli alacakları ve görevleri tek bir yerden yönetmeni; vadesi gelen alacaklar için müşterilere otomatik olarak **SMS (NetGSM)** ve **e‑posta** hatırlatmaları göndermeni sağlar.

Laravel 12 + Blade (Breeze) üzerine kuruludur.

## Özellikler

- **Müşteri yönetimi** — telefon ve e‑posta bilgileriyle müşteri kayıtları.
- **Alacak takibi** — vade tarihi, tutar, durum (ödendi/bekliyor); tekrar eden ve taksitli alacak desteği (`recurrence_type`, `installment_count`, `parent_id`).
- **Görevler** — müşteri/alacaklarla ilişkili yapılacaklar.
- **Command Center** — müşteri, alacak ve görev oluşturma, alacağı ödendi işaretleme ve tek tıkla bildirim gönderme için hızlı işlem paneli.
- **Otomatik bildirimler** — vadesi gelen alacaklar için SMS ve e‑posta hatırlatmaları; tenant bazında açık/kapalı ayarı ve gönderim saati.
- **Loglama** — gönderilen tüm SMS ve e‑postalar için kayıt (`sms_logs`, `mail_logs`).
- **Dashboard** — özet metrikler ve genel durum.
- **Çok kiracılılık** — her tenant kendi müşteri, alacak, görev ve loglarına sahiptir (`BelongsToTenant`).

## Mimari

Uygulama katmanlı bir yapı kullanır:

- **Controllers** — yalnızca HTTP giriş/çıkışı; iş mantığını servislere devreder.
- **Services** (`app/Services`) — iş kuralları: `CustomerService`, `ReceivableService`, `TaskService`, `DashboardService`, `NotificationService`, `TenantService`.
- **Repositories** (`app/Repositories/Eloquent`) — veri erişimi, `app/Contracts/Repositories` altındaki interface'ler arkasında soyutlanmıştır ve `RepositoryServiceProvider` ile bind edilir.
- **Jobs** — bildirim gönderimi kuyruğa alınarak yapılır (`SendSmsJob`, `SendDueMailJob`).

## Gereksinimler

- PHP 8.2+
- Composer
- Node.js & npm
- SQLite (varsayılan) veya MySQL/PostgreSQL

## Kurulum

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # SQLite kullanıyorsan
php artisan migrate
npm install
npm run build
```

> Kısayol: `composer setup` yukarıdaki adımların çoğunu tek komutta çalıştırır.

## Geliştirme

Sunucu, kuyruk, log takibi ve Vite'ı aynı anda başlatmak için:

```bash
composer dev
```

Bu komut şunları paralel çalıştırır: `php artisan serve`, `php artisan queue:listen`, `php artisan pail` ve `npm run dev`.

## Bildirimler

Vadesi gelen alacaklar için hatırlatmaları göndermek üzere:

```bash
php artisan app:send-due-notifications
```

Command Center üzerinden `command-center.run-notifications` endpoint'i ile de tetiklenebilir. Bildirim gönderimi kuyruk üzerinden yapıldığından bir queue worker'ın çalışıyor olması gerekir.

### NetGSM (SMS) ayarları

`.env` içindeki ilgili değişkenler:

```env
NETGSM_MOCK=true          # true iken gerçek SMS gönderilmez, loglanır
NETGSM_API_URL=https://api.netgsm.com.tr/sms/send/get/
NETGSM_USER_CODE=
NETGSM_PASSWORD=
NETGSM_HEADER=
```

Geliştirme sırasında `NETGSM_MOCK=true` bırakılması önerilir.

## Test

```bash
composer test
# veya
php artisan test
```

## Lisans

MIT

# Vadexa — Yazılım Şartnamesi (Spec)

Bu belge Vadexa'nın ne yaptığını, hangi kurallara göre çalıştığını ve nasıl tasarlandığını tanımlar. Kod tabanının referans dokümanıdır.

- **Sürüm:** 1.0
- **Tür:** Çok kiracılı (multi-tenant) SaaS — alacak takip & tahsilat hatırlatma
- **Yığın:** Laravel 12 · PHP 8.2+ · Blade + Alpine.js · Tailwind CSS · Vite · SQLite/MySQL

---

## 1. Amaç ve kapsam

### 1.1 Problem
İşletmeler (özellikle KOBİ'ler) vadeli/taksitli alacaklarını takip etmekte ve müşterilerini zamanında hatırlatmakta zorlanır. Manuel takip → geciken tahsilat, unutulan borçlar.

### 1.2 Çözüm
Vadexa; müşteri, alacak ve görevleri tek panelde toplar ve vadesi gelen alacaklar için otomatik SMS/e-posta hatırlatması gönderir. Her işletme (tenant) izole bir çalışma alanına, ekip üyelerine ve abonelik planına sahiptir.

### 1.3 Kapsam dışı (non-goals)
- Muhasebe/e-fatura entegrasyonu
- Gerçek tekrarlayan (recurring) tahsilat — mevcut ödeme tek seferliktir
- Mobil uygulama
- Bu bir **portfolyo projesidir**; SMS (NetGSM) ve ödeme (Iyzico) **mock modda** çalışır.

---

## 2. Aktörler ve roller

| Aktör | Açıklama |
|---|---|
| **Ziyaretçi** | Giriş yapmamış kullanıcı. Landing ve yasal sayfaları görür, kayıt olabilir. |
| **Owner (Yönetici)** | Tenant'ı kaydeden ilk kullanıcı. Tüm yetkiler + ekip, şablon, plan/ödeme yönetimi. |
| **Member (Üye)** | Davetle katılan kullanıcı. Günlük operasyon (müşteri/alacak/görev, raporlar) yapar; yönetim ayarlarına erişemez. |

**Yetki matrisi (özet):**

| Yetenek | Owner | Member |
|---|:---:|:---:|
| Müşteri / alacak / görev CRUD | ✓ | ✓ |
| Bildirim gönderme, raporlar | ✓ | ✓ |
| Ekip yönetimi (davet, çıkar, rol) | ✓ | ✗ |
| Mesaj şablonları | ✓ | ✗ |
| Plan yükseltme / ödeme | ✓ | ✗ |

Yetki, `owner` route middleware'i ([EnsureUserIsOwner](../app/Http/Middleware/EnsureUserIsOwner.php)) ile zorlanır.

---

## 3. Çok kiracılılık (multi-tenancy)

**Model:** Tek veritabanı, `tenant_id` sütunuyla satır bazında izolasyon (shared-database, shared-schema).

**Mekanizma:**
1. Tenant'a bağlı her model [`BelongsToTenant`](../app/Models/Concerns/BelongsToTenant.php) trait'ini kullanır.
2. Trait, global scope ekler → tüm sorgular otomatik `where tenant_id = <aktif tenant>` alır; ayrıca `creating` olayında `tenant_id`'yi otomatik doldurur.
3. Aktif tenant [`TenantContext`](../app/Support/TenantContext.php) (statik bağlam) içinde tutulur.
4. [`ResolveTenant`](../app/Http/Middleware/ResolveTenant.php) middleware'i her istekte tenant'ı **giriş yapan kullanıcıdan** çözüp `TenantContext`'e yazar.
5. Bağlam yoksa `TenantContext::tenantId()` **exception fırlatır** (sessiz veri sızıntısı yerine sert hata).

**Kayıt akışı:** Yeni kayıt → yeni `Tenant` + `owner` kullanıcı + varsayılan mesaj şablonları (transaction içinde).

**İzolasyon garantisi:** Bir tenant, route model binding ile başka tenant'ın kaydına erişmeye çalışırsa global scope nedeniyle **404** alır (bkz. `TenantIsolationTest`).

---

## 4. Domain modeli

```
Tenant 1──* User
Tenant 1──* Customer 1──* Receivable *──1 Customer
Tenant 1──* Task *──1 Customer (opsiyonel)
Receivable 1──* Receivable (parent_id: taksitler)
Tenant 1──* SmsLog / MailLog
Tenant 1──* Invitation
Tenant 1──* NotificationTemplate  (channel × type)
Tenant 1──1 Subscription
Tenant 1──* Payment
```

### 4.1 Varlıklar

- **Tenant** — firma/çalışma alanı. `plan`, `trial_ends_at`, `notification_time`, `sms_enabled`, `mail_enabled`.
- **User** — `tenant_id`, `role` (owner/member).
- **Customer** — ad, telefon, e-posta, notlar.
- **Receivable** — `amount`, `due_date`, `status`, `paid_at`, `description`, taksit/tekrar alanları (`recurrence_type`, `installment_count`, `installment_number`, `parent_id`).
- **Task** — `title`, `status`, `due_date`, opsiyonel müşteri.
- **NotificationTemplate** — `channel` (sms/mail) × `type` (upcoming/due_today/overdue) × `body`. Tenant başına tekil.
- **SmsLog / MailLog** — gönderim kaydı (`type`, `status`, `message/subject`, `error`, `context_*`).
- **Invitation** — `email`, `role`, `token`, `expires_at`, `accepted_at`.
- **Subscription** — `plan`, `status`, `provider`, `current_period_ends_at`.
- **Payment** — `plan`, `amount`, `currency`, `status`, `provider_ref`, `paid_at`.

---

## 5. İş kuralları

### 5.1 Alacak durumları
`upcoming` (yaklaşan), `due_today` (bugün vadeli), `overdue` (gecikmiş), `paid` (ödendi). Durum türü, `due_date`'in bugüne göre konumundan hesaplanır.

### 5.2 Bildirim kuralları
- Gönderim tipi: vade yarın → `upcoming`, bugün → `due_today`, geçmiş → `overdue`.
- **İdempotent:** Aynı alacak için aynı gün ikinci kez SMS/e-posta gönderilmez (log kontrolü).
- Tenant `sms_enabled`/`mail_enabled` bayrakları gönderimi kapatabilir.
- **Kota:** Aylık SMS/e-posta limiti aşılırsa gönderim atlanır (bkz. §6).
- Mesaj metni, tenant şablonundan üretilir; `{ad}`, `{tutar}`, `{vade}` yer tutucuları doldurulur. Şablon yoksa dahili varsayılan kullanılır.

### 5.3 Zamanlama
`notifications:send-due --respect-schedule` saatlik çalışır; yalnızca `notification_time` saati o an olan tenant'lar işlenir. Manuel çalıştırma tüm tenant'ları işler. Gönderim kuyruğa alınır (`SendSmsJob`, `SendDueMailJob`).

### 5.4 Ekip kuralları
- Davet: benzersiz e-posta (kullanıcı veya bekleyen davet olarak zaten yoksa), 7 gün geçerli token.
- **Son owner korunur:** tek owner silinemez veya member'a düşürülemez ([TeamRuleException](../app/Exceptions/TeamRuleException.php)).
- Davet kabulü: token geçerliyse ilgili tenant'ta kullanıcı oluşturur ve giriş yaptırır.

---

## 6. Planlar, kotalar ve deneme

Planlar [`config/plans.php`](../config/plans.php) içinde tanımlıdır.

| Plan | Fiyat/ay | Müşteri | Kullanıcı | Aylık SMS | Aylık E-posta |
|---|---|---|---|---|---|
| Free | 0 ₺ | 25 | 1 | 50 | 200 |
| Pro | 499 ₺ | 500 | 5 | 2000 | ∞ |
| Business | 1499 ₺ | ∞ | ∞ | ∞ | ∞ |

- **Deneme:** Yeni tenant 14 gün boyunca **Pro** limitlerini kullanır (`trial_ends_at`).
- **Zorlama:** [`QuotaService`](../app/Services/QuotaService.php) müşteri/kullanıcı oluşturmadan önce limiti kontrol eder; aşımda [`PlanLimitException`](../app/Exceptions/PlanLimitException.php) → kullanıcıya dostu hata. SMS/e-posta kotası gönderim anında kontrol edilir.
- **Deneme bitişi:** Sessizce Free limitlerine döner (freemium).

---

## 7. Ödeme (mock-ready)

**Soyutlama:** [`PaymentGateway`](../app/Contracts/PaymentGateway.php) arayüzü → [`IyzicoGateway`](../app/Services/Payments/IyzicoGateway.php).

- **Mock mod** (varsayılan / anahtar yoksa): ödeme simüle edilir, uçtan uca çalışır.
- **Gerçek mod:** IYZWSv2 (HMAC-SHA256) imzalı istek hazır; yalnızca `.env` anahtarları eksik.

**Akış** ([BillingService](../app/Services/BillingService.php)):
1. `subscribe(plan)` → `Payment` (pending) oluştur.
2. Gateway `charge()` → başarısızsa payment `failed` + `PaymentException`.
3. Başarıda (transaction): payment `paid` → `Subscription` aktif (dönem +1 ay) → tenant planı güncellenir, deneme kapatılır.
4. `cancel()` → abonelik iptal + Free plana dönüş.

---

## 8. Rotalar (özet)

**Herkese açık:** `/` (landing / giriş yapan → dashboard), `/login`, `/register`, `/hakkimizda`, `/iletisim`, `/gizlilik-politikasi`, `/mesafeli-satis-sozlesmesi`, `/teslimat-ve-iade`, `/invitations/{token}`.

**Kimlik doğrulamalı:** `/dashboard`, `/onboarding`, `customers|receivables|tasks` (resource), `/sms-logs`, `/mail-logs`, `/reports`, `/plan`, `command-center/*`.

**Owner-only:** `/team` (+ üye/davet işlemleri), `/templates`, `/billing/subscribe`, `/billing/cancel`.

---

## 9. Mimari

Katmanlı yapı: **Controller → Service → Repository**.

- **Controller** — yalnızca HTTP giriş/çıkış; iş mantığını servise devreder.
- **Service** ([app/Services](../app/Services)) — iş kuralları. Örn. `CustomerService`, `NotificationService`, `BillingService`, `QuotaService`, `TeamService`, `ReportService`.
- **Repository** ([app/Repositories/Eloquent](../app/Repositories/Eloquent)) — veri erişimi, [Contracts/Repositories](../app/Contracts/Repositories) arayüzleri arkasında; `RepositoryServiceProvider` ile bind edilir.
- **Job** — bildirimler kuyruk üzerinden gönderilir.
- **Middleware** — `ResolveTenant` (tenant çözümü), `owner` (yetki).

**İstek yaşam döngüsü (örnek — müşteri oluşturma):**
```
POST /customers
 → auth + verified + ResolveTenant (TenantContext set)
 → CustomerController@store
 → CustomerService::create()  ├─ QuotaService::assertCanCreate('customers')
                              └─ CustomerRepository::create()  (BelongsToTenant → tenant_id + scope)
 → redirect + flash
```

**Tasarım kararları:** bkz. [PLAN.md](PLAN.md).

---

## 10. Güvenlik ve gizlilik

- Tenant izolasyonu global scope + `TenantContext` ile zorlanır; bağlam yoksa hata.
- Yetki `owner` middleware'i; çapraz-tenant erişim 404.
- Kimlik doğrulama Laravel Breeze (session, hashed parola, doğrulama iskeleti).
- Gizli anahtarlar yalnızca `.env` (git dışı). Mock modda gerçek sır bulunmaz.

---

## 11. Test

`php artisan test` — **129 test**. Kapsam: tenant izolasyonu, plan limitleri, ekip/davet, faturalama, raporlar, bildirim şablonları, yasal sayfalar ve mevcut CRUD/bildirim akışları.

# Vadexa — SaaS Yol Haritası

> Alacak takip & tahsilat hatırlatma uygulamasını tekil demodan gerçek bir SaaS ürününe taşımak için teknik yol haritası.
> Durum: taslak · Son güncelleme: 2026-07-13

## Mevcut durumun özeti

**Güçlü yanlar**
- Katmanlı mimari: Controller → Service → Repository (interface + Eloquent).
- Temiz domain: Customer, Receivable (taksit/tekrar), Task, SMS/Mail log, Tenant.
- Bildirim altyapısı kuyruk üzerinden (`SendSmsJob`, `SendDueMailJob`), NetGSM mock desteği.

**Kritik açık — çok kiracılılık gerçekte çalışmıyor**
- `ResolveTenant` giriş yapan kullanıcıya bakmadan **ilk aktif tenant'ı** seçiyor.
- `users` tablosunda `tenant_id` yok; User↔Tenant ilişkisi yok.
- Kayıt akışı tenant oluşturmuyor → tüm kullanıcılar aynı veriyi görür.
- `BelongsToTenant` global scope doğru, ama `TenantContext` yanlış kaynaktan besleniyor, izolasyon boşa çıkıyor.

Bu açık kapatılmadan hiçbir SaaS özelliği güvenli değildir. Faz 1 bu yüzden zorunlu ilk adım.

---

## Faz 1 — Gerçek tenant izolasyonu (temel)

**Amaç:** Her firma yalnızca kendi verisini görsün; tenant, giriş yapan kullanıcıdan çözülsün.

### İşler
1. **Şema:** `users` tablosuna `tenant_id` (foreignId, nullable→sonra zorunlu, index) ekle.
2. **Model:** `User` → `belongsTo(Tenant)`, `Tenant` → `hasMany(User)`. `User`'a `tenant_id` fillable ekle.
3. **Kayıt akışı (`RegisteredUserController`):** register olurken
   - yeni `Tenant` oluştur (firma adı alanı ekle),
   - kullanıcıyı o tenant'a bağla,
   - varsayılan bildirim ayarlarını kur.
4. **`ResolveTenant` yeniden yaz:** `Auth::user()->tenant_id` → `TenantContext::setTenantId()`. Kullanıcı yoksa context set etme.
5. **`TenantContext`:** fallback olarak "ilk aktif tenant" davranışını **kaldır** (sessiz veri sızıntısı riski). Context yoksa exception.
6. **Middleware sırası:** `ResolveTenant` `auth`'tan **sonra** çalışmalı.
7. **Konsol/queue bağlamı:** `app:send-due-notifications` ve job'lar tüm tenant'lar üzerinde döngüyle çalışmalı (her tenant için context set → işle → temizle). Şu an tek tenant varsayıyor.

### Kabul kriterleri
- İki farklı tenant kullanıcısı giriş yaptığında yalnızca kendi müşteri/alacak/log verisini görür.
- Yeni register → yeni tenant + izole veri.
- `TenantContext` set edilmemişken tenant'a bağlı sorgu patlar (sessiz global sonuç dönmez).
- Feature test: cross-tenant veri erişimi engelleniyor (tenant A, tenant B'nin receivable'ına 404).

---

## Faz 2 — Abonelik, planlar ve onboarding (para kazanma)

**Amaç:** Kayıt → deneme → ücretli plan akışı; plana göre limitler.

### İşler
1. **Faturalama:** Laravel Cashier. TR için **Iyzico** (veya global için Stripe) — abonelik + kart saklama.
2. **Planlar:** `plans` tablosu / config — Free/Pro/Business; her plana kota:
   - müşteri sayısı, aylık SMS/e-posta adedi, kullanıcı sayısı.
3. **Deneme süresi:** 14 gün trial; bitişte kısıtlama.
4. **Limit enforcement:** Service katmanında kota kontrolü (ör. `CustomerService::create` öncesi limit). Aşımda anlamlı hata.
5. **Onboarding:** ilk giriş sihirbazı (firma bilgisi, SMS başlığı, ilk müşteri), boş-durum (empty state) ekranları.
6. **Billing paneli:** plan yükselt/düşür, fatura geçmişi, kart güncelleme.

### Kabul kriterleri
- Trial biten tenant yazma işlemi yapamaz, okuma açık.
- Plan kotası aşılınca işlem net hata ile durur.
- Iyzico test ortamında abonelik başlatma/iptal çalışıyor.

---

## Faz 3 — Ekip, roller ve güvenlik

1. **Roller:** tenant içi `owner` / `member` (basit) — spatie/laravel-permission opsiyonel.
2. **Ekip daveti:** e-posta ile davet, davet kabul akışı.
3. **Denetim (audit) log:** kritik işlemler (alacak silme, ödeme işaretleme) kim/ne zaman.
4. **Güvenlik sertleştirme:** rate limiting (özellikle bildirim tetikleme), yetki policy'leri, `.env.example` temizliği.

---

## Faz 4 — Ürün farklılaştırması

Önceliklendirilebilir bağımsız modüller:

- **Bildirim şablonları** — tenant başına özelleştirilebilir metin, değişkenler (`{ad}`, `{tutar}`, `{vade}`).
- **Ödeme linki** — hatırlatmaya Iyzico/PayTR linki, online tahsilat + otomatik "ödendi".
- **WhatsApp Business** entegrasyonu (TR'de tahsilatta yüksek etki).
- **Otomasyon kuralları** — "vade +3 gün → SMS, +7 gün → arama görevi".
- **Raporlar** — tahsilat oranı, alacak yaşlandırma (aging), müşteri risk skoru.
- **Muhasebe entegrasyonu** — Paraşüt / Logo / e-Fatura.
- **Müşteri self-servis portalı** — müşteri kendi borcunu/ödemesini görür.

---

## Yatay konular (tüm fazlara yayılı)

- **Testler:** en azından tenant izolasyonu, bildirim gönderimi ve limit enforcement için feature testleri. Şu an test kapsamı zayıf.
- **Scheduler:** `notification_time` alanı kullanılmalı; `app:send-due-notifications` `routes/console.php` içinde tenant'ın saatine göre zamanlanmalı.
- **CI:** Pint (format) + PHPUnit + migrate kontrolü.
- **Gözlemlenebilirlik:** bildirim başarı/başarısızlık metrikleri, kuyruk izleme.
- **`.env.example`:** `APP_NAME=vadexa`, NetGSM prod notu.

---

## Önerilen sıra

1. **Faz 1** (zorunlu, ~diğer her şeyin önkoşulu)
2. **Faz 2** (gelir)
3. **Faz 3** (ekip/güvenlik — ilk müşteriler gelmeden hemen sonra)
4. **Faz 4** (talebe göre modül modül)

> Not: Faz 1 tamamlanmadan Faz 2+ geliştirmek, izolasyonsuz veri üzerine faturalama kurmak demektir — teknik borç ve güvenlik riski büyür.

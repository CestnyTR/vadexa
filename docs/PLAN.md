# Vadexa — Geliştirme Planı

Bu belge, projenin nasıl inşa edildiğini (fazlar, durum) ve alınan teknik kararları belgeler. Şartname için bkz. [SPEC.md](SPEC.md).

**Durum özeti:** Tüm fazlar tamamlandı. 129 test geçiyor. SMS ve ödeme mock modda (portfolyo).

Durum anahtarı: ✅ tamam · 🟡 kısmi · ⬜ yapılmadı (kapsam dışı)

---

## Fazlar

### Faz 1 — Gerçek tenant izolasyonu ✅
Tekil demoyu gerçek çok kiracılı yapıya taşıma.

- ✅ `users.tenant_id` + `role`; User↔Tenant ilişkileri
- ✅ Kayıt akışı: yeni tenant + owner + varsayılan şablonlar (transaction)
- ✅ `ResolveTenant` tenant'ı giriş yapan kullanıcıdan çözer
- ✅ `TenantContext`'in güvensiz "ilk tenant" fallback'i kaldırıldı (artık exception)
- ✅ Bildirim komutu/zamanlayıcı tüm aktif tenant'lar üzerinde döner
- ✅ Kabul: iki tenant kullanıcısı yalnızca kendi verisini görür; çapraz erişim 404

### Faz 2 — Planlar, kotalar, onboarding ✅
- ✅ `config/plans.php` (Free/Pro/Business) + 14 gün deneme
- ✅ `QuotaService` + `PlanLimitException` (müşteri/kullanıcı/SMS/e-posta limitleri)
- ✅ Kayıt sonrası onboarding sihirbazı; Plan & Kullanım sayfası

### Faz 3 — Roller, ekip, güvenlik ✅
- ✅ owner/member rolleri + `owner` middleware
- ✅ Ekip: e-posta ile davet, üye çıkarma/rol değiştirme, son-owner koruması
- ✅ Token'lı public davet kabul akışı
- ✅ Çapraz-tenant koruması

### Faz 4 — Farklılaştırma ✅
- ✅ Bildirim şablonları ({ad}/{tutar}/{vade}) + canlı önizleme, iki kanalda da uygulanır
- ✅ Raporlar: tahsilat oranı + alacak yaşlandırma
- ✅ Otomasyon: `notification_time`'a göre saatlik zamanlama

### Faz 5 — Ödeme altyapısı (mock-ready) ✅
- ✅ `PaymentGateway` soyutlaması + `IyzicoGateway` (mock + IYZWSv2 imzalı gerçek path)
- ✅ `subscriptions` + `payments` tabloları; `BillingService` subscribe/cancel
- ✅ Plan sayfası: çalışan yükseltme/iptal + ödeme geçmişi

### Faz 6 — iyzico üye iş yeri gereklilikleri ✅
- ✅ Halka açık yasal sayfalar: Hakkımızda, İletişim (künye), Gizlilik, Mesafeli Satış, Teslimat & İade
- ✅ `config/company.php` ile künye (env-driven)
- ✅ Footer + ödeme logoları (yer tutucu)

### Faz 7 — UI / UX ✅
- ✅ Landing sayfası (hero, özellikler, fiyatlar, CTA)
- ✅ Türkçe metin düzeltmeleri (SMS gövdeleri bilinçli ASCII)
- ✅ `x-status-badge` tasarım bileşeni; sekmeli gönderilenler & şablonlar
- ✅ Sticky footer; teknik olmayan kullanıcıya göre sadeleştirilmiş dil

---

## Teknik kararlar (ve gerekçeleri)

| Karar | Gerekçe |
|---|---|
| **Shared-DB + `tenant_id` global scope** | Tek DB, düşük operasyon maliyeti; global scope izolasyonu tek noktada garanti eder. DB-per-tenant bu ölçek için gereksiz karmaşıklık. |
| **`TenantContext` statik + fallback yok** | Bağlam yoksa sessiz "tüm veri" yerine sert hata → veri sızıntısını imkânsız kılar. |
| **Controller → Service → Repository** | İş mantığı test edilebilir servislerde; veri erişimi interface arkasında soyut → değiştirilebilir/mocklanabilir. |
| **`PaymentGateway` arayüzü + mock** | Sağlayıcı değiştirilebilir; gerçek anahtar olmadan uçtan uca test/demo. NetGSM ile aynı desen. |
| **Kuyruklu bildirim (Job)** | Gönderim yavaş/dış servise bağlı; HTTP isteğini bloklamaz, retry/backoff sağlar. |
| **Planlar config'te, ödeme opsiyonel** | Portfolyo için gerçek recurring/webhook gereksiz; plan mantığı ve limitler tam çalışır. |
| **İş kuralı exception'ları (PlanLimit/TeamRule/Payment)** | HTTP `abort()`'tan ayrı tutulur; controller'da yakalanıp kullanıcıya dostu mesaja çevrilir, 404/403 ile karışmaz. |

---

## Bilinçli kapsam dışı (portfolyo)

- ⬜ Gerçek NetGSM / Iyzico anahtarları (mock son hâl)
- ⬜ Tekrarlayan (recurring) tahsilat + Iyzico webhook
- ⬜ Production kurulumu (queue worker, cron, SSL, gerçek mail/DB)
- ⬜ Denetim (audit) log, e-posta doğrulama zorunluluğu, rate limiting
- ⬜ Yasal metinlerin hukuki incelemesi (şablon metin)

Bunlar canlı ticari lansman içindir; portfolyo/demo amacı için gerekmez.

---

## Olası sonraki adımlar

Ürünü ileri taşımak istenirse öncelik sırası:
1. Tekrarlayan abonelik + Iyzico webhook (gerçek SaaS faturalama)
2. Production dağıtımı (queue/cron/SSL/mail/DB) + CI
3. Denetim log, e-posta doğrulama, rate limiting
4. WhatsApp bildirimi, ödeme linki, muhasebe entegrasyonu

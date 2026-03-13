# AI Article Generator – Selftest
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/SELFTEST.md

---

## 1. Amaç

Selftest sistemi, AI Article Generator modülünün çalışma sağlığını doğrulamak için vardır.

Amaç:
- eksik dosyaları erken görmek
- yanlış bootstrap sırasını fark etmek
- provider yapılandırma sorunlarını yakalamak
- storage/log erişim problemlerini görmek
- router/gateway/provider zincirinin gerçekten çalışıp çalışmadığını anlamak
- runtime’da rastgele hata beklemek yerine kontrollü sağlık raporu üretmek

Selftest yalnızca "dosya var mı" testi değildir.  
Gerçek kontratların sağlıklı olup olmadığını da anlamalıdır.

---

## 2. Mimari Konum

Ana dosyalar:
- core/services/selftest-service.php
- core/ai-article-selftest.php
- storage/health.json

Selftest ideal olarak service katmanında merkezi biçimde toplanmalıdır.  
Legacy selftest parçaları varsa uyumluluk için kalabilir, ama resmi referans `selftest-service.php` olmalıdır.

---

## 3. Selftest’in Kapsadığı Alanlar

Selftest en az şu alanlarda sağlık raporu vermelidir:

1. module bootstrap
2. paths/constants
3. storage erişimi
4. log erişimi
5. provider registry
6. provider availability
7. router
8. gateway
9. prompt preset/config
10. news kaynakları
11. article pipeline
12. rewrite pipeline/service
13. seo service/engine
14. usage/health storage yazımı

---

## 4. Selftest Türleri

Selftest tek seviyeli olmamalıdır.

### 4.1 Static Checks
Dosya, sınıf, sabit, klasör, yazma izni gibi kontroller.

### 4.2 Contract Checks
Fonksiyon döndü mü, response shape doğru mu, registry provider görüyor mu gibi kontroller.

### 4.3 Runtime Checks
Gerçekten bir test prompt’u ile provider çağrısı yapabiliyor mu, router route döndürüyor mu gibi canlı testler.

### 4.4 Optional Deep Checks
Gerekirse daha pahalı veya daha kapsamlı testler:
- article_generate dry run
- rewrite dry run
- news collect small sample
- seo generate sample

---

## 5. Kontrol Grupları

### 5.1 Module Bootstrap Checks

Amaç:
Bootstrap’ın temel bileşenleri yüklediğini doğrulamak.

Örnek kontroller:
- ana bootstrap dosyası okunabiliyor mu
- temel constant/path değerleri var mı
- log fonksiyonu hazır mı
- settings okuyucu hazır mı

Başarısızlık örneği:
- path constant eksik
- foundation dosyaları geç yüklenmiş
- registry dosyası hiç dahil edilmemiş

---

### 5.2 Storage Checks

Kontrol edilecek yollar:
- storage/
- storage/settings.json
- storage/providers.json
- storage/models.json
- storage/router.json
- storage/prompt-presets.json
- storage/feature-map.json
- storage/health.json
- storage/usage/

Kontroller:
- dosya/klasör var mı
- okunabilir mi
- gerekiyorsa yazılabilir mi
- boş/bozuk JSON mu
- beklenen ana alanlar mevcut mu

### Kural
Selftest yalnız “dosya var” dememeli; JSON parse edilebiliyor mu da bakmalıdır.

---

### 5.3 Log Checks

Kontrol edilecek yollar:
- logs/ai-article-generator.log
- logs/news.log
- logs/collector.log

Kontroller:
- klasör var mı
- yazılabilir mi
- log yazımı denendiğinde hata oluşuyor mu

### Amaç
Sessiz hata yaşayan sistemleri görünür kılmak.

---

### 5.4 Provider Registry Checks

Kontrol edilecekler:
- registry dosyası yüklenmiş mi
- registry fonksiyonları/sınıfı var mı
- provider key listesi alınabiliyor mu
- tanımlı provider’lar registry’de görünüyor mu

Örnek kontrol soruları:
- openai registry’de var mı?
- groq registry’de var mı?
- provider örneği oluşturulabiliyor mu?

### Kritik not
Yanlış fonksiyon adı kontrol edilirse selftest false negative üretir.  
Bu hata geçmişte görülen önemli risklerden biridir.

---

### 5.5 Provider Availability Checks

Her aktif provider için:
- enabled mi
- config var mı
- api key/connection bilgisi var mı
- class load olmuş mu
- health/ping yapılabiliyor mu

### Sonuç seviyeleri
- pass
- warning
- fail

Örnek:
- provider dosyası var ama api key yok → warning/fail
- provider disabled → info/warning
- provider active ama request timeout → fail

---

### 5.6 Router Checks

Kontrol edilecekler:
- router.json okunuyor mu
- article_generate için route var mı
- article_rewrite için route var mı
- seo_generate için route var mı
- selected primary provider available mı
- fallback chain mantıklı mı

### Örnek hata
- route tanımlı ama provider disabled
- fallback zincirindeki model models.json’da yok
- rewrite route capability’si olmayan modele gidiyor

---

### 5.7 Gateway Checks

Kontrol edilecekler:
- gateway fonksiyonları mevcut mu
- normalize response üretilebiliyor mu
- basit test prompt’u işlenebiliyor mu
- timeout/retry mekanizması çalışıyor mu
- hata shape’i tek standartta mı

### Amaç
Provider var gibi görünse bile gateway contract’ı bozuksa bunu yakalamak.

---

### 5.8 News Layer Checks

Kontrol edilecek dosyalar:
- data/news-sources.json
- core/news/news-sources.php
- core/news/news-collector.php
- core/news/news-normalizer.php
- core/news/news-fact-pack.php

Kontroller:
- news source dosyası parse ediliyor mu
- en az bir kaynak mevcut mu
- kategori alanları okunabiliyor mu
- küçük örnek news collect çalışıyor mu
- normalize sonrası beklenen alanlar oluşuyor mu

### Kural
Collector dosyası var diye news katmanı sağlıklı sayılmaz.

---

### 5.9 Pipeline Checks

Kontrol edilecekler:
- context build fonksiyonu çalışıyor mu
- outline üretilebiliyor mu
- pipeline task response shape doğru mu
- article object normalize edilebiliyor mu
- prompt/messages alanı eksik kalmıyor mu

### Kritik amaç
Bugünkü ana sorunlardan biri olan “payload var ama gerçek prompt contract eksik” durumunu yakalamak.

---

### 5.10 Rewrite Checks

Kontrol edilecekler:
- rewrite-service mevcut mu
- rewrite için route bulunuyor mu
- test içerik üzerinde minimal rewrite çağrısı yapılabiliyor mu
- rewrite response standardı korunuyor mu
- rewrite cleanup ile karışmış mı

### Amaç
“Rewrite boş dönüyor” problemini daha oluşmadan fark etmek.

---

### 5.11 SEO Checks

Kontrol edilecekler:
- seo-service mevcut mu
- meta builder çalışıyor mu
- schema builder çalışıyor mu
- faq builder çalışıyor mu
- seo response article response ile birleşebiliyor mu

---

## 6. Selftest Sonuç Formatı

Selftest çıktısı standart olmalıdır.

Örnek shape:

```php
[
  'ok' => true,
  'summary' => [
    'total' => 24,
    'passed' => 20,
    'warnings' => 3,
    'failed' => 1,
  ],
  'checks' => [
    [
      'group' => 'storage',
      'name' => 'settings_json_readable',
      'status' => 'pass',
      'message' => 'settings.json parsed successfully',
      'meta' => [],
    ],
  ],
  'meta' => [
    'generated_at' => '2026-03-11T12:00:00+03:00',
    'build' => '',
  ],
]

Status değerleri

pass

warning

fail

info

7. health.json Kullanımı

Dosya:

storage/health.json

Bu dosya en son selftest sonucunun özetini saklayabilir.

Örnek alanlar:

last_run_at

overall_status

passed

warnings

failed

critical_failures

provider_health

storage_health

Kural

health.json gerçek kaynağın yerine geçmez; son bilinen durum özetidir.

8. Selftest Çalıştırma Zamanları

Selftest şu durumlarda tetiklenebilir:

panelden manuel

önemli ayar kaydından sonra

provider config değişiminden sonra

deploy/upgrade sonrası

debug bakım modunda otomatik

Ama pahalı runtime testleri her sayfa yüklenişinde çalıştırılmamalıdır.

9. False Negative / False Positive Riskleri
False Negative

Sistem sağlıklı olduğu halde selftest bozuk gösterir.

Örnek nedenler:

yanlış constant adı kontrolü

yanlış fonksiyon adı bekleme

registry API değişmiş ama selftest güncellenmemiş

False Positive

Sistem bozuk olduğu halde selftest sağlıklı gösterir.

Örnek nedenler:

yalnız dosya varlığına bakmak

gerçek runtime çağrısı yapmamak

JSON parse etmeden dosya var saymak

Kural

Selftest kod değiştikçe güncellenmelidir.

10. Panelde Gösterim İlkeleri

Selftest sonuçları panelde anlaşılır biçimde gösterilmelidir.

Önerilen görünüm:

genel durum kartı

provider durumu

storage/log durumu

pipeline durumu

rewrite durumu

seo durumu

kritik hatalar listesi

warning listesi

Kural

Ham exception dump panelde son kullanıcıya açılmamalıdır.

11. Loglama İlkeleri

Selftest çalıştığında loglanabilir:

başlama zamanı

bitiş zamanı

toplam check sayısı

kritik fail sayısı

hangi grup çöktü

Ama gereksiz ayrıntı ve hassas veri loglanmamalıdır.

12. Üretim Seviyesi Zorunlu Check Seti

Üretime yakın bir modülde en az şu testler zorunludur:

settings.json okunuyor

providers.json okunuyor

registry provider döndürüyor

en az bir article_generate route mevcut

en az bir rewrite route mevcut

news-sources.json parse oluyor

logs klasörü yazılabilir

storage klasörü yazılabilir

test provider çağrısı en az bir sağlayıcıda geçiyor

13. Bu Belgenin Rolü

Bu belge selftest sisteminin resmi davranış tanımıdır.

Şu soruların cevabı burada bulunmalıdır:

selftest neyi test eder?

hangi grup zorunlu?

sonuç shape’i ne?

health.json ne işe yarar?

false negative nasıl önlenir?

14. Son Hüküm

Selftest, AI Article Generator modülünün erken uyarı sistemidir.

Doğru selftest:

bozuk bootstrap’ı yakalar

provider sorunlarını görünür kılar

pipeline/rewrite kopukluklarını erken fark eder

üretim güvenini artırır

Zayıf selftest:

yanlış güven verir

gerçek problemleri saklar

debug maliyetini büyütür
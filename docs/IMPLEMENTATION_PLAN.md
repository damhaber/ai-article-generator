# AI Article Generator – Implementation Plan
Version: 6.0 Draft Stabilization
Status: Active Plan
Location: masal-panel/modules/ai-article-generator/docs/IMPLEMENTATION_PLAN.md

---

## 1. Amaç

Bu belge modülün stabilizasyon ve yeniden yapılandırma sırasını tanımlar.

Amaç:
- rastgele patch üretmek yerine
- kontrollü, sıralı, dosya-temelli bir iyileştirme planı oluşturmak

---

## 2. Mevcut Ana Sorunlar

Bugünkü ana sorun kümeleri:
1. bootstrap / include sırası belirsizliği
2. V6 pipeline ile legacy generate contract uyuşmazlığı
3. rewrite service ile cleanup pipeline karışıklığı
4. provider registry / router / gateway zincirinin tam resmileşmemesi
5. response shape birliği eksikliği
6. selftest’in yanlış negatif üretebilmesi
7. UI’nin backend eksiklerini yamamaya çalışması

---

## 3. Uygulama İlkeleri

### İlke 1
Patch mantığıyla ilerlemek yerine dosya bazlı tam düzeltme yapılır.

### İlke 2
Docs her aşamada güncellenir.

### İlke 3
Önce contract ve yükleme sırası düzeltilir; sonra ince kalite işleri yapılır.

### İlke 4
Legacy yapı tamamen silinmez; adaptere indirilir.

---

## 4. Fazlar

### Faz 0 — Saf Analiz
Çıktılar:
- dosya haritası
- çakışma noktaları
- eski/yeni mimari ayrımı
- kritik contract kopuklukları

### Faz 1 — Docs / Source of Truth
Çıktılar:
- architecture
- pipeline
- providers
- gateway
- router
- selftest
- quality
- security
- panel docs

### Faz 2 — Bootstrap ve Contract Stabilizasyonu
Öncelik:
- ai-article-generator.php loader sırası
- response shape standardı
- registry boot
- core dependency sırası

### Faz 3 — Article Üretim Omurgası
Öncelik:
- article-service.php
- ai-article-pipeline.php
- ai-article-context.php
- ai-article-outline.php

### Faz 4 — Rewrite Omurgası
Öncelik:
- rewrite-service.php
- rewrite-pipeline.php
- ajax rewrite endpoint standardı

### Faz 5 — Router / Gateway / Provider Resmileştirme
Öncelik:
- provider registry
- router task policy
- gateway normalize contract
- provider response standardı

### Faz 6 — SEO ve Quality
Öncelik:
- seo-service
- seo-pipeline
- quality scoring
- panel quality gösterimi

### Faz 7 — Selftest ve Health
Öncelik:
- selftest-service
- health.json
- panel health kartları

### Faz 8 — UI Sadeleştirme
Öncelik:
- editor.js’nin savunmacı fazlalıklarını azaltmak
- panel/business logic ayrımını netleştirmek

---

## 5. İlk Yeniden Yazım Paketi

Birlikte ele alınması gereken ilk dosyalar:
- ai-article-generator.php
- ajax-handler.php
- core/services/article-service.php
- core/ai-article-pipeline.php
- core/services/rewrite-service.php
- core/services/selftest-service.php

### Neden birlikte?
Çünkü bunlar aynı contract zincirini paylaşır.

---

## 6. İkinci Paket

- core/ai-article-provider-registry.php
- core/ai-article-router.php
- core/ai-article-gateway.php
- core/ai-article-llm.php
- core/providers/*

---

## 7. Üçüncü Paket

- core/news/*
- core/ai-article-context.php
- core/ai-article-outline.php
- core/pipelines/seo-pipeline.php
- core/services/seo-service.php
- core/ai-article-quality.php

---

## 8. Dördüncü Paket

- panel.php
- ui/editor.js
- ui/settings.php
- integrations/*
- docs güncellemeleri

---

## 9. Başarı Kriterleri

Stabilizasyon başarılı sayılmak için:
- article generate tek contract ile dönmeli
- rewrite gerçek rewrite yapmalı
- Türkçe article’da İngilizce kalıntı minimuma inmeli
- provider failover görünür meta üretmeli
- selftest false negative üretmemeli
- UI ekstra shape yamamak zorunda kalmamalı

---

## 10. Bu Belgenin Rolü

Bu belge teknik yeniden yapılandırma sırasının resmi planıdır.

Kod yazımında hangi dosyaların hangi sırayla ele alınacağını belirler.

RESMİ LOADER SIRASI
ai-article-generator.php için mimari yükleme planı

Burası çok kritik. Şu anki problemlerin büyük kısmı burada başlıyor.
Aşağıdaki sıra, modülün resmi bootstrap sırası olmalı.

1. Foundation

Önce temel sabitler ve çekirdek yardımcılar yüklenmeli:

ai-article-generator.php
core/ai-log.php
core/ai-article-settings.php
core/ai-article-usage.php
core/ai-article-metrics.php
Neden?

Çünkü geri kalan her şey:

log,

settings,

usage,

metrics

katmanlarına dayanır.

2. Provider Temeli

Sonra provider altyapısı açılmalı:

core/providers/provider-interface.php
core/providers/provider-base-openai-compat.php
core/providers/provider-openai.php
core/providers/provider-groq.php
core/providers/provider-gemini.php
core/providers/provider-deepseek.php
core/providers/provider-mistral.php
core/providers/provider-ollama.php
core/providers/provider-openrouter.php
core/ai-article-provider-registry.php
Neden?

Router/gateway, provider’ları hazır görmeli.

3. Routing + Gateway + LLM

Sonra karar ve çağrı zinciri:

core/ai-article-router.php
core/ai-article-gateway.php
core/ai-article-llm.php
core/ai-article-core.php
Not

ai-article-core.php artık ana orkestrasyon sahibi değil; low-level adapter olmalı.

4. News / Context / Outline

Sonra haber ve içerik hazırlık katmanı:

core/news/news-helpers.php
core/news/news-cache.php
core/news/news-sources.php
core/news/news-normalizer.php
core/news/news-collector.php
core/news/news-fact-pack.php
core/ai-article-context.php
core/ai-article-outline.php
core/ai-article-templates.php
Neden?

Pipeline, context ve outline hazır olmadan çalışmamalı.

5. Pipelines

Sonra iş akışı dosyaları:

core/pipelines/rewrite-pipeline.php
core/pipelines/seo-pipeline.php
core/ai-article-pipeline.php
6. SEO Katmanı

Sonra SEO motoru:

core/seo/meta-builder.php
core/seo/schema-builder.php
core/seo/faq-builder.php
core/seo/seo-engine.php
7. Services

Sonra iş mantığı servisleri:

core/services/media-service.php
core/services/seo-service.php
core/services/rewrite-service.php
core/services/article-service.php
core/services/selftest-service.php
Neden bu sıra?

article-service diğer servisleri kullanabilir

selftest en sonda hepsini test etmeli

8. Destekleyici Legacy / Bridge Katmanı

Sonra destek dosyaları:

core/ai-article-media.php
core/ai-article-post.php
core/ai-article-quality.php
core/ai-article-internal-links.php
core/ai-article-engines.php
core/ai-article-bridge.php
core/ai-article-selftest.php
core/ai-article-devnotes.php
core/ai-article-queue.php
Not

Bunlar merkez değil, destek katmanı.

9. Integrations + UI + Actions

En son dış yüz ve panel:

integrations/ai-language-hook.php
integrations/ai-rewrite-hook.php
integrations/ai-seo-hook.php
integrations/ai-sources-hook.php
integrations/rest-api.php
integrations/api-keys-panel.php
ajax-handler.php
panel.php
ui/settings.php
Çok kritik bootstrap kuralları
Kural 1

Hiçbir dosya kendinden önce yüklenmesi gereken altyapıyı varsayamaz.

Kural 2

Registry yüklenmeden router/gateway çalışmamalı.

Kural 3

Settings yüklenmeden provider config okunmamalı.

Kural 4

Context/news yüklenmeden pipeline article üretmeye başlamamalı.

Kural 5

Service katmanı yüklenmeden AJAX business logic taşımamalı.

Kural 6

Panel/UI hiçbir zaman bootstrap eksiklerini yamamaya çalışmamalı.


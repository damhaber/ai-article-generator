# AI Article Generator – Which File Does What
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/WHICH_FILE_WHAT.md

---

## 1. Amaç

Bu belge, modüldeki ana dosyaların sorumluluklarını açık ve bağlayıcı şekilde tanımlar.

Amaç:
- dosya görevlerinin çakışmasını önlemek
- “bu işi hangi dosya yapmalı?” sorusunu netleştirmek
- legacy ve V6 rolleri ayırmak
- yeniden yazım sırasında sorumluluk kaymasını önlemek

Bu belge kısa açıklama listesi değildir.  
Bu belge dosya sorumluluk sözleşmesidir.

---

## 2. Ana Kural

Her dosya:
- birincil bir role sahip olmalı
- ikincil destek roller taşıyabilir
- ama başka katmanın ana görevini üstlenmemelidir

Örnek:
- router karar verir
- gateway çağrı yapar
- provider dış API ile konuşur

Bu sınırlar bozulursa sistem dağılır.

---

## 3. Root Düzeyi Dosyalar

### ai-article-generator.php
**Tür:** module bootstrap / loader

**Birincil görevleri:**
- modül sabitlerini tanımlamak
- temel path/storage yapılarını hazırlamak
- yükleme sırasını yönetmek
- core/service/integration/UI zincirini doğru sırada açmak

**Yapmaması gerekenler:**
- makale üretmek
- rewrite yapmak
- provider seçmek
- panel HTML mantığını taşımak

**Not:**
Bu dosya plugin bootstrap gibi değil, **module loader** gibi düşünülmelidir.

---

### ajax-handler.php
**Tür:** action/AJAX giriş kapısı

**Birincil görevleri:**
- request almak
- nonce/capability/input temel doğrulaması yapmak
- doğru service çağrısını tetiklemek
- standard response dönmek

**Yapmaması gerekenler:**
- prompt üretmek
- provider route seçmek
- article/rewrite fallback zinciri kurmak
- business logic taşımak

**Kritik not:**
AJAX katmanı ince olmalı.  
Kalınlaştıkça mimari koku oluşur.

---

### panel.php
**Tür:** panel entry / admin UI shell

**Birincil görevleri:**
- panel sekmelerini ve genel yerleşimi oluşturmak
- UI bloklarını çağırmak
- kullanıcıya araç yüzeyi sunmak

**Yapmaması gerekenler:**
- service yerine geçmek
- article pipeline yönetmek
- rewrite mantığı taşımak

---

### db-setup.php
**Tür:** yardımcı setup katmanı

**Birincil görevleri:**
- gerekiyorsa DB tabanlı yardımcı yapı/legacy setup görevleri

**Yapmaması gerekenler:**
- article logic
- provider logic
- panel logic

**Not:**
Bu modül JSON-first ise DB yalnızca sınırlı destek katmanı olmalıdır.

---

### ai-article-generator.json
**Tür:** module metadata/config descriptor

**Birincil görevleri:**
- modül tanımı
- build/version metadata
- panel veya loader için tanımsal veri sağlama

**Yapmaması gerekenler:**
- runtime business logic kaynağı gibi davranmak

---

## 4. Core Foundation Dosyaları

### core/ai-log.php
**Birincil görevleri:**
- standart log yazımı
- log level desteği
- bağlam verisini güvenli yazma

**Yapmaması gerekenler:**
- selftest kararı vermek
- error recovery yönetmek
- UI render etmek

---

### core/ai-article-settings.php
**Birincil görevleri:**
- settings.json okuma/yazma
- ayar normalize etme
- varsayılan ayarları sağlama

**Yapmaması gerekenler:**
- provider çağrısı
- article üretimi
- router kararı

---

### core/ai-article-usage.php
**Birincil görevleri:**
- kullanım metrikleri
- rate/usage verilerinin yazımı
- provider/model kullanım özeti

**Yapmaması gerekenler:**
- doğrudan rate limit enforcement’in tüm kararlarını tek başına vermek
- article pipeline yürütmek

---

### core/ai-article-metrics.php
**Birincil görevleri:**
- performans/ölçüm metrikleri
- süre, sayım, event ölçümü

**Yapmaması gerekenler:**
- kalite kararı
- route kararı
- UI mantığı

---

## 5. Provider / Routing / Gateway Dosyaları

### core/ai-article-provider-registry.php
**Tür:** provider registry

**Birincil görevleri:**
- provider kayıtlarını tutmak
- provider instance vermek
- provider availability temel görünümü sunmak

**Yapmaması gerekenler:**
- route seçmek
- task puanı hesaplamak
- article üretmek

---

### core/ai-article-router.php
**Tür:** task → provider/model karar motoru

**Birincil görevleri:**
- task tipine uygun provider/model seçmek
- fallback zinciri belirlemek
- quality/cost/speed dengesine göre karar vermek

**Yapmaması gerekenler:**
- HTTP çağrısı yapmak
- prompt üretmek
- article parse etmek

---

### core/ai-article-gateway.php
**Tür:** normalized AI call executor

**Birincil görevleri:**
- provider’a güvenli çağrı yapmak
- timeout/retry uygulamak
- response normalize etmek
- usage ve error standardize etmek

**Yapmaması gerekenler:**
- model seçmek
- article outline üretmek
- SEO kararı vermek

---

### core/ai-article-llm.php
**Tür:** higher-level LLM helper facade

**Birincil görevleri:**
- gateway’i daha üst düzey yardımcı API’ye çevirmek
- text generation / rewrite / seo block generation gibi ortak fonksiyonlar sunmak

**Yapmaması gerekenler:**
- AJAX bilmek
- panel bilmek
- post save akışına sahip olmak

---

### core/ai-article-core.php
**Tür:** low-level legacy adapter / generation helper

**Birincil görevleri:**
- düşük seviye generate yardımcıları
- legacy uyumluluk

**Yapmaması gerekenler:**
- modülün ana article orchestration merkezi olmak

**Kritik not:**
Bu dosyanın rolü küçültülmeli.  
Merkez artık service + pipeline olmalı.

---

## 6. Provider Sınıfları

### core/providers/provider-interface.php
**Birincil görevleri:**
- tüm providerlar için ortak sözleşme tanımlamak

---

### core/providers/provider-base-openai-compat.php
**Birincil görevleri:**
- OpenAI-benzeri sağlayıcılar için ortak altyapı sağlamak

---

### core/providers/provider-openai.php
### core/providers/provider-groq.php
### core/providers/provider-gemini.php
### core/providers/provider-deepseek.php
### core/providers/provider-mistral.php
### core/providers/provider-ollama.php
### core/providers/provider-openrouter.php

**Birincil görevleri:**
- ilgili provider API’si ile konuşmak
- request/response map etmek
- provider-specific davranışları kapsamak

**Yapmaması gerekenler:**
- article logic
- route logic
- panel logic

---

## 7. News Katmanı

### core/news/news-sources.php
**Birincil görevleri:**
- `data/news-sources.json` erişimi
- kaynak tanımlarını sunmak
- kategori bazlı kaynak listesi döndürmek

**Yapmaması gerekenler:**
- HTTP haber toplama
- article yazımı

---

### core/news/news-cache.php
**Birincil görevleri:**
- cache oku/yaz
- haber cache geçerliliği kontrolü

**Yapmaması gerekenler:**
- provider veya article logic

---

### core/news/news-collector.php
**Birincil görevleri:**
- RSS/API/harici kaynaklardan veri toplamak

**Yapmaması gerekenler:**
- article body üretmek
- SEO üretmek
- route kararı vermek

---

### core/news/news-normalizer.php
**Birincil görevleri:**
- farklı kaynaklardan gelen haberleri ortak şemaya çevirmek

**Yapmaması gerekenler:**
- editorial yorum üretmek
- prompt kurmak

---

### core/news/news-fact-pack.php
**Birincil görevleri:**
- normalize haberlerden fact pack çıkarmak
- entity/keyword/event yoğunluğunu artırmak

**Yapmaması gerekenler:**
- doğrudan article body üretmek
- provider çağırmak

---

### core/news/news-helpers.php
**Birincil görevleri:**
- ortak yardımcı fonksiyonlar

**Yapmaması gerekenler:**
- ana business logic’i burada gizlemek

---

## 8. Context / Outline / Templates

### core/ai-article-context.php
**Birincil görevleri:**
- topic/category/news/fact pack verisini birleştirip context pack üretmek

**Yapmaması gerekenler:**
- provider seçmek
- article body generate etmek

**Not:**
V6 kalitesinin en kritik dosyalarından biridir.

---

### core/ai-article-outline.php
**Birincil görevleri:**
- içerik iskeleti oluşturmak
- template bazlı section planı üretmek

**Yapmaması gerekenler:**
- tam makale yazmak
- SEO üretmek

---

### core/ai-article-templates.php
**Birincil görevleri:**
- template tanımları
- içerik tipine göre structure/prompt yardımcıları

**Yapmaması gerekenler:**
- article orchestration merkezi olmak

---

## 9. Pipeline Dosyaları

### core/ai-article-pipeline.php
**Tür:** ana article orchestration dosyası

**Birincil görevleri:**
- normalize input almak
- context üretmek
- outline oluşturmak
- prompt/messages hazırlamak
- router/gateway çağrısını koordine etmek
- output parse etmek
- rewrite/seo sonrası final article object döndürmek

**Yapmaması gerekenler:**
- nonce kontrolü
- panel output üretimi
- provider registry yerine geçmek

**Kritik not:**
Bugünkü ana contract kopuklukları bu dosyada yoğunlaşıyor.

---

### core/pipelines/rewrite-pipeline.php
**Tür:** rewrite postprocess / cleanup pipeline

**Birincil görevleri:**
- cleanup
- heading restore
- html koruma
- küçük format düzeltmeleri

**Yapmaması gerekenler:**
- gerçek rewrite service’in yerine geçmek

---

### core/pipelines/seo-pipeline.php
**Tür:** seo enrichment pipeline

**Birincil görevleri:**
- makale sonrası seo enrichment akışı

**Yapmaması gerekenler:**
- makale generate etmek
- provider routing merkezi olmak

---

## 10. SEO Dosyaları

### core/seo/meta-builder.php
**Birincil görevleri:**
- meta title / meta description üretim yardımcıları

---

### core/seo/schema-builder.php
**Birincil görevleri:**
- structured data / schema üretmek

---

### core/seo/faq-builder.php
**Birincil görevleri:**
- içerikten FAQ üretmek

---

### core/seo/seo-engine.php
**Birincil görevleri:**
- seo yardımcılarını birleştirmek
- SEO output akışını toplamak

**Yapmaması gerekenler:**
- article generate etmek

---

## 11. Service Dosyaları

### core/services/article-service.php
**Tür:** article use-case service

**Birincil görevleri:**
- makale üretim isteğinin tek giriş kapısı olmak
- settings ve feature flag kontrolü yapmak
- pipeline’ı çağırmak
- final response contract’ı döndürmek

**Yapmaması gerekenler:**
- provider-level HTTP logic
- panel render

---

### core/services/rewrite-service.php
**Tür:** rewrite use-case service

**Birincil görevleri:**
- gerçek rewrite isteğini almak
- rewrite route seçtirmek
- LLM rewrite çağrısı yaptırmak
- cleanup/postprocess uygulamak
- final rewrite response dönmek

**Yapmaması gerekenler:**
- yalnız cleanup yapıp rewrite olmuş gibi davranmak

---

### core/services/seo-service.php
**Tür:** seo use-case service

**Birincil görevleri:**
- seo üretimini merkezi yürütmek
- meta/faq/schema/keywords çıktısını toplamak

---

### core/services/media-service.php
**Tür:** media support service

**Birincil görevleri:**
- makaleye bağlı medya hazırlığı
- hero/cover/görsel önerisi verileri

---

### core/services/selftest-service.php
**Tür:** health/selftest service

**Birincil görevleri:**
- modül sağlık raporu üretmek
- provider/router/gateway/news/storage zincirini test etmek

**Yapmaması gerekenler:**
- yanlış sabit/fonksiyon isimleri bekleyerek false negative üretmek

---

## 12. Yardımcı / Legacy / Bridge Dosyaları

### core/ai-article-bridge.php
**Birincil görevleri:**
- legacy veya entegrasyon köprüsü olmak

**Yapmaması gerekenler:**
- resmi ana omurga olmak

---

### core/ai-article-engines.php
**Birincil görevleri:**
- farklı üretim motorlarını veya stratejileri temsil eden yardımcı yapı

**Not:**
Merkezi iş mantığı burada dağılmamalı.

---

### core/ai-article-media.php
**Birincil görevleri:**
- media helper görevleri

---

### core/ai-article-post.php
**Birincil görevleri:**
- WordPress/post save köprüsü
- article object’ten post veri hazırlama

**Yapmaması gerekenler:**
- article generate etmek

---

### core/ai-article-quality.php
**Birincil görevleri:**
- kalite skoru ve kalite bayrakları üretmek

---

### core/ai-article-internal-links.php
**Birincil görevleri:**
- internal link önerileri / eşleştirmeleri

---

### core/ai-article-selftest.php
**Birincil görevleri:**
- legacy selftest helper
- compatibility amaçlı destek

---

### core/ai-article-devnotes.php
**Birincil görevleri:**
- geliştirici notları / debug yardımcıları

**Kural:**
Üretim akışı bu dosyaya bağlı olmamalı.

---

### core/ai-article-queue.php
**Birincil görevleri:**
- gelecekte queue/batch processing desteği
- arka plan görev mantığı için helper

---

## 13. Integration Dosyaları

### integrations/ai-language-hook.php
**Birincil görevleri:**
- dil ile ilgili entegrasyon hook’ları

---

### integrations/ai-rewrite-hook.php
**Birincil görevleri:**
- rewrite ile ilgili entegrasyon noktaları

---

### integrations/ai-seo-hook.php
**Birincil görevleri:**
- seo entegrasyon hook’ları

---

### integrations/ai-sources-hook.php
**Birincil görevleri:**
- kaynak verisi / source entegrasyonu

---

### integrations/api-keys-panel.php
**Birincil görevleri:**
- provider anahtarları panel entegrasyonu

---

### integrations/rest-api.php
**Birincil görevleri:**
- gerektiğinde kontrollü REST entegrasyonu sunmak

**Yapmaması gerekenler:**
- iç helper’ları kontrolsüz public API’ye çevirmek

---

## 14. UI Dosyaları

### ui/settings.php
**Birincil görevleri:**
- ayar ekranı render etmek

---

### ui/editor.js
**Birincil görevleri:**
- generate/rewrite/seo/selftest AJAX çağrılarını tetiklemek
- preview güncellemek
- loading ve hata durumlarını göstermek

**Yapmaması gerekenler:**
- backend response shape icat etmek
- core eksiklerini JS ile yamamak
- provider route logic taşımak

---

### ui/style.css
**Birincil görevleri:**
- panel görünümü

---

### ui/components/*
**Birincil görevleri:**
- arayüz bileşenleri

---

## 15. Storage ve Data Dosyaları

### data/news-sources.json
**Birincil görevleri:**
- haber kaynak tanımları

---

### data/news-cache/*
**Birincil görevleri:**
- haber cache verisi

---

### storage/settings.json
**Birincil görevleri:**
- modül ayarları

---

### storage/providers.json
**Birincil görevleri:**
- provider config

---

### storage/models.json
**Birincil görevleri:**
- model capability/config

---

### storage/router.json
**Birincil görevleri:**
- task bazlı router politikaları

---

### storage/prompt-presets.json
**Birincil görevleri:**
- prompt presetleri

---

### storage/feature-map.json
**Birincil görevleri:**
- aktif özellik haritası

---

### storage/health.json
**Birincil görevleri:**
- son sağlık özeti

---

### storage/usage/*
**Birincil görevleri:**
- kullanım kayıtları

---

## 16. Logs

### logs/ai-article-generator.log
**Birincil görevleri:**
- genel çalışma logu

### logs/news.log
**Birincil görevleri:**
- news katmanı logları

### logs/collector.log
**Birincil görevleri:**
- collector detay logları

---

## 17. Son Hüküm

Bu modülde resmi merkez dosyalar şunlardır:

- ai-article-generator.php
- ajax-handler.php
- core/services/article-service.php
- core/services/rewrite-service.php
- core/services/seo-service.php
- core/services/selftest-service.php
- core/ai-article-pipeline.php
- core/ai-article-context.php
- core/ai-article-outline.php
- core/ai-article-router.php
- core/ai-article-gateway.php
- core/ai-article-provider-registry.php

Bunların dışındaki dosyalar:
- destek katmanı
- specialized helper
- entegrasyon
- UI
- legacy bridge

olarak düşünülmelidir.

Bu ayrım korunursa sistem sürdürülebilir olur.
Bu ayrım bozulursa mimari tekrar sürüklenmeye başlar.

DOSYA DOSYA YENİDEN YAZIM ÖNCELİK MATRİSİ

Şimdi en kritik pratik plana geçiyorum.
Burada her dosyayı şu sınıflardan biriyle işaretliyorum:

P1 = hemen yeniden yazılmalı

P2 = güçlü refactor gerekli

P3 = contract temizliği / sadeleştirme

P4 = şimdilik korunabilir

P5 = legacy/support, sonra bakılır

P1 — Hemen yeniden yazılmalı
1. core/ai-article-pipeline.php

Neden P1?
Ana contract kopukluğu burada:

context/outline var

prompt payload var

ama gerçek prompt/messages zinciri eksik

fallback çok erken devreye giriyor

Hedef:

gerçek V6 orchestration

tek final response

router/gateway entegrasyonu net

2. core/services/article-service.php

Neden P1?
Makale üretiminin tek giriş kapısı olması gerekiyor.
Bu netleşmeden AJAX ve panel sadeleşmez.

Hedef:

input normalize

feature/settings kontrolü

pipeline çağrısı

final response contract

3. core/services/rewrite-service.php

Neden P1?
Şu an rewrite ile cleanup karışmış durumda.
Kullanıcı rewrite isterken gerçek rewrite olmuyor.

Hedef:

gerçek LLM rewrite

cleanup ayrı aşama

rewrite response standardı

4. ajax-handler.php

Neden P1?
Şu an fazla şey biliyor.
Service varsa onu, yoksa legacy’yi, sonra başka fallback’i çağıran dağınık bir merkez olmamalı.

Hedef:

çok ince action layer

service merkezli akış

standard response

5. ai-article-generator.php

Neden P1?
Loader sırası düzelmeden diğer her şey kısmen bozuk kalabilir.

Hedef:

resmi bootstrap order

foundation → providers → router/gateway → news/context → pipelines → services → UI/integrations

6. core/services/selftest-service.php

Neden P1?
Yanlış negatif üreten bir selftest, tüm sistemi yanlış yönlendirir.

Hedef:

gerçek runtime contract testleri

doğru fonksiyon/sabit adları

health.json uyumu

P2 — Güçlü refactor gerekli
7. core/ai-article-router.php

Neden P2?
Task bazlı route seçimi resmileştirilmeli.

Hedef:

task-aware routing

quality/cost/speed

fallback chain üretimi

8. core/ai-article-gateway.php

Neden P2?
Response normalize, retry, timeout ve hata tekilleştirme burada netleşmeli.

Hedef:

tek gateway contract

provider farklarını gizleme

9. core/ai-article-provider-registry.php

Neden P2?
Yarım bağlı registry, provider sistemini zayıflatır.

Hedef:

merkezi provider kayıt sistemi

guaranteed boot

10. core/ai-article-llm.php

Neden P2?
LLM helper katmanı sade ve net olmalı.

Hedef:

gateway üzerinde yüksek seviyeli helper API

legacy karmaşadan ayrışma

11. core/news/news-collector.php

Neden P2?
Collector çalışıyor olabilir ama runtime/contract tarafı çok net olmalı.

Hedef:

deterministik collect sonucu

iyi log

source/category uyumu

12. core/ai-article-context.php

Neden P2?
V6’nın kalbi burada. Çok önemli.

Hedef:

context pack standardı

fact/source/entity/keyword yoğunluğu

prompt-ready context

P3 — Contract temizliği / sadeleştirme
13. core/ai-article-outline.php

Hedef:

template-aware outline

sadece iskelet üretmesi

body generate etmeye kaymaması

14. core/pipelines/rewrite-pipeline.php

Hedef:

rol netleştirme

gerçek rewrite değil cleanup/postprocess olması

15. core/pipelines/seo-pipeline.php

Hedef:

article sonrası enrichment contract’ı

service ile uyum

16. core/services/seo-service.php

Hedef:

meta/faq/schema/keywords tek merkezden

17. core/seo/seo-engine.php

Hedef:

seo yardımcılarını toplamak

dağınık logic’i azaltmak

18. ui/editor.js

Hedef:

savunmacı shape yamalarını azaltmak

backend contract’ına güvenen sade yapı

19. panel.php

Hedef:

UI shell olarak kalması

business logic taşımaması

P4 — Şimdilik korunabilir, sonra iyileştirilir
20. core/providers/provider-interface.php
21. core/providers/provider-base-openai-compat.php
22. core/providers/provider-openai.php
23. core/providers/provider-groq.php
24. core/providers/provider-gemini.php
25. core/providers/provider-deepseek.php
26. core/providers/provider-mistral.php
27. core/providers/provider-ollama.php
28. core/providers/provider-openrouter.php

Neden P4?
Temel sözleşme netleşince bu dosyalarda uyarlama yapılır.
Ama ana sorun çoğu zaman burada değil, üst contract katmanındadır.

29. core/news/news-normalizer.php
30. core/news/news-fact-pack.php
31. core/news/news-sources.php
32. core/news/news-cache.php
33. core/news/news-helpers.php

Neden P4?
Genelde destekleyici ve daha izole katmanlar.
Ama context standardı netleşince gerektiği kadar dokunulur.

34. core/ai-article-quality.php
35. core/ai-article-metrics.php
36. core/ai-article-usage.php

Neden P4?
Ana omurga düzeldikten sonra daha doğru çalışırlar.

P5 — Legacy / support / sonra bakılır
37. core/ai-article-bridge.php
38. core/ai-article-engines.php
39. core/ai-article-media.php
40. core/ai-article-post.php
41. core/ai-article-internal-links.php
42. core/ai-article-selftest.php
43. core/ai-article-devnotes.php
44. core/ai-article-queue.php
45. integrations/*
46. ui/settings.php
47. ui/style.css
48. ui/components/*

Neden P5?
Bunlar ana contract kırığı oluşturan ilk dosyalar değil.
Ana omurga düzelmeden bunlara saldırmak zaman kaybı olur.

İLK GERÇEK YENİDEN YAZIM PAKETİ

Benim teknik kararım şu:

Paket 1 — Omurga Paketi

Bu dosyalar birlikte ele alınmalı:

ai-article-generator.php

ajax-handler.php

core/services/article-service.php

core/ai-article-pipeline.php

core/services/rewrite-service.php

core/services/selftest-service.php

Neden birlikte?

Çünkü bunlar:

giriş kapısı

bootstrap

üretim contract’ı

rewrite contract’ı

health contract’ı

aynı anda paylaşır.

Bunlardan yalnız biri düzeltilirse sistem yine yamalı kalır.

PAKET 2 — AI ÇAĞRI OMURGASI

core/ai-article-router.php

core/ai-article-gateway.php

core/ai-article-provider-registry.php

core/ai-article-llm.php

Amaç

gerçek çoklu provider kararı

normalize gateway

registry resmi hale gelsin

PAKET 3 — CONTEXT / NEWS / OUTLINE

core/ai-article-context.php

core/ai-article-outline.php

core/news/news-collector.php

core/news/news-normalizer.php

core/news/news-fact-pack.php

Amaç

article kalitesinin gerçek yükselişi

PAKET 4 — SEO / QUALITY / PANEL TEMİZLİĞİ

core/services/seo-service.php

core/pipelines/seo-pipeline.php

core/ai-article-quality.php

panel.php

ui/editor.js

NET SONUÇ

Şu anda elimizde artık sadece analiz değil, uygulanabilir savaş planı var:

Docs tarafında tamamlanan çekirdek

Architecture

Pipeline

Providers

Gateway

Failover

Router Algorithm

Selftest

Security

Quality

Panel Architecture

Implementation Plan

Which File What

Teknik karar

Bu modülün resmi omurgası V6 service/pipeline/news/router/gateway yapısı olacak

legacy parçalar adaptere inecek

UI ve AJAX inceltilecek

response contract tekleşecek
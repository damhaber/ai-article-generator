# AI Article Generator – Architecture
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/ARCHITECTURE.md

---

## 1. Amaç

AI Article Generator, Masal Panel içinde çalışan bir **içerik üretim modülüdür**.  
Bu yapı bir WordPress plugin olarak değil, **panel tarafından yüklenen modüler bir çalışma birimi** olarak ele alınmalıdır.

Modülün amacı yalnızca “bir prompt gönderip içerik almak” değildir.

Bu modülün hedefi:

- haber toplamak
- haberleri normalize etmek
- fact pack üretmek
- bağlam (context) oluşturmak
- makale iskeleti çıkarmak
- uygun model/sağlayıcı seçmek
- LLM üzerinden içerik üretmek
- yeniden yazmak / parlatmak
- SEO çıktıları oluşturmak
- medya ve yayınlama sürecine veri hazırlamak

Yani bu modül pratikte bir:

**AI Newsroom Engine / AI Editorial Engine**

olarak düşünülmelidir.

---

## 2. Bu Yapı Nedir, Ne Değildir?

### Bu yapı nedir?
- Masal Panel içindeki bir modüldür
- JSON-first çalışan bir içerik motorudur
- Çoklu AI sağlayıcıyı destekleyen bir üretim altyapısıdır
- Haber + LLM + SEO + yayın akışını birleştiren bir çekirdektir

### Bu yapı ne değildir?
- Basit tek dosyalı bir makale yazıcı değildir
- Sadece tek provider’a bağlı bir LLM wrapper değildir
- Salt WordPress plugin mantığıyla düşünülmemelidir
- UI içinde iş mantığı taşıyan dağınık bir sistem olmamalıdır

---

## 3. Fiziksel Konum ve Kimlik

Modül yolu:

masal-panel/modules/ai-article-generator/

Ana bootstrap dosyası:

ai-article-generator.php

Ana destek dosyaları:

- ajax-handler.php
- panel.php
- core/*
- integrations/*
- ui/*
- storage/*
- data/*
- docs/*

Bu modül Masal Panel mimarisine uymalıdır.  
Dolayısıyla burada “plugin activation lifecycle” değil, “module bootstrap lifecycle” esastır.

---

## 4. Yüksek Seviye Mimari

Genel akış:

UI / Panel
↓
AJAX / Action Layer
↓
Service Layer
↓
Pipeline Layer
↓
Context + News Layer
↓
Router Layer
↓
Gateway Layer
↓
Provider Layer
↓
LLM Model
↓
Post Process / Rewrite / SEO
↓
Output / Save / Publish

Bu akışın kırıldığı her yerde sistemde şu belirtiler ortaya çıkar:

- kısa içerik
- çıplak metin
- dil bozulması
- rewrite etkisizliği
- response alanlarının eksik gelmesi
- fallback üstüne fallback davranışı

---

## 5. Temel Mimari Katmanlar

### 5.1 UI Layer

Ana dosyalar:
- panel.php
- ui/editor.js
- ui/settings.php
- ui/style.css
- ui/components/*

Görevleri:
- kullanıcı etkileşimi
- form toplama
- önizleme
- buton / loading / durum yönetimi
- AJAX isteklerini tetikleme

UI katmanı **iş mantığı taşımaz**.

UI katmanı:
- model seçme mantığını bilmemeli
- rewrite prompt üretmemeli
- makale contract’ını tahmin ederek normalize etmemeli
- service/pipeline eksiklerini JS içinde yamamamalı

### Kural
UI yalnızca veri gönderir ve standart response okur.

---

### 5.2 Action / AJAX Layer

Ana dosya:
- ajax-handler.php

Görevleri:
- gelen isteği almak
- nonce / permission / input temel doğrulaması yapmak
- doğru servisi çağırmak
- standard response dönmek

AJAX katmanı şunları yapmamalı:
- prompt inşa etmemeli
- provider seçmemeli
- generate / rewrite fallback zinciri kurmamalı
- business logic taşımamalı

AJAX mümkün olduğunca ince kalmalıdır.

---

### 5.3 Service Layer

Ana dosyalar:
- core/services/article-service.php
- core/services/rewrite-service.php
- core/services/seo-service.php
- core/services/media-service.php
- core/services/selftest-service.php

Service katmanı iş kurallarının sahibidir.

#### article-service.php
Makale üretiminin ana giriş kapısıdır.

Sorumlulukları:
- input normalize
- settings oku
- feature flag kontrolü
- pipeline çağır
- final response hazırla

#### rewrite-service.php
Gerçek yeniden yazım servisidir.

Sorumlulukları:
- rewrite instruction al
- rewrite görev tipini belirle
- LLM’e uygun yeniden yazım isteği gönder
- cleanup / postprocess uygula
- final rewrite response dön

#### seo-service.php
SEO çıktılarının merkezi katmanıdır.

Sorumlulukları:
- meta title
- meta description
- schema
- faq
- keyword set
- SEO yardımcı verileri

#### media-service.php
Medya ile ilgili veri hazırlığı yapar.

Örnek:
- görsel önerileri
- hero media bağlamı
- kapak eşleme
- medya metadata

#### selftest-service.php
Sağlık raporu üretir.

Sorumlulukları:
- provider durumu
- storage erişimi
- route/gateway testi
- news source erişimi
- yapılandırma okuma testi
- health çıktısı üretme

### Kural
Service katmanı UI’dan bağımsızdır ama pipeline’a yakındır.

---

### 5.4 Pipeline Layer

Ana dosyalar:
- core/ai-article-pipeline.php
- core/pipelines/rewrite-pipeline.php
- core/pipelines/seo-pipeline.php

Pipeline katmanı işlem sırasının sahibidir.

#### ai-article-pipeline.php
Ana içerik üretim orkestrasyonudur.

İdeal görev zinciri:
1. request normalize
2. news/context build
3. outline build
4. prompt/messages build
5. route select
6. gateway call
7. response parse
8. rewrite/polish
9. seo enrich
10. final article object return

#### rewrite-pipeline.php
Gerçek rewrite servisinin yerine geçmez.

Bunun doğru rolü:
- cleanup
- heading restore
- html koruma
- küçük format düzeltmeleri
- postprocess polishing

#### seo-pipeline.php
Makale çıktıktan sonra SEO enrichment yapar.

Örnek:
- başlık varyasyonları
- meta description
- schema hazırlığı
- FAQ önerisi

### Kural
Pipeline katmanı karar zincirini yönetir ama nihai iş kuralı sahibi service katmanıdır.

---

### 5.5 Context + News Layer

Ana dosyalar:
- core/ai-article-context.php
- core/ai-article-outline.php
- core/news/news-sources.php
- core/news/news-collector.php
- core/news/news-cache.php
- core/news/news-normalizer.php
- core/news/news-fact-pack.php
- core/news/news-helpers.php

Bu katman V6 mimarisinin kalbidir.

#### news-sources.php
Kaynak tanımları ve `data/news-sources.json` erişimi.

#### news-cache.php
Haber cache kontrolü.

#### news-collector.php
RSS/API/diğer kaynaklardan ham haber toplama.

#### news-normalizer.php
Ham veriyi ortak şemaya çevirme.

Ortak alanlar örneği:
- title
- url
- source
- published_at
- summary
- language
- category
- image

#### news-fact-pack.php
Normalize haberlerden fact pack üretir.

Örnek:
- ana maddeler
- kişi/kurum adları
- anahtar kavramlar
- olay kümeleri
- tarihsel/bağlamsal notlar

#### ai-article-context.php
Topic + category + dil + haber verilerini birleştirip LLM için context pack üretir.

#### ai-article-outline.php
Makale iskeletini üretir.

### Kural
News katmanı makale yazmaz.  
Context katmanı provider seçmez.  
Outline katmanı gövde içerik üretmez.

---

### 5.6 Routing Layer

Ana dosya:
- core/ai-article-router.php

Görevi:
- hangi görev için hangi model/sağlayıcı kullanılacak sorusunu cevaplamak

Görev tipleri örnek:
- article_generate
- article_rewrite
- seo_generate
- title_generate
- summary_generate
- faq_generate
- schema_generate
- news_analyze
- fact_expand

Router girdileri:
- task type
- model preference
- quality ihtiyacı
- latency ihtiyacı
- maliyet politikası
- availability durumu
- override ayarları

Router çıktıları:
- provider
- model
- failover chain
- timeout önerisi
- token sınırı
- temperature / creativity profili

### Kural
Router içerik üretmez; yalnız karar verir.

---

### 5.7 Gateway Layer

Ana dosya:
- core/ai-article-gateway.php

Görevi:
- router çıktısını gerçek provider çağrısına çevirmek

Sorumlulukları:
- retry
- timeout
- exception yakalama
- provider response normalize
- usage normalize
- hata standardizasyonu

Gateway, provider farklarını yukarı katmanlardan gizler.

### Kural
Service veya pipeline katmanı provider-specific response bilmemelidir.  
Bu soyutlamayı gateway yapmalıdır.

---

### 5.8 Provider Layer

Ana dosyalar:
- core/providers/provider-interface.php
- core/providers/provider-base-openai-compat.php
- core/providers/provider-openai.php
- core/providers/provider-groq.php
- core/providers/provider-gemini.php
- core/providers/provider-deepseek.php
- core/providers/provider-mistral.php
- core/providers/provider-ollama.php
- core/providers/provider-openrouter.php
- core/ai-article-provider-registry.php

Provider katmanı dış AI sistemleriyle konuşur.

#### provider-interface.php
Tüm providerların uygulaması gereken temel sözleşme.

#### provider-base-openai-compat.php
OpenAI-benzeri API kullanan sağlayıcılar için ortak temel sınıf / yardımcı katman.

#### provider-registry.php
Aktif provider listesini ve provider örneklerini yönetir.

Provider katmanı:
- HTTP çağrısı yapabilir
- request/response map edebilir
- auth header kullanabilir

Ama şunları yapmamalı:
- news toplama
- makale outline kararı
- SEO üretimi
- panel logic

---

### 5.9 LLM Utility Layer

Ana dosyalar:
- core/ai-article-llm.php
- core/ai-article-core.php

#### ai-article-llm.php
Gateway’i daha üst seviyeli bir yardımcı katman olarak sarar.

Örnek görevler:
- llm_generate_text
- llm_rewrite_text
- llm_generate_seo_block
- llm_generate_title

#### ai-article-core.php
Eski/legacy davranışları taşıyan düşük seviye adapter olabilir.

Ama yeni mimaride bu dosyanın rolü küçültülmelidir.

### Kritik not
Bu dosya artık ana orkestrasyon sahibi olmamalıdır.  
Asıl akış service + pipeline merkezli olmalıdır.

---

### 5.10 SEO Layer

Ana dosyalar:
- core/seo/meta-builder.php
- core/seo/schema-builder.php
- core/seo/faq-builder.php
- core/seo/seo-engine.php
- core/services/seo-service.php

Görevi:
- makale üretiminden sonra SEO verisi hazırlamak
- içerik ile SEO alanlarını uyumlu hale getirmek
- schema üretmek
- meta açıklama üretmek
- FAQ bloğu önermek

SEO katmanı makale üretiminin yerine geçmez.  
Makale sonrası enrichment katmanıdır.

---

### 5.11 Storage + Data Layer

Ana yollar:
- data/news-sources.json
- data/news-cache/
- storage/settings.json
- storage/providers.json
- storage/models.json
- storage/router.json
- storage/prompt-presets.json
- storage/feature-map.json
- storage/health.json
- storage/usage/

Bu modül JSON-first çalışmalıdır.

### Storage amacı
- ayar saklama
- provider durumu saklama
- model tercihleri
- route politikası
- prompt presetleri
- health raporu
- usage ve limit takibi

### Kural
Bu dosyalar:
- bootstrap sırasında okunabilir olmalı
- selftest ile doğrulanmalı
- panel ile güncellenebilir olmalı
- mümkün olduğunca tek şemaya bağlı olmalı

---

### 5.12 Logs Layer

Ana yollar:
- logs/ai-article-generator.log
- logs/news.log
- logs/collector.log

Logların amacı:
- hata yakalamak
- akış izlemek
- selftest ve runtime sorunlarını görünür kılmak

Loglar şunları içermeli:
- olay adı
- zaman
- seviye
- bağlam verisi
- hatanın kaynağı

Loglar debug için vardır, iş mantığı yerine geçmez.

---

## 6. Bootstrap / Loader İlkeleri

Ana loader:
- ai-article-generator.php

Bu dosya için temel yükleme ilkesi şudur:

### Önce foundation
- path sabitleri
- log
- settings
- usage
- metrics

### Sonra provider altyapısı
- interface
- base provider
- provider sınıfları
- provider registry

### Sonra router/gateway/llm
- router
- gateway
- llm
- core adapter

### Sonra news/context
- helpers
- cache
- sources
- collector
- normalizer
- fact-pack
- context
- outline
- templates

### Sonra pipeline
- rewrite-pipeline
- seo-pipeline
- ai-article-pipeline

### Sonra seo
- meta-builder
- schema-builder
- faq-builder
- seo-engine

### Sonra services
- media-service
- seo-service
- rewrite-service
- article-service
- selftest-service

### Sonra integrations + ajax + panel
- integrations/*
- ajax-handler.php
- panel.php
- ui related includes

### Kural
Hiçbir dosya kendisinden önce yüklenmesi gereken altyapıyı varsaymamalıdır.

---

## 7. Response Contract Standardı

Bu modülde tek bir response standardı olmalıdır.

### Makale üretim response örneği

```php
[
  'ok' => true,
  'article' => [
    'title' => '',
    'content' => '',
    'html' => '',
    'summary' => '',
    'sections' => [],
    'sources' => [],
    'lang' => 'tr',
    'category' => '',
    'topic' => '',
  ],
  'seo' => [
    'meta_title' => '',
    'meta_description' => '',
    'faq' => [],
    'schema' => [],
    'keywords' => [],
  ],
  'meta' => [
    'provider' => '',
    'model' => '',
    'usage' => [],
    'quality' => [],
    'timing' => [],
    'build' => '',
  ],
  'error' => null,
]
Rewrite response örneği
[
  'ok' => true,
  'rewrite' => [
    'content' => '',
    'html' => '',
    'summary' => '',
    'lang' => 'tr',
  ],
  'meta' => [
    'provider' => '',
    'model' => '',
    'usage' => [],
  ],
  'error' => null,
]
Kural

UI ve AJAX bu contract dışında veri beklememelidir.

8. Legacy ve V6 Ayrımı

Bu modülde geçmiş sürümlerden kalan davranışlar olabilir:

legacy generate akışı

eski rewrite fallback’leri

farklı response shape’leri

eski provider call davranışları

Yeni resmi mimari şudur:

V6 service + pipeline + context + router + gateway merkez olur.

Legacy parçalar:

uyumluluk katmanı

adapter

eski entegrasyon desteği

olarak kalabilir.

Ama artık ana omurga olmamalıdır.

9. Bugünkü Mimari Sorunların Kaynağı

Mevcut semptomların kök nedenleri genellikle şunlardır:

pipeline ile generate katmanı aynı contract’ı konuşmuyor

prompt payload var ama gerçek prompt/messages net değil

rewrite ile cleanup birbirine karışmış

provider registry boot sırasına tam oturmamış

selftest yanlış isim/sabit bekleyebiliyor

response shape tek değil

UI savunmacı kodla boşlukları kapatmaya çalışıyor

Bu yüzden dış semptomlar oluşuyor:

kısa içerik

İngilizce kalıntılar

rewrite boş gibi görünmesi

collector var ama bazen missing hissi

bazı katmanların çalışıyor gibi ama tam değil olması

10. Mimari Karar İlkeleri

Bu modülde gelecekte alınacak kararlar şu ilkelere uymalıdır:

İlke 1

Her dosyanın tek ana sorumluluğu olmalı.

İlke 2

UI business logic taşıyamaz.

İlke 3

AJAX service yerine geçemez.

İlke 4

Pipeline provider-specific detay bilemez.

İlke 5

Gateway provider farklarını yukarı katmanlardan gizlemeli.

İlke 6

News katmanı makale yazmamalı.

İlke 7

Rewrite service gerçek rewrite yapmalı; cleanup ile karıştırılmamalı.

İlke 8

Tek response şeması korunmalı.

İlke 9

Storage JSON-first olmalı.

İlke 10

Kod değişirse docs da aynı anda güncellenmeli.

11. Bu Belgenin Rolü

Bu belge:

pazarlama metni değildir

kısa özet değildir

kalıcı teknik hafızadır

Yeni biri bu modülü anlamak istediğinde ilk okuyacağı belge bu olmalıdır.

Bu belgenin amacı:

modülün ne olduğunu

hangi katmanlardan oluştuğunu

hangi dosyanın ne yaptığını

hangi sınırların korunacağını

tek yerde açıklamaktır.

12. Özet Hüküm

AI Article Generator modülü:

haber toplama

bağlam kurma

çoklu sağlayıcı ile LLM çağrısı yapma

rewrite

SEO enrichment

yayın öncesi veri hazırlama

işlerini yapan, Masal Panel içinde çalışan, JSON-first, çok katmanlı bir AI editorial module yapısıdır.

Bu modülde resmi omurga:

Service Layer
+
Pipeline Layer
+
Context/News Layer
+
Router/Gateway/Provider Layer

birlikte çalışmalıdır.

UI, AJAX ve legacy parçalar yalnızca destekleyici kenar katmanlar olmalıdır.


---

Bu artık öncekiler gibi boş değil; **gerçek mimari referans** seviyesinde.

Şimdi aynı kalitede devam edeceğim ve sıradaki dosyayı vereceğim:

# `docs/PIPELINE.md`

```markdown
# AI Article Generator – Pipeline
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/PIPELINE.md

---

## 1. Amaç

Bu belge, AI Article Generator modülündeki makale üretim akışını tanımlar.

Amaç yalnızca “prompt gönder → yazı al” zinciri değildir.

Pipeline şu işi yapar:

- konu veya kategori alır
- haber toplar
- haberleri normalize eder
- fact pack çıkarır
- context üretir
- outline kurar
- uygun model/sağlayıcı seçer
- LLM üzerinden ilk taslağı üretir
- rewrite/polish uygular
- SEO zenginleştirme yapar
- standart article object döndürür

Bu nedenle pipeline, modülün en kritik çalışma omurgalarından biridir.

---

## 2. Pipeline’ın Yeri

Yüksek seviye sistem içindeki konumu:

UI / Panel
↓
AJAX
↓
Article Service
↓
Article Pipeline
↓
Router + Gateway + Provider
↓
LLM
↓
Rewrite / SEO / Finalize

Pipeline doğrudan kullanıcı arayüzü katmanı değildir.  
Pipeline aynı zamanda saf provider katmanı da değildir.

Pipeline, **üretim zincirinin düzenleyicisidir**.

---

## 3. Ana Dosyalar

Makale üretim pipeline’ı ile doğrudan ilgili ana dosyalar:

- core/ai-article-pipeline.php
- core/ai-article-context.php
- core/ai-article-outline.php
- core/pipelines/rewrite-pipeline.php
- core/pipelines/seo-pipeline.php
- core/services/article-service.php
- core/services/rewrite-service.php
- core/services/seo-service.php

Destekleyici dosyalar:
- core/news/*
- core/ai-article-router.php
- core/ai-article-gateway.php
- core/ai-article-llm.php
- core/seo/*
- storage/prompt-presets.json
- storage/router.json

---

## 4. Pipeline Girdileri

Makale üretim pipeline’ı tipik olarak aşağıdaki girdileri alır:

- topic
- category
- target language
- tone
- target length
- style/template
- selected or preferred provider/model
- source mode
- seo enabled flag
- rewrite enabled flag
- media enabled flag

Örnek mantıksal input:

```php
[
  'topic' => 'Bugünün teknoloji gündemi',
  'category' => 'tech',
  'lang' => 'tr',
  'tone' => 'analytical',
  'length' => 'long',
  'template' => 'news_analysis',
  'seo' => true,
  'rewrite' => true,
]
Girdi ilkesi

Input mümkün olduğunca service katmanında normalize edilmelidir.
Pipeline normalize edilmemiş, çelişkili veya kirli input ile baş başa bırakılmamalıdır.

5. Pipeline Fazları

Makale üretimi tek adım değildir.
Aşağıdaki fazlardan oluşur.

Faz 1 — Request Normalize

Sahibi:

article-service.php

Amaç:

topic boş mu?

kategori geçerli mi?

dil hedefi nedir?

rewrite/seo flag’leri açık mı?

hangi template kullanılacak?

kullanıcı tercihi sistem ayarıyla nasıl birleşecek?

Bu aşamada kirli input temizlenir.

Faz 2 — News Collect

Sahibi:

news-sources.php

news-cache.php

news-collector.php

Amaç:

ilgili kategori / konu için haber toplamak

cache kullanmak

gereksiz ağ yükünü azaltmak

yeterli kaynak havuzu kurmak

Bu fazın çıktısı ham veya yarı normalize haberlerdir.

Faz 3 — News Normalize

Sahibi:

news-normalizer.php

Amaç:
Toplanan her kaynağı ortak veri şemasına dönüştürmek.

Örnek normalize alanları:

title

url

source

summary

published_at

language

category

image

author

tags

Kural

Pipeline hiçbir zaman birbirinden tamamen farklı haber formatlarıyla doğrudan çalışmamalıdır.
Önce normalize edilmelidir.

Faz 4 — Fact Pack Build

Sahibi:

news-fact-pack.php

Amaç:
Haber kümelerinden LLM için daha yararlı veri çıkarmak.

Örnek çıktılar:

ana olay maddeleri

öne çıkan şirketler / kişiler

tekrar eden ana kavramlar

dikkat çeken tarihler

ortak eğilimler

riskler / fırsatlar

bağlamsal notlar

Fact pack, LLM’e “ham RSS yığını” yerine daha akıllı bağlam verir.

Faz 5 — Context Build

Sahibi:

ai-article-context.php

Amaç:

topic

category

normalized news

fact pack

hedef dil

template bilgisi

gibi girdileri bir araya getirerek üretim için kullanılacak bağlamı kurmaktır.

Context pack örneği:

topic

category

language

source blocks

fact bullets

entities

keywords

editorial angle

audience hint

Kritik not

V6 kalitesinin ana anahtarı context katmanıdır.
İyi context yoksa iyi makale çıkmaz.

Faz 6 — Outline Build

Sahibi:

ai-article-outline.php

Amaç:
İçeriğin iskeletini kurmak.

Örnek section yapısı:

giriş

ana gelişmeler

neden önemli

sektör etkisi

ileriye dönük beklentiler

sonuç

Farklı template’lerde outline değişebilir:

breaking news

analysis

guide

review

roundup

Kural

Outline gövde metni üretmez.
Sadece yazı iskeletini tanımlar.

Faz 7 — Prompt / Messages Build

Sahibi:

ai-article-pipeline.php

prompt preset yapıları

gerektiğinde ai-article-templates.php

Amaç:
Context ve outline kullanılarak gerçek LLM isteğini hazırlamak.

Bu aşamada üretilmesi gereken şey yalnızca “payload objesi” değil, gerçekten LLM’in anlayacağı tam yapı olmalıdır.

Bu yapı şunlardan biri olabilir:

tek string prompt

messages array

system + user ayrımı olan çok parçalı istem

Çok kritik mimari uyarı

Buradaki tarihi hata şudur:

context üretildi

prompt payload üretildi

ama generate katmanının beklediği gerçek prompt/messages eksik kaldı

Bu olduğunda:

LLM doğru çağrılmaz

fallback gereksiz erken devreye girer

kısa / zayıf / yabancı dil karışımlı içerik ortaya çıkar

Kural

Prompt aşaması yarım bırakılamaz.
Pipeline’ın LLM’e gönderdiği veri tam, açık ve deterministic olmalıdır.

Faz 8 — Route Select

Sahibi:

ai-article-router.php

Amaç:
Bu görev için en uygun sağlayıcı/model çiftini seçmek.

Girdiler:

task type = article_generate

hedef kalite

hız ihtiyacı

maliyet politikası

provider availability

kullanıcı/panel tercihleri

router.json politikaları

Çıktılar:

provider

model

fallback zinciri

timeout

token sınırı

temperature profili

Faz 9 — Gateway Call

Sahibi:

ai-article-gateway.php

ai-article-llm.php

provider sınıfları

Amaç:
Router’ın seçtiği provider/model ile gerçek AI çağrısını yapmak.

Gateway sorumlulukları:

timeout

retry

provider-specific dönüşümü normalize etme

usage toplama

hata standardizasyonu

Bu aşamanın çıktısı tek formata indirilmiş AI cevabıdır.

Örnek:

[
  'ok' => true,
  'content' => '...',
  'provider' => 'groq',
  'model' => 'llama-x',
  'usage' => [...],
  'error' => null,
]
Faz 10 — Raw Output Parse

Sahibi:

ai-article-pipeline.php

Amaç:
LLM’den gelen ham içeriği article object’e dönüştürmek.

Örnek ayrıştırmalar:

başlık çıkarımı

ana gövde

section tespiti

summary üretimi veya çıkarımı

sources block bağlama

html/text normalize

Kural

Provider cevabı doğrudan UI’ya verilmemelidir.
Önce article shape’e dönüştürülmelidir.

Faz 11 — Rewrite / Polish

Sahibi:

rewrite-service.php

rewrite-pipeline.php

Amaç:
İlk taslağı daha iyi hale getirmek.

Bu iki katmanın rolleri farklıdır:

rewrite-service.php

Gerçek LLM tabanlı yeniden yazımı yönetir.

rewrite-pipeline.php

Postprocess / cleanup yapar.

Örnek postprocess:

heading düzeltme

aşırı tekrar temizliği

html koruma

section bütünlüğü

küçük dil cilası

Kural

Cleanup ile gerçek rewrite birbirine karıştırılmamalıdır.

Faz 12 — SEO Enrichment

Sahibi:

seo-service.php

seo-pipeline.php

core/seo/*

Amaç:
Makale tamamlandıktan sonra SEO verisi üretmek.

Örnek çıktılar:

meta title

meta description

FAQ

schema

keyword kümesi

internal linking önerileri

SEO üretimi makalenin yerine geçmez; makaleyi zenginleştirir.

Faz 13 — Final Normalize

Sahibi:

article-service.php

ai-article-pipeline.php

Amaç:
Tüm çıktıları tek response contract altında birleştirmek.

Beklenen final shape:

[
  'ok' => true,
  'article' => [
    'title' => '',
    'content' => '',
    'html' => '',
    'summary' => '',
    'sections' => [],
    'sources' => [],
    'lang' => 'tr',
    'category' => '',
    'topic' => '',
  ],
  'seo' => [
    'meta_title' => '',
    'meta_description' => '',
    'faq' => [],
    'schema' => [],
    'keywords' => [],
  ],
  'meta' => [
    'provider' => '',
    'model' => '',
    'usage' => [],
    'quality' => [],
    'timing' => [],
    'build' => '',
  ],
  'error' => null,
]
6. Pipeline İçinde Olmaması Gerekenler

Pipeline güçlüdür ama her şeyi yapmamalıdır.

Pipeline şunları yapmamalı:

nonce doğrulama

panel HTML üretimi

doğrudan JS davranışı yönetimi

provider kayıt sistemi yerine geçme

storage panel kaydetme mantığı

WordPress post insert detaylarını üstlenme

Bu ayrımlar korunmazsa kod dağılır.

7. Pipeline ve Rewrite İlişkisi

En sık karışan alanlardan biri budur.

Doğru ilişki

article pipeline ilk üretimi yapar

rewrite-service ikinci aşama yeniden yazımı yönetir

rewrite-pipeline cleanup yapar

Yanlış ilişki

rewrite-service yalnız cleanup yaparsa

article pipeline “rewrite varmış gibi” davranıp gerçek LLM rewrite yapmazsa

AJAX kendi rewrite prompt’unu kurarsa

sistem yüzeysel ve tutarsız hale gelir.

8. Pipeline ve News Katmanı İlişkisi

Doğru ilişki:

pipeline, news katmanını kullanır

news katmanı pipeline’a veri sağlar

news katmanı makale kararları vermez

Bu sınır çok önemlidir.

Yanlış yapı örneği

collector içinde article body üretmek

fact pack içinde prompt kurmak

normalizer içinde editorial yön vermek

Bunlar mimari sınır ihlalidir.

9. Pipeline ve Router İlişkisi

Pipeline model seçmez; model seçim ihtiyacını tanımlar.
Router seçimi yapar.

Pipeline diyebilir ki:

bu bir article_generate görevi

yüksek kalite gerekli

Türkçe akıcı uzun form isteniyor

Ama şunu pipeline tek başına yapmamalı:

kesin provider hardcode etmek

fallback zincirini kendi belirlemek

usage politikasını bypass etmek

10. Pipeline Hata Yönetimi İlkeleri

Makale pipeline’ında hata yönetimi net olmalıdır.

Hata sınıfları

input errors

source/news errors

route selection errors

provider/network errors

parse/shape errors

rewrite/seo enrichment errors

Politika

kritik olmayan aşamalar kısmi başarısız olabilir

ama final response yine standard shape ile dönmelidir

ok=false veya ok=true kararının anlamı net olmalıdır

hatalar error alanında açıklanmalıdır

fallback tetiklenirse meta içinde görünür olmalıdır

11. Mevcut Mimari Açısından Kritik Sorun

Bugünkü sistemde en büyük pipeline sorunu şu tiptedir:

context üretiliyor

outline üretiliyor

prompt payload üretiliyor

fakat generate katmanının beklediği gerçek prompt/messages sözleşmesi tam tamamlanmıyor

Bunun sonucu:

LLM boş veya yetersiz girdiye düşüyor

sistem erken fallback’e kayıyor

kısa içerik üretiyor

İngilizce özet kalıntıları article içine sızıyor

rewrite gerçek rewrite gibi davranmıyor

Sonuç

Pipeline’ın asıl onarım noktası:
prompt contract + final normalize contract

12. Pipeline Kalite İlkeleri

Başarılı bir article pipeline şu özelliklere sahip olmalıdır:

deterministic giriş/çıkış şekli

tek response standardı

açık task tipi

gerçek bağlam kullanımı

gereksiz fallback yerine net hata yönetimi

rewrite ve seo’nun ayrı sorumluluklara sahip olması

provider bağımsızlık

yüksek gözlemlenebilirlik (log/usage/meta)

13. Bu Belgenin Rolü

Bu belge:

yalnızca akış şeması değildir

gerçek sistem davranış rehberidir

Yeni geliştirici ya da gelecekteki geliştirme oturumları için şu soruların cevabını burada bulmak gerekir:

makale nasıl üretiliyor?

hangi aşamalar zorunlu?

hangi aşama hangi dosyada?

en kritik contract nerede?

hangi sınırlar korunmalı?

14. Son Hüküm

AI Article Generator pipeline’ı, modülün yalnızca bir parçası değil; editoryal üretim omurgasıdır.

Bu omurganın resmi sırası:

request normalize
→ news collect
→ normalize
→ fact pack
→ context
→ outline
→ prompt/messages
→ route
→ gateway
→ provider/model
→ raw parse
→ rewrite/polish
→ seo enrich
→ final article object

Bu sıralama bozulduğunda modül kalitesi hızla düşer.

Bu sıralama doğru kurulduğunda ise modül:

kısa içerik sorununu aşar

dil kalitesini yükseltir

rewrite’ı gerçek anlamda işler hale getirir

çoklu sağlayıcı mimarisini sağlıklı kullanır

daha üretim seviyesi bir AI newsroom motoruna dönüşür
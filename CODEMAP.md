# CODEMAP — AI Article Generator

Bu dosya modülün teknik hafızasıdır. Amaç, “hangi dosya ne yapıyor?” sorusunu tek yerde cevaplamaktır.

## 1. Giriş noktaları

### `ai-article-generator.php`
Ana loader.
- sabitleri tanımlar
- çekirdek dosyaları include eder
- admin script/style enqueue eder
- LLM filter bridge bağlar

### `panel.php`
Admin UI.
- üretim ekranı
- rewrite alanı
- pexels alanı
- legacy LLM paneli
- usage / self-test / log görüntüleme

### `ajax-handler.php`
Admin AJAX tek kapısı.
- log tail / clear
- pexels probe / fetch
- pexels key save
- pipeline generate
- rewrite
- templates marketplace
- usage refresh
- self-test
- legacy llm load/save/test

## 2. Core üretim katmanı

### `core/ai-article-core.php`
Genel üretim ve yardımcı akışların ana merkezi.

### `core/ai-article-pipeline.php`
Makale üretim pipeline yöneticisi.
- outline
- section generation
- seo/schema/meta
- quality gate
- save hazırlığı

### `core/ai-article-outline.php`
Outline / başlık iskeleti üretimi.

### `core/ai-article-quality.php`
Deterministik kalite sinyalleri ve skor.

### `core/ai-article-post.php`
WordPress yazı oluşturma / güncelleme.

### `core/ai-article-context.php`
Brand / brief / kaynak / hedef kitle bağlamı.

### `core/ai-article-media.php`
Pexels medya arama, doğrulama, indirme ve ekleme yardımcıları.

### `core/ai-article-templates.php`
Şablon / prompt stratejisi yardımcıları.

### `core/ai-article-internal-links.php`
WP içi ilgili içerik bulma ve internal link önerileri.

### `core/ai-article-metrics.php`
Token, süre, similarity, cost benzeri ölçüm verileri.

### `core/ai-log.php`
JSON log altyapısı.

## 3. Yeni LLM omurgası
Bu dosyalar gelecekteki ana runtime çekirdeğidir.

### `core/ai-article-settings.php`
JSON-first ayar yöneticisi.
- `storage/settings.json` okur/yazar
- default / merge / validation

### `core/ai-article-engines.php`
Provider ve model envanteri.

### `core/ai-article-router.php`
Karar motoru.
- provider seçimi
- model seçimi
- preset uygulama
- candidate chain oluşturma
- failover mantığı

### `core/ai-article-gateway.php`
Transport katmanı.
- request formatlama
- header ekleme
- HTTP gönderimi
- response normalize
- hata sınıflandırma

### `core/ai-article-usage.php`
Kullanım ve maliyet takibi.

### `core/ai-article-selftest.php`
Sağlayıcı / bağlantı / latency testleri.

## 4. Legacy katman

### `core/ai-article-llm.php`
Eski tek-provider LLM köprüsü.
Yeni planda primary runtime olmamalı; compatibility bridge olarak kalmalıdır.

### `core/ai-article-bridge.php`
Dış entegrasyon hook köprüsü.

## 5. UI ve istemci tarafı

### `ui/editor.js`
Panelin istemci mantığı.
- buton event’leri
- AJAX çağrıları
- sonuç / status alanı güncellemeleri

### `ui/settings.php`
UI ayar yardımcıları.

### `ui/style.css`
Panel stilleri.

## 6. Entegrasyonlar
- `integrations/ai-language-hook.php`
- `integrations/ai-rewrite-hook.php`
- `integrations/ai-seo-hook.php`
- `integrations/ai-sources-hook.php`
- `integrations/api-keys-panel.php`
- `integrations/rest-api.php`

## 7. Kalıcı veri

### `storage/settings.json`
Tek doğru ayar kaynağı olması gereken dosya.
Hedef ana bölümler:
- `llm.providers`
- `llm.models`
- `llm.routing`
- `llm.presets`
- `llm.health`
- `llm.billing`
- `llm.defaults`

### `storage/feature-map.json`
Özellik → dosya eşleşmesi için makine okunur kayıt.

## 8. Dokümantasyon

### Güncel çekirdek dokümanlar
- `README.md`
- `CODEMAP.md`
- `docs/ARCHITECTURE.md`
- `docs/CODEMAP.md`
- `docs/GATEWAY.md`
- `docs/PIPELINE.md`
- `docs/QUALITY.md`
- `docs/RATE_LIMIT.md`
- `docs/ROADMAP.md`
- `docs/SECURITY.md`
- `docs/TEMPLATES.md`

### Yeni LLM upgrade dokümanları
- `docs/LLM_ARCHITECTURE.md`
- `docs/PROVIDERS.md`
- `docs/FAILOVER.md`
- `docs/ROUTER_ALGORITHM.md`
- `docs/WHICH_FILE_WHAT.md`
- `docs/IMPLEMENTATION_PLAN.md`

## 9. Mimari not
Mevcut ZIP’te aktif runtime ile hedef mimari aynı değildir.

### Şu an baskın runtime
`panel -> ajax -> ai-article-llm.php -> filter -> core/pipeline`

### Hedef runtime
`panel/ajax -> settings -> router -> gateway -> provider -> normalized response -> pipeline`

Bu ayrım unutulmamalıdır.

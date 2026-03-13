# AI Article Generator — Multi-Provider LLM Upgrade Baseline

AI Article Generator, Masal Panel içinde çalışan makale üretim, düzenleme, kalite kontrol, medya ekleme ve WordPress kaydetme modülüdür. Bu sürümde en kritik hedef, modülü **tek sağlayıcılı LLM akışından** çıkarıp **çoklu provider + çoklu model + failover + free-first** mimarisine taşımaktır.

## Proje amacı

- Telifsiz / sıfırdan üretim odaklı içerik motoru kurmak
- Makale üretimini pipeline mantığıyla yönetmek
- LLM katmanını tek endpoint bağımlılığından kurtarmak
- OpenAI-compatible, OpenRouter, DeepSeek ve local/custom endpoint desteği sağlamak
- Gelecekte diğer modüllerin de kullanacağı ortak bir LLM çekirdeği üretmek

## Mevcut durum özeti

ZIP incelemesine göre modülde iki LLM katmanı birlikte bulunuyor:

### 1) Aktif runtime katmanı
Şu an gerçek çalışmada ağırlıklı olarak şu yol aktif:

`panel.php -> ajax-handler.php -> core/ai-article-llm.php -> ai_article/llm_generate -> core/ai-article-core.php / pipeline`

Bu katman:
- tek provider
- tek endpoint
- tek model
- WP option ağırlıklı kayıt

### 2) Yeni ama tam bağlanmamış katman
Aşağıdaki dosyalar çoklu provider mimarisinin başlangıcını taşıyor:

- `core/ai-article-settings.php`
- `core/ai-article-engines.php`
- `core/ai-article-router.php`
- `core/ai-article-gateway.php`
- `core/ai-article-usage.php`
- `core/ai-article-selftest.php`

Bu katman doğru yönde ama loader ve panel tarafından henüz ana omurga haline getirilmemiş.

## Hedef mimari

Kalıcı yön şu olacaktır:

`Panel / AJAX -> Settings -> Router -> Gateway -> Provider -> Normalized Response -> Pipeline -> Save/Post`

### Temel kararlar
- Tek doğru ayar kaynağı: `storage/settings.json`
- Varsayılan routing stratejisi: `free_first`
- Eski `core/ai-article-llm.php`: compatibility bridge olarak kalır
- Ana karar motoru: `core/ai-article-router.php`
- Ana taşıma katmanı: `core/ai-article-gateway.php`

## Free-first politika

Varsayılan öncelik sırası:

1. Local / ücretsiz endpoint
2. Ücretsiz provider veya ücretsiz model
3. Ucuz provider / model
4. Dengeli kalite profili
5. Premium rescue path

Bu sayede sistem, mümkün olduğunca ücretsiz çalışır; kalite veya erişilebilirlik gerektiğinde daha üst kata çıkar.

## Ana dosya grupları

### Loader ve giriş
- `ai-article-generator.php`
- `panel.php`
- `ajax-handler.php`

### Core pipeline
- `core/ai-article-core.php`
- `core/ai-article-pipeline.php`
- `core/ai-article-outline.php`
- `core/ai-article-quality.php`
- `core/ai-article-post.php`
- `core/ai-article-media.php`
- `core/ai-article-context.php`
- `core/ai-article-templates.php`

### Yeni LLM çekirdeği
- `core/ai-article-settings.php`
- `core/ai-article-engines.php`
- `core/ai-article-router.php`
- `core/ai-article-gateway.php`
- `core/ai-article-usage.php`
- `core/ai-article-selftest.php`

### Legacy / bridge
- `core/ai-article-llm.php`
- `core/ai-article-bridge.php`

## Admin panel modülleri
Şu an panelde ağırlıklı olarak bu bloklar bulunuyor:
- Makale Üret
- Edit / Rewrite Studio
- Template Marketplace
- Pexels Medya Arama
- API Anahtarları
- LLM Sağlayıcı (legacy tek endpoint ekranı)
- Token/Cost + Similarity Monitor
- Self-Test
- Log Viewer

Hedef panel düzeni ise şuna evrilecektir:
- Provider Manager
- Model Manager
- Routing & Failover
- Presets
- Diagnostics
- Usage / Cost
- Pipeline / Generate

## Dokümantasyon yapısı
Bu ZIP için docs klasörü tamamen yenilenmiştir ve artık aşağıdaki canonical dosyalar üzerinden okunmalıdır:

### Ana dokümanlar
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

### Yeni canonical LLM dokümanları
- `docs/LLM_ARCHITECTURE.md`
- `docs/PROVIDERS.md`
- `docs/FAILOVER.md`
- `docs/ROUTER_ALGORITHM.md`
- `docs/WHICH_FILE_WHAT.md`
- `docs/IMPLEMENTATION_PLAN.md`

## Yükleme ve konum
Modülün beklenen yolu:

`masal-panel/modules/ai-article-generator/`

Loader dosyası:

`ai-article-generator.php`

## Bu sürümdeki dokümantasyon kararı

Eski dokümanlar körlemesine silinmedi. Bunun yerine:
- çelişen bölümler güncellendi
- tekrar eden bölümler sadeleştirildi
- legacy gerçekler ile hedef mimari açıkça ayrıştırıldı
- LLM upgrade için yeni canonical dokümanlar eklendi

Bu dosya artık modülün üst seviye yön haritasıdır.

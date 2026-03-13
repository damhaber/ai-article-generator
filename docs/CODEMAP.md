# AI Article Generator – Code Map
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/CODEMAP.md

---

## 1. Amaç

Bu belge modülün dosya haritasını yalnızca listelemek için değil, aynı zamanda:
- mimari seviye
- yükleme sırası
- öncelik derecesi
- rol tipi
- legacy/core/support ayrımı

ile birlikte tanımlamak için vardır.

Bu belge “hangi dosya nerede” listesinden fazlasıdır.  
Bu belge, modülün fiziksel-mimari topoğrafyasıdır.

---

## 2. Seviye Etiketleri

Bu belgede her dosya için şu sınıflandırmalar kullanılır:

### Mimari Seviye
- `FOUNDATION`
- `PROVIDER`
- `ROUTING`
- `NEWS`
- `PIPELINE`
- `SEO`
- `SERVICE`
- `SUPPORT`
- `INTEGRATION`
- `UI`
- `DATA`
- `STORAGE`
- `LOG`
- `DOC`

### Öncelik Seviyesi
- `P1` = hemen yeniden yazılmalı
- `P2` = güçlü refactor gerekli
- `P3` = contract temizliği / sadeleştirme
- `P4` = şimdilik korunabilir
- `P5` = legacy/support, sonra bakılır

### Rol Tipi
- `CORE`
- `EDGE`
- `LEGACY`
- `ADAPTER`
- `SUPPORT`

---

## 3. Kök Dizin Haritası

```text
ai-article-generator/
├── cache/
├── core/
├── data/
├── docs/
├── integrations/
├── logs/
├── storage/
├── ui/
├── ai-article-generator.json
├── ai-article-generator.php
├── ajax-handler.php
├── CODEMAP.md
├── db-setup.php
├── panel.php
├── README.md


4. Root Dosya Sınıflandırması
ai-article-generator.php

Level: FOUNDATION

Priority: P1

Role: CORE

Function: module bootstrap / loader

ajax-handler.php

Level: INTEGRATION

Priority: P1

Role: CORE

Function: ajax entry layer

panel.php

Level: UI

Priority: P3

Role: EDGE

Function: panel shell

db-setup.php

Level: SUPPORT

Priority: P5

Role: SUPPORT

Function: yardımcı setup / legacy db tasks

ai-article-generator.json

Level: DATA

Priority: P5

Role: SUPPORT

Function: module metadata/config descriptor

README.md

Level: DOC

Priority: P3

Role: SUPPORT

Function: üst düzey modül açıklaması

CODEMAP.md

Level: DOC

Priority: P3

Role: SUPPORT

Function: kısa harita / yönlendirme

5. Core Dizini – Ana Yapı
core/
├── news/
├── pipelines/
├── providers/
├── seo/
├── services/
├── ai-article-bridge.php
├── ai-article-context.php
├── ai-article-core.php
├── ai-article-devnotes.php
├── ai-article-engines.php
├── ai-article-gateway.php
├── ai-article-internal-links.php
├── ai-article-llm.php
├── ai-article-media.php
├── ai-article-metrics.php
├── ai-article-outline.php
├── ai-article-pipeline.php
├── ai-article-post.php
├── ai-article-provider-registry.php
├── ai-article-quality.php
├── ai-article-queue.php
├── ai-article-router.php
├── ai-article-selftest.php
├── ai-article-settings.php
├── ai-article-templates.php
├── ai-article-usage.php
├── ai-log.php
6. Core Root Dosyaları – Sınıflandırma
ai-log.php

Level: FOUNDATION

Priority: P4

Role: CORE

Function: logging infrastructure

ai-article-settings.php

Level: FOUNDATION

Priority: P4

Role: CORE

Function: settings infrastructure

ai-article-usage.php

Level: FOUNDATION

Priority: P4

Role: SUPPORT

Function: usage tracking

ai-article-metrics.php

Level: FOUNDATION

Priority: P4

Role: SUPPORT

Function: runtime metrics

ai-article-provider-registry.php

Level: PROVIDER

Priority: P2

Role: CORE

Function: provider registry

ai-article-router.php

Level: ROUTING

Priority: P2

Role: CORE

Function: task-aware routing

ai-article-gateway.php

Level: ROUTING

Priority: P2

Role: CORE

Function: normalized provider execution

ai-article-llm.php

Level: ROUTING

Priority: P2

Role: CORE

Function: high-level LLM helper facade

ai-article-core.php

Level: SUPPORT

Priority: P2/P3

Role: ADAPTER

Function: low-level legacy generation helper

ai-article-context.php

Level: NEWS

Priority: P2

Role: CORE

Function: context builder

ai-article-outline.php

Level: PIPELINE

Priority: P3

Role: CORE

Function: outline builder

ai-article-templates.php

Level: PIPELINE

Priority: P3

Role: SUPPORT

Function: template definitions/helpers

ai-article-pipeline.php

Level: PIPELINE

Priority: P1

Role: CORE

Function: main article orchestration

ai-article-quality.php

Level: SUPPORT

Priority: P4

Role: SUPPORT

Function: quality engine

ai-article-post.php

Level: SUPPORT

Priority: P5

Role: EDGE

Function: post save / WordPress bridge

ai-article-media.php

Level: SUPPORT

Priority: P5

Role: SUPPORT

Function: media helpers

ai-article-engines.php

Level: SUPPORT

Priority: P5

Role: SUPPORT

Function: engine abstraction/helpers

ai-article-internal-links.php

Level: SUPPORT

Priority: P5

Role: SUPPORT

Function: internal linking helpers

ai-article-bridge.php

Level: SUPPORT

Priority: P5

Role: LEGACY/ADAPTER

Function: legacy bridge

ai-article-selftest.php

Level: SUPPORT

Priority: P5

Role: LEGACY

Function: legacy selftest support

ai-article-devnotes.php

Level: SUPPORT

Priority: P5

Role: SUPPORT

Function: developer/debug notes helper

ai-article-queue.php

Level: SUPPORT

Priority: P5

Role: SUPPORT

Function: future queue/batch helper

7. news/ Alt Dizini
core/news/
├── news-sources.php
├── news-collector.php
├── news-cache.php
├── news-normalizer.php
├── news-fact-pack.php
├── news-helpers.php
news-sources.php

Level: NEWS

Priority: P4

Role: CORE

Function: source definitions / JSON access

news-collector.php

Level: NEWS

Priority: P2

Role: CORE

Function: source collection

news-cache.php

Level: NEWS

Priority: P4

Role: SUPPORT

Function: news cache

news-normalizer.php

Level: NEWS

Priority: P4

Role: CORE

Function: normalized news schema

news-fact-pack.php

Level: NEWS

Priority: P4

Role: CORE

Function: fact/entity/keyword extraction

news-helpers.php

Level: NEWS

Priority: P4

Role: SUPPORT

Function: helper functions

8. pipelines/ Alt Dizini
core/pipelines/
├── rewrite-pipeline.php
├── seo-pipeline.php
rewrite-pipeline.php

Level: PIPELINE

Priority: P3

Role: SUPPORT

Function: rewrite cleanup/postprocess

seo-pipeline.php

Level: PIPELINE

Priority: P3

Role: SUPPORT

Function: SEO enrichment pipeline

9. providers/ Alt Dizini
core/providers/
├── provider-base-openai-compat.php
├── provider-deepseek.php
├── provider-gemini.php
├── provider-groq.php
├── provider-interface.php
├── provider-mistral.php
├── provider-ollama.php
├── provider-openai.php
├── provider-openrouter.php
provider-interface.php

Level: PROVIDER

Priority: P4

Role: CORE

Function: provider contract

provider-base-openai-compat.php

Level: PROVIDER

Priority: P4

Role: SUPPORT

Function: shared OpenAI-compatible base

provider-openai.php
provider-groq.php
provider-gemini.php
provider-deepseek.php
provider-mistral.php
provider-ollama.php
provider-openrouter.php

Level: PROVIDER

Priority: P4

Role: CORE

Function: provider-specific adapters

10. seo/ Alt Dizini
core/seo/
├── faq-builder.php
├── meta-builder.php
├── schema-builder.php
├── seo-engine.php
faq-builder.php

Level: SEO

Priority: P4

Role: SUPPORT

Function: FAQ generation helper

meta-builder.php

Level: SEO

Priority: P4

Role: SUPPORT

Function: meta title/description helper

schema-builder.php

Level: SEO

Priority: P4

Role: SUPPORT

Function: schema generation helper

seo-engine.php

Level: SEO

Priority: P3

Role: CORE

Function: aggregated SEO engine

11. services/ Alt Dizini
core/services/
├── article-service.php
├── media-service.php
├── rewrite-service.php
├── selftest-service.php
├── seo-service.php
article-service.php

Level: SERVICE

Priority: P1

Role: CORE

Function: main article use-case entry

rewrite-service.php

Level: SERVICE

Priority: P1

Role: CORE

Function: main rewrite use-case entry

selftest-service.php

Level: SERVICE

Priority: P1

Role: CORE

Function: official health/selftest entry

seo-service.php

Level: SERVICE

Priority: P3

Role: CORE

Function: SEO use-case service

media-service.php

Level: SERVICE

Priority: P3/P4

Role: SUPPORT

Function: media-related support service

12. data/ Dizini
data/
├── news-sources.json
├── news-cache/
news-sources.json

Level: DATA

Priority: P4

Role: CORE DATA

Function: category/source definitions

news-cache/

Level: DATA

Priority: P4

Role: SUPPORT DATA

Function: cached source payloads

13. storage/ Dizini
storage/
├── usage/
├── feature-map.json
├── health.json
├── models.json
├── prompt-presets.json
├── providers.json
├── router.json
├── settings.json
settings.json

Level: STORAGE

Priority: P4

Role: CORE DATA

Function: module settings

providers.json

Level: STORAGE

Priority: P4

Role: CORE DATA

Function: provider config

models.json

Level: STORAGE

Priority: P4

Role: CORE DATA

Function: model metadata/capabilities

router.json

Level: STORAGE

Priority: P4

Role: CORE DATA

Function: routing policy

prompt-presets.json

Level: STORAGE

Priority: P4

Role: SUPPORT DATA

Function: prompt presets

feature-map.json

Level: STORAGE

Priority: P4

Role: SUPPORT DATA

Function: feature flags

health.json

Level: STORAGE

Priority: P4

Role: SUPPORT DATA

Function: last health summary

usage/

Level: STORAGE

Priority: P4

Role: SUPPORT DATA

Function: usage logs/records

14. integrations/ Dizini
integrations/
├── ai-language-hook.php
├── ai-rewrite-hook.php
├── ai-seo-hook.php
├── ai-sources-hook.php
├── api-keys-panel.php
├── rest-api.php
ai-language-hook.php

Level: INTEGRATION

Priority: P5

Role: EDGE

Function: language-related hooks

ai-rewrite-hook.php

Level: INTEGRATION

Priority: P5

Role: EDGE

Function: rewrite hooks

ai-seo-hook.php

Level: INTEGRATION

Priority: P5

Role: EDGE

Function: SEO hooks

ai-sources-hook.php

Level: INTEGRATION

Priority: P5

Role: EDGE

Function: source hooks

api-keys-panel.php

Level: INTEGRATION

Priority: P5

Role: EDGE

Function: provider key panel integration

rest-api.php

Level: INTEGRATION

Priority: P5

Role: EDGE

Function: controlled REST exposure

15. ui/ Dizini
ui/
├── components/
├── editor.js
├── settings.php
├── style.css
editor.js

Level: UI

Priority: P3

Role: EDGE

Function: interaction + ajax + preview

settings.php

Level: UI

Priority: P5

Role: EDGE

Function: settings render

style.css

Level: UI

Priority: P5

Role: SUPPORT

Function: panel styling

components/

Level: UI

Priority: P5

Role: SUPPORT

Function: reusable UI pieces

16. logs/ Dizini
logs/
├── ai-article-generator.log
├── news.log
├── collector.log
ai-article-generator.log

Level: LOG

Priority: P4

Role: SUPPORT

Function: general module logs

news.log

Level: LOG

Priority: P4

Role: SUPPORT

Function: news flow logs

collector.log

Level: LOG

Priority: P4

Role: SUPPORT

Function: collector-specific logs

17. docs/ Dizini
docs/
├── ARCHITECTURE.md
├── CODEMAP.md
├── CURRENT_RUNTIME_TRUTH.md
├── DOCS_TRUTH.md
├── FAILOVER.md
├── GATEWAY.md
├── IMPLEMENTATION_PLAN.md
├── LLM_ARCHITECTURE.md
├── PANEL_ARCHITECTURE.md
├── PIPELINE.md
├── PROMPT_ENGINE.md
├── PROVIDERS.md
├── QUALITY.md
├── RATE_LIMIT.md
├── README.md
├── ROADMAP.md
├── ROUTER_ALGORITHM.md
├── SECURITY.md
├── SELFTEST.md
├── TEMPLATES.md
├── V6_STABILIZATION_PLAN.md
├── WHICH_FILE_WHAT.md
Kritik docs

ARCHITECTURE.md

PIPELINE.md

PROVIDERS.md

GATEWAY.md

ROUTER_ALGORITHM.md

SELFTEST.md

QUALITY.md

IMPLEMENTATION_PLAN.md

WHICH_FILE_WHAT.md

CURRENT_RUNTIME_TRUTH.md

V6_STABILIZATION_PLAN.md

Bunlar source of truth seviyesindedir.

18. Resmi Merkez Dosyalar

Bu modülde resmi omurga dosyaları şunlardır:

ai-article-generator.php

ajax-handler.php

core/services/article-service.php

core/services/rewrite-service.php

core/services/selftest-service.php

core/ai-article-pipeline.php

core/ai-article-context.php

core/ai-article-outline.php

core/ai-article-router.php

core/ai-article-gateway.php

core/ai-article-provider-registry.php

Bu dosyalar omurgadır.
Diğer dosyalar bunları destekler.

19. İlk Yeniden Yazım Paketi

Önce birlikte ele alınması gereken dosyalar:

ai-article-generator.php

ajax-handler.php

core/services/article-service.php

core/ai-article-pipeline.php

core/services/rewrite-service.php

core/services/selftest-service.php

20. Son Hüküm

Code map’in ana mesajı şudur:

Bu modülün kalbi az sayıda dosyadadır.
Geri kalan geniş dosya seti, bu kalbin etrafında dolaşan destek katmanlarıdır.

Bu ayrım korunursa:

geliştirme sırası netleşir

docs güvenilir olur

patch yerine mimari ilerleme mümkün olur



# `ai-article-generator.php` için resmi bootstrap pseudo-code

Aşağıdaki yapı **gerçek kod değil**, ama artık yazılacak gerçek kodun resmi iskeleti olmalı.

```php
<?php
/**
 * AI Article Generator
 * Module Bootstrap
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ---------------------------------------------------------
 * 1. MODULE IDENTITY / CONSTANTS
 * --------------------------------------------------------- */
define('AIG_MODULE_VERSION', '6.0.0');
define('AIG_MODULE_BUILD', '20260311-V6-STABILIZATION');
define('AIG_MODULE_DIR', __DIR__);
define('AIG_CORE_DIR', AIG_MODULE_DIR . '/core');
define('AIG_DATA_DIR', AIG_MODULE_DIR . '/data');
define('AIG_STORAGE_DIR', AIG_MODULE_DIR . '/storage');
define('AIG_LOG_DIR', AIG_MODULE_DIR . '/logs');
define('AIG_UI_DIR', AIG_MODULE_DIR . '/ui');
define('AIG_INTEGRATIONS_DIR', AIG_MODULE_DIR . '/integrations');

/* ---------------------------------------------------------
 * 2. FOUNDATION LOAD
 * --------------------------------------------------------- */
require_once AIG_CORE_DIR . '/ai-log.php';
require_once AIG_CORE_DIR . '/ai-article-settings.php';
require_once AIG_CORE_DIR . '/ai-article-usage.php';
require_once AIG_CORE_DIR . '/ai-article-metrics.php';

/* ---------------------------------------------------------
 * 3. BASIC BOOT VALIDATION
 * --------------------------------------------------------- */
// ensure storage/log/data paths exist
// ensure critical json files exist or create defaults
// ensure logger is ready
// load feature map/settings early

/* ---------------------------------------------------------
 * 4. PROVIDER LAYER LOAD
 * --------------------------------------------------------- */
require_once AIG_CORE_DIR . '/providers/provider-interface.php';
require_once AIG_CORE_DIR . '/providers/provider-base-openai-compat.php';
require_once AIG_CORE_DIR . '/providers/provider-openai.php';
require_once AIG_CORE_DIR . '/providers/provider-groq.php';
require_once AIG_CORE_DIR . '/providers/provider-gemini.php';
require_once AIG_CORE_DIR . '/providers/provider-deepseek.php';
require_once AIG_CORE_DIR . '/providers/provider-mistral.php';
require_once AIG_CORE_DIR . '/providers/provider-ollama.php';
require_once AIG_CORE_DIR . '/providers/provider-openrouter.php';
require_once AIG_CORE_DIR . '/ai-article-provider-registry.php';

/* ---------------------------------------------------------
 * 5. ROUTING / GATEWAY / LLM LOAD
 * --------------------------------------------------------- */
require_once AIG_CORE_DIR . '/ai-article-router.php';
require_once AIG_CORE_DIR . '/ai-article-gateway.php';
require_once AIG_CORE_DIR . '/ai-article-llm.php';
require_once AIG_CORE_DIR . '/ai-article-core.php';

/* ---------------------------------------------------------
 * 6. NEWS / CONTEXT / OUTLINE LOAD
 * --------------------------------------------------------- */
require_once AIG_CORE_DIR . '/news/news-helpers.php';
require_once AIG_CORE_DIR . '/news/news-cache.php';
require_once AIG_CORE_DIR . '/news/news-sources.php';
require_once AIG_CORE_DIR . '/news/news-normalizer.php';
require_once AIG_CORE_DIR . '/news/news-collector.php';
require_once AIG_CORE_DIR . '/news/news-fact-pack.php';
require_once AIG_CORE_DIR . '/ai-article-context.php';
require_once AIG_CORE_DIR . '/ai-article-outline.php';
require_once AIG_CORE_DIR . '/ai-article-templates.php';

/* ---------------------------------------------------------
 * 7. PIPELINE LOAD
 * --------------------------------------------------------- */
require_once AIG_CORE_DIR . '/pipelines/rewrite-pipeline.php';
require_once AIG_CORE_DIR . '/pipelines/seo-pipeline.php';
require_once AIG_CORE_DIR . '/ai-article-pipeline.php';

/* ---------------------------------------------------------
 * 8. SEO LOAD
 * --------------------------------------------------------- */
require_once AIG_CORE_DIR . '/seo/meta-builder.php';
require_once AIG_CORE_DIR . '/seo/schema-builder.php';
require_once AIG_CORE_DIR . '/seo/faq-builder.php';
require_once AIG_CORE_DIR . '/seo/seo-engine.php';

/* ---------------------------------------------------------
 * 9. SERVICE LOAD
 * --------------------------------------------------------- */
require_once AIG_CORE_DIR . '/services/media-service.php';
require_once AIG_CORE_DIR . '/services/seo-service.php';
require_once AIG_CORE_DIR . '/services/rewrite-service.php';
require_once AIG_CORE_DIR . '/services/article-service.php';
require_once AIG_CORE_DIR . '/services/selftest-service.php';

/* ---------------------------------------------------------
 * 10. SUPPORT / LEGACY / EDGE HELPERS
 * --------------------------------------------------------- */
require_once AIG_CORE_DIR . '/ai-article-media.php';
require_once AIG_CORE_DIR . '/ai-article-post.php';
require_once AIG_CORE_DIR . '/ai-article-quality.php';
require_once AIG_CORE_DIR . '/ai-article-internal-links.php';
require_once AIG_CORE_DIR . '/ai-article-engines.php';
require_once AIG_CORE_DIR . '/ai-article-bridge.php';
require_once AIG_CORE_DIR . '/ai-article-selftest.php';
require_once AIG_CORE_DIR . '/ai-article-devnotes.php';
require_once AIG_CORE_DIR . '/ai-article-queue.php';

/* ---------------------------------------------------------
 * 11. INTEGRATIONS
 * --------------------------------------------------------- */
require_once AIG_INTEGRATIONS_DIR . '/ai-language-hook.php';
require_once AIG_INTEGRATIONS_DIR . '/ai-rewrite-hook.php';
require_once AIG_INTEGRATIONS_DIR . '/ai-seo-hook.php';
require_once AIG_INTEGRATIONS_DIR . '/ai-sources-hook.php';
require_once AIG_INTEGRATIONS_DIR . '/rest-api.php';
require_once AIG_INTEGRATIONS_DIR . '/api-keys-panel.php';

/* ---------------------------------------------------------
 * 12. ACTION / AJAX / PANEL UI
 * --------------------------------------------------------- */
require_once AIG_MODULE_DIR . '/ajax-handler.php';
require_once AIG_MODULE_DIR . '/panel.php';
require_once AIG_UI_DIR . '/settings.php';

/* ---------------------------------------------------------
 * 13. FINAL MODULE BOOT
 * --------------------------------------------------------- */
// log module boot success
// optionally register panel hooks
// optionally register selftest summary
// expose module version/build info
Bootstrap için bağlayıcı kurallar
Kural 1

Foundation katmanı olmadan hiçbir core davranış yüklenmez.

Kural 2

Registry, router/gateway’den önce yüklenir.

Kural 3

News/context/outline, pipeline’dan önce yüklenir.

Kural 4

Pipelines, services’ten önce yüklenebilir; ama services bunların hazır olduğunu varsayar.

Kural 5

AJAX ve panel en son gelir.

Kural 6

Bootstrap dosyası iş mantığı taşımaz; yalnız yükleme ve ilk doğrulama yapar.

ajax-handler.php için resmi endpoint contract şeması

Bu bölüm çok önemli. Çünkü dışarıdan gelen tüm işlemler tek dil konuşmalı.

Genel ilke

Her endpoint şu yapıyı izlemeli:

request al

nonce doğrula

capability doğrula

input normalize et

doğru service çağır

standard response dön

Endpoint 1 — Article Generate
Action örneği
action=ai_article_generate
Beklenen request alanları
[
  'topic' => 'string',
  'category' => 'string',
  'lang' => 'string',
  'tone' => 'string',
  'length' => 'string',
  'template' => 'string',
  'provider' => 'string|null',
  'model' => 'string|null',
  'rewrite' => 'bool',
  'seo' => 'bool',
]
Çağrılacak service
aig_article_service_generate($input)
Dönüş shape’i
[
  'ok' => true,
  'article' => [...],
  'seo' => [...],
  'meta' => [...],
  'error' => null,
]
Endpoint 2 — Rewrite
Action örneği
action=ai_article_rewrite
Beklenen request alanları
[
  'content' => 'string',
  'instruction' => 'string',
  'lang' => 'string',
  'tone' => 'string',
  'mode' => 'string',
  'preserve_html' => 'bool',
  'target_length' => 'string',
  'provider' => 'string|null',
  'model' => 'string|null',
]
Çağrılacak service
aig_rewrite_service_run($input)
Dönüş shape’i
[
  'ok' => true,
  'rewrite' => [...],
  'meta' => [...],
  'error' => null,
]
Endpoint 3 — SEO Generate
Action örneği
action=ai_article_generate_seo
Beklenen request alanları
[
  'title' => 'string',
  'content' => 'string',
  'category' => 'string',
  'lang' => 'string',
]
Çağrılacak service
aig_seo_service_generate($input)
Dönüş shape’i
[
  'ok' => true,
  'seo' => [...],
  'meta' => [...],
  'error' => null,
]
Endpoint 4 — Selftest
Action örneği
action=ai_article_selftest
Beklenen request alanları
[
  'mode' => 'quick|full',
]
Çağrılacak service
aig_selftest_service_run($input)
Dönüş shape’i
[
  'ok' => true,
  'summary' => [...],
  'checks' => [...],
  'meta' => [...],
]
Endpoint 5 — Save Settings
Action örneği
action=ai_article_save_settings
Beklenen request alanları

settings form alanları

provider config alanları

feature flags

router / preset alanları

Çağrılacak katman

Doğrudan settings writer / config service

Kural

AJAX handler config validation yapar ama business logic settings katmanında kalır.

AJAX için bağlayıcı kurallar
Kural 1

AJAX handler prompt üretmez.

Kural 2

AJAX handler provider route seçmez.

Kural 3

AJAX handler fallback logic sahibi olmaz.

Kural 4

AJAX handler flat/legacy shape ile UI’yı beslemeye çalışmaz; standard contract döner.

Kural 5

Tüm endpoint’lerde nonce ve yetki kontrolü zorunludur.

Kural 6

Tüm endpoint’lerde normalize edilmiş hata kodu bulunur.

ajax-handler.php için pseudo-code iskeleti
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ARTICLE GENERATE
 */
function aig_ajax_article_generate() {
    // 1) verify nonce
    // 2) verify capability
    // 3) normalize request input
    // 4) call article service
    // 5) return wp_send_json(...)
}
add_action('wp_ajax_ai_article_generate', 'aig_ajax_article_generate');

/**
 * REWRITE
 */
function aig_ajax_article_rewrite() {
    // 1) verify nonce
    // 2) verify capability
    // 3) normalize request input
    // 4) call rewrite service
    // 5) return wp_send_json(...)
}
add_action('wp_ajax_ai_article_rewrite', 'aig_ajax_article_rewrite');

/**
 * SEO GENERATE
 */
function aig_ajax_article_generate_seo() {
    // 1) verify nonce
    // 2) verify capability
    // 3) normalize request input
    // 4) call seo service
    // 5) return wp_send_json(...)
}
add_action('wp_ajax_ai_article_generate_seo', 'aig_ajax_article_generate_seo');

/**
 * SELFTEST
 */
function aig_ajax_article_selftest() {
    // 1) verify nonce
    // 2) verify capability
    // 3) normalize request input
    // 4) call selftest service
    // 5) return wp_send_json(...)
}
add_action('wp_ajax_ai_article_selftest', 'aig_ajax_article_selftest');
Bu aşamanın net sonucu

Artık elimizde:

tam mimari docs

tam dosya sorumluluk seti

tam runtime truth

tam stabilizasyon planı

resmi code map

resmi bootstrap iskeleti

resmi AJAX contract şeması

var.

AŞAMA 6

core/services/article-service.php için resmi fonksiyon sözleşmesi + pseudo-code

core/ai-article-pipeline.php için resmi orchestration pseudo-code

core/services/rewrite-service.php için resmi sözleşme + pseudo-code

Bunlar artık “docs” ile “gerçek kod” arasındaki son büyük köprü olacak.

core/services/article-service.php
Resmi görev tanımı

article-service.php, modülde makale üretim use-case’inin tek resmi giriş kapısı olmalıdır.

Bu dosya:

panelden gelen generate isteğini alır

request’i normalize eder

settings/feature flag/policy kontrolü yapar

article pipeline’ı çağırır

final response contract’ı döndürür

Bu dosya doğrudan provider çağrısı yapan yer olmamalıdır.
Bu dosya prompt yazarı da olmamalıdır.

article-service.php için bağlayıcı kurallar
Kural 1

Service, generate isteğinin tek giriş kapısıdır.

Kural 2

AJAX, pipeline’ı doğrudan çağırmak yerine article-service’i çağırmalıdır.

Kural 3

Service, input normalize etmeden pipeline’a veri geçmemelidir.

Kural 4

Service, response contract standardını zorunlu kılmalıdır.

Kural 5

Service, feature flag/settings/policy gate olarak davranmalıdır.

Girdi sözleşmesi

Article service’e gelen normalize input mantıksal olarak şu alanları taşımalıdır:

[
  'topic' => 'string',
  'category' => 'string',
  'lang' => 'string',
  'tone' => 'string',
  'length' => 'short|medium|long',
  'template' => 'string',
  'provider' => 'string|null',
  'model' => 'string|null',
  'rewrite' => true,
  'seo' => true,
  'include_sources' => true,
  'include_summary' => true,
  'context_mode' => 'news',
  'user_id' => 0,
  'request_meta' => [],
]
Çıktı sözleşmesi
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
    'fallback_used' => false,
  ],
  'error' => null,
]
article-service.php için önerilen ana fonksiyonlar
1. aig_article_service_generate(array $input): array

Ana giriş fonksiyonu.

2. aig_article_service_normalize_input(array $input): array

Kirli input’u normalize eder.

3. aig_article_service_validate_input(array $input): array

Eksik/uygunsuz alanları kontrol eder.

4. aig_article_service_resolve_options(array $input): array

Settings + feature-map + request override’ları birleştirir.

5. aig_article_service_build_error(string $code, string $message, array $meta = []): array

Standart hata response üretir.

6. aig_article_service_finalize_response(array $pipelineResult, array $resolvedOptions): array

Pipeline sonucunu final contract’a indirger.

article-service.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main article generation entry point.
 */
function aig_article_service_generate(array $input): array
{
    $startedAt = microtime(true);

    // 1. Normalize input
    $normalized = aig_article_service_normalize_input($input);

    // 2. Validate input
    $validation = aig_article_service_validate_input($normalized);
    if (!$validation['ok']) {
        return aig_article_service_build_error(
            $validation['error']['code'],
            $validation['error']['message'],
            ['stage' => 'validate_input']
        );
    }

    // 3. Resolve runtime options
    $resolved = aig_article_service_resolve_options($normalized);

    // 4. Feature / policy checks
    if (!empty($resolved['feature_map']['article_generation_disabled'])) {
        return aig_article_service_build_error(
            'feature_disabled',
            'Article generation is disabled by feature map.',
            ['stage' => 'feature_gate']
        );
    }

    // 5. Call pipeline
    $pipelineResult = aig_article_pipeline_run($resolved);

    // 6. If pipeline failed, return normalized error
    if (empty($pipelineResult['ok'])) {
        return aig_article_service_build_error(
            $pipelineResult['error']['code'] ?? 'pipeline_failed',
            $pipelineResult['error']['message'] ?? 'Article pipeline failed.',
            [
                'stage' => 'pipeline',
                'pipeline_meta' => $pipelineResult['meta'] ?? [],
            ]
        );
    }

    // 7. Finalize response
    $response = aig_article_service_finalize_response($pipelineResult, $resolved);

    // 8. Attach timing
    $response['meta']['timing']['service_ms'] = (int) round((microtime(true) - $startedAt) * 1000);

    return $response;
}

/**
 * Normalize inbound article input.
 */
function aig_article_service_normalize_input(array $input): array
{
    return [
        'topic'            => trim((string) ($input['topic'] ?? '')),
        'category'         => trim((string) ($input['category'] ?? 'general')),
        'lang'             => trim((string) ($input['lang'] ?? 'tr')),
        'tone'             => trim((string) ($input['tone'] ?? 'analytical')),
        'length'           => trim((string) ($input['length'] ?? 'long')),
        'template'         => trim((string) ($input['template'] ?? 'news_analysis')),
        'provider'         => !empty($input['provider']) ? (string) $input['provider'] : null,
        'model'            => !empty($input['model']) ? (string) $input['model'] : null,
        'rewrite'          => !empty($input['rewrite']),
        'seo'              => !empty($input['seo']),
        'include_sources'  => array_key_exists('include_sources', $input) ? !empty($input['include_sources']) : true,
        'include_summary'  => array_key_exists('include_summary', $input) ? !empty($input['include_summary']) : true,
        'context_mode'     => trim((string) ($input['context_mode'] ?? 'news')),
        'user_id'          => (int) ($input['user_id'] ?? 0),
        'request_meta'     => is_array($input['request_meta'] ?? null) ? $input['request_meta'] : [],
    ];
}

/**
 * Validate normalized article input.
 */
function aig_article_service_validate_input(array $input): array
{
    if ($input['topic'] === '') {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_topic',
                'message' => 'Topic is required for article generation.',
            ],
        ];
    }

    if (!in_array($input['length'], ['short', 'medium', 'long'], true)) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'invalid_length',
                'message' => 'Length must be short, medium, or long.',
            ],
        ];
    }

    return ['ok' => true];
}

/**
 * Merge settings + feature flags + input overrides.
 */
function aig_article_service_resolve_options(array $input): array
{
    $settings   = function_exists('aig_settings_get_all') ? aig_settings_get_all() : [];
    $featureMap = function_exists('aig_feature_map_get_all') ? aig_feature_map_get_all() : [];

    return array_merge($input, [
        'settings'    => $settings,
        'feature_map' => $featureMap,
        'build'       => defined('AIG_MODULE_BUILD') ? AIG_MODULE_BUILD : '',
        'version'     => defined('AIG_MODULE_VERSION') ? AIG_MODULE_VERSION : '',
    ]);
}

/**
 * Final normalized error response.
 */
function aig_article_service_build_error(string $code, string $message, array $meta = []): array
{
    return [
        'ok' => false,
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
        'meta' => $meta,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ];
}

/**
 * Finalize successful pipeline result into official service contract.
 */
function aig_article_service_finalize_response(array $pipelineResult, array $resolvedOptions): array
{
    return [
        'ok' => true,
        'article' => [
            'title' => (string) ($pipelineResult['article']['title'] ?? ''),
            'content' => (string) ($pipelineResult['article']['content'] ?? ''),
            'html' => (string) ($pipelineResult['article']['html'] ?? ($pipelineResult['article']['content'] ?? '')),
            'summary' => (string) ($pipelineResult['article']['summary'] ?? ''),
            'sections' => is_array($pipelineResult['article']['sections'] ?? null) ? $pipelineResult['article']['sections'] : [],
            'sources' => is_array($pipelineResult['article']['sources'] ?? null) ? $pipelineResult['article']['sources'] : [],
            'lang' => (string) ($pipelineResult['article']['lang'] ?? $resolvedOptions['lang']),
            'category' => (string) ($pipelineResult['article']['category'] ?? $resolvedOptions['category']),
            'topic' => (string) ($pipelineResult['article']['topic'] ?? $resolvedOptions['topic']),
        ],
        'seo' => is_array($pipelineResult['seo'] ?? null) ? $pipelineResult['seo'] : [
            'meta_title' => '',
            'meta_description' => '',
            'faq' => [],
            'schema' => [],
            'keywords' => [],
        ],
        'meta' => is_array($pipelineResult['meta'] ?? null) ? $pipelineResult['meta'] : [
            'provider' => '',
            'model' => '',
            'usage' => [],
            'quality' => [],
            'timing' => [],
            'build' => $resolvedOptions['build'] ?? '',
            'fallback_used' => false,
        ],
        'error' => null,
    ];
}
core/ai-article-pipeline.php
Resmi görev tanımı

Bu dosya modülün ana article orchestration merkezi olmalıdır.

Pipeline’ın görevi:

article generate use-case’ini adım adım işletmek

context build

outline build

prompt/messages build

router/gateway/LLM çağrısı

raw output parse

rewrite/polish

SEO enrichment

final article object üretmek

Bu dosya:

nonce kontrolü yapmamalı

panel davranışı taşımamalı

WordPress save mantığı taşımamalı

Pipeline için bağlayıcı kurallar
Kural 1

Pipeline yalnız normalize edilmiş input alır.

Kural 2

Pipeline’ın resmi görevi article object üretmektir.

Kural 3

Pipeline gerçek prompt/messages üretmeden LLM çağrısına geçmemelidir.

Kural 4

Fallback kullanılırsa meta’da görünür olmalıdır.

Kural 5

Rewrite ve SEO pipeline içine gömülse bile, kendi service/pipeline sınırları korunmalıdır.

ai-article-pipeline.php için önerilen ana fonksiyonlar
1. aig_article_pipeline_run(array $input): array

Ana orchestration girişi.

2. aig_article_pipeline_build_context(array $input): array

Context katmanını çağırır.

3. aig_article_pipeline_build_outline(array $context, array $input): array

Outline üretir.

4. aig_article_pipeline_build_prompt_messages(array $context, array $outline, array $input): array

Gerçek LLM input’unu üretir.

5. aig_article_pipeline_route(array $input, array $messages): array

Router çağrısı.

6. aig_article_pipeline_generate(array $route, array $messages, array $input): array

Gateway/LLM çağrısı.

7. aig_article_pipeline_parse_output(array $llmResult, array $context, array $outline, array $input): array

Raw LLM çıktısını article object’e dönüştürür.

8. aig_article_pipeline_apply_rewrite(array $article, array $input): array

İsteğe bağlı rewrite/parlatma.

9. aig_article_pipeline_apply_seo(array $article, array $input): array

İsteğe bağlı SEO enrichment.

10. aig_article_pipeline_finalize(array $article, array $seo, array $meta): array

Final contract üretir.

ai-article-pipeline.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main orchestration entry for article generation.
 */
function aig_article_pipeline_run(array $input): array
{
    $startedAt = microtime(true);

    // 1. Build context
    $contextResult = aig_article_pipeline_build_context($input);
    if (empty($contextResult['ok'])) {
        return [
            'ok' => false,
            'meta' => ['stage' => 'context'],
            'error' => $contextResult['error'] ?? [
                'code' => 'context_failed',
                'message' => 'Context build failed.',
            ],
        ];
    }

    $context = $contextResult['context'];

    // 2. Build outline
    $outlineResult = aig_article_pipeline_build_outline($context, $input);
    if (empty($outlineResult['ok'])) {
        return [
            'ok' => false,
            'meta' => ['stage' => 'outline'],
            'error' => $outlineResult['error'] ?? [
                'code' => 'outline_failed',
                'message' => 'Outline build failed.',
            ],
        ];
    }

    $outline = $outlineResult['outline'];

    // 3. Build actual prompt/messages
    $messageResult = aig_article_pipeline_build_prompt_messages($context, $outline, $input);
    if (empty($messageResult['ok'])) {
        return [
            'ok' => false,
            'meta' => ['stage' => 'prompt'],
            'error' => $messageResult['error'] ?? [
                'code' => 'prompt_build_failed',
                'message' => 'Prompt/messages build failed.',
            ],
        ];
    }

    $messages = $messageResult['messages'];

    // 4. Route
    $routeResult = aig_article_pipeline_route($input, $messages);
    if (empty($routeResult['ok'])) {
        return [
            'ok' => false,
            'meta' => ['stage' => 'route'],
            'error' => $routeResult['error'] ?? [
                'code' => 'route_failed',
                'message' => 'Router could not select a provider/model.',
            ],
        ];
    }

    $route = $routeResult['route'];

    // 5. Generate via gateway/LLM
    $generateResult = aig_article_pipeline_generate($route, $messages, $input);
    if (empty($generateResult['ok'])) {
        return [
            'ok' => false,
            'meta' => [
                'stage' => 'generate',
                'route' => $route,
            ],
            'error' => $generateResult['error'] ?? [
                'code' => 'llm_generate_failed',
                'message' => 'LLM generation failed.',
            ],
        ];
    }

    // 6. Parse raw output into article object
    $articleResult = aig_article_pipeline_parse_output($generateResult, $context, $outline, $input);
    if (empty($articleResult['ok'])) {
        return [
            'ok' => false,
            'meta' => ['stage' => 'parse'],
            'error' => $articleResult['error'] ?? [
                'code' => 'article_parse_failed',
                'message' => 'Could not parse generated article output.',
            ],
        ];
    }

    $article = $articleResult['article'];

    // 7. Optional rewrite/polish
    if (!empty($input['rewrite'])) {
        $rewriteResult = aig_article_pipeline_apply_rewrite($article, $input);
        if (!empty($rewriteResult['ok'])) {
            $article = $rewriteResult['article'];
        }
    }

    // 8. Optional SEO enrichment
    $seo = [
        'meta_title' => '',
        'meta_description' => '',
        'faq' => [],
        'schema' => [],
        'keywords' => [],
    ];

    if (!empty($input['seo'])) {
        $seoResult = aig_article_pipeline_apply_seo($article, $input);
        if (!empty($seoResult['ok'])) {
            $seo = $seoResult['seo'];
        }
    }

    // 9. Quality (optional)
    $quality = [];
    if (function_exists('aig_quality_evaluate_article')) {
        $quality = aig_quality_evaluate_article($article, $input);
    }

    // 10. Finalize
    return aig_article_pipeline_finalize(
        $article,
        $seo,
        [
            'provider' => $generateResult['provider'] ?? ($route['provider'] ?? ''),
            'model' => $generateResult['model'] ?? ($route['model'] ?? ''),
            'usage' => is_array($generateResult['usage'] ?? null) ? $generateResult['usage'] : [],
            'quality' => $quality,
            'timing' => [
                'pipeline_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ],
            'build' => $input['build'] ?? '',
            'fallback_used' => !empty($generateResult['meta']['fallback_used']),
        ]
    );
}

/**
 * Build context from topic/category/news/facts.
 */
function aig_article_pipeline_build_context(array $input): array
{
    if (!function_exists('aig_article_context_build')) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_context_builder',
                'message' => 'Context builder function is missing.',
            ],
        ];
    }

    return aig_article_context_build($input);
}

/**
 * Build article outline.
 */
function aig_article_pipeline_build_outline(array $context, array $input): array
{
    if (!function_exists('aig_article_outline_build')) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_outline_builder',
                'message' => 'Outline builder function is missing.',
            ],
        ];
    }

    return aig_article_outline_build($context, $input);
}

/**
 * Build real LLM messages from context + outline + input.
 */
function aig_article_pipeline_build_prompt_messages(array $context, array $outline, array $input): array
{
    $lang = $input['lang'] ?? 'tr';
    $tone = $input['tone'] ?? 'analytical';
    $topic = $input['topic'] ?? '';
    $template = $input['template'] ?? 'news_analysis';

    $system = "You are an expert editorial AI. Write a high-quality {$lang} article with clear structure, factual grounding, and strong readability.";
    $user = "Topic: {$topic}\n"
          . "Language: {$lang}\n"
          . "Tone: {$tone}\n"
          . "Template: {$template}\n\n"
          . "Context:\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
          . "\n\nOutline:\n" . json_encode($outline, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
          . "\n\nWrite the full article with title, structured sections, a concise summary, and a clear conclusion.";

    return [
        'ok' => true,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ],
    ];
}

/**
 * Ask router for provider/model selection.
 */
function aig_article_pipeline_route(array $input, array $messages): array
{
    if (!function_exists('aig_router_select')) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_router',
                'message' => 'Router selection function is missing.',
            ],
        ];
    }

    $route = aig_router_select([
        'task' => 'article_generate',
        'preferred_provider' => $input['provider'] ?? null,
        'preferred_model' => $input['model'] ?? null,
        'lang' => $input['lang'] ?? 'tr',
        'length' => $input['length'] ?? 'long',
    ]);

    if (empty($route['ok'])) {
        return $route;
    }

    return ['ok' => true, 'route' => $route];
}

/**
 * Generate article through gateway/LLM layer.
 */
function aig_article_pipeline_generate(array $route, array $messages, array $input): array
{
    if (!function_exists('aig_llm_generate_messages')) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_llm_generate',
                'message' => 'LLM generate helper is missing.',
            ],
        ];
    }

    return aig_llm_generate_messages([
        'provider' => $route['provider'] ?? '',
        'model' => $route['model'] ?? '',
        'messages' => $messages,
        'task' => 'article_generate',
        'options' => $route['options'] ?? [],
    ]);
}

/**
 * Parse generated content into official article object.
 */
function aig_article_pipeline_parse_output(array $generateResult, array $context, array $outline, array $input): array
{
    $raw = trim((string) ($generateResult['content'] ?? ''));
    if ($raw === '') {
        return [
            'ok' => false,
            'error' => [
                'code' => 'empty_generation',
                'message' => 'Generated content was empty.',
            ],
        ];
    }

    // Real implementation may do richer parsing.
    $title = '';
    $body = $raw;

    if (preg_match('/^(.+?)\n+/u', $raw, $m)) {
        $title = trim($m[1]);
        $body = trim(substr($raw, strlen($m[0])));
    }

    if ($title === '') {
        $title = (string) ($input['topic'] ?? 'Generated Article');
    }

    $sources = is_array($context['sources'] ?? null) ? $context['sources'] : [];

    return [
        'ok' => true,
        'article' => [
            'title' => $title,
            'content' => $body,
            'html' => nl2br(esc_html($body)),
            'summary' => '',
            'sections' => [],
            'sources' => $sources,
            'lang' => (string) ($input['lang'] ?? 'tr'),
            'category' => (string) ($input['category'] ?? ''),
            'topic' => (string) ($input['topic'] ?? ''),
        ],
    ];
}

/**
 * Apply optional rewrite/polish.
 */
function aig_article_pipeline_apply_rewrite(array $article, array $input): array
{
    if (!function_exists('aig_rewrite_service_run')) {
        return ['ok' => false];
    }

    $result = aig_rewrite_service_run([
        'content' => $article['content'] ?? '',
        'instruction' => 'Polish and improve clarity while preserving meaning.',
        'lang' => $input['lang'] ?? 'tr',
        'tone' => $input['tone'] ?? 'analytical',
        'mode' => 'polish',
        'preserve_html' => false,
        'target_length' => $input['length'] ?? 'long',
        'provider' => $input['provider'] ?? null,
        'model' => $input['model'] ?? null,
    ]);

    if (empty($result['ok'])) {
        return ['ok' => false];
    }

    $article['content'] = (string) ($result['rewrite']['content'] ?? $article['content']);
    $article['html']    = (string) ($result['rewrite']['html'] ?? $article['html']);
    $article['summary'] = (string) ($result['rewrite']['summary'] ?? $article['summary']);

    return [
        'ok' => true,
        'article' => $article,
    ];
}

/**
 * Apply optional SEO enrichment.
 */
function aig_article_pipeline_apply_seo(array $article, array $input): array
{
    if (!function_exists('aig_seo_service_generate')) {
        return ['ok' => false];
    }

    return aig_seo_service_generate([
        'title' => $article['title'] ?? '',
        'content' => $article['content'] ?? '',
        'category' => $article['category'] ?? ($input['category'] ?? ''),
        'lang' => $article['lang'] ?? ($input['lang'] ?? 'tr'),
    ]);
}

/**
 * Finalize pipeline response into standard pipeline contract.
 */
function aig_article_pipeline_finalize(array $article, array $seo, array $meta): array
{
    return [
        'ok' => true,
        'article' => $article,
        'seo' => $seo,
        'meta' => $meta,
        'error' => null,
    ];
}
core/services/rewrite-service.php
Resmi görev tanımı

rewrite-service.php, modülde gerçek yeniden yazım use-case’inin resmi giriş kapısı olmalıdır.

Bu dosya:

mevcut metni alır

instruction/mode/tone/lang/preserve_html gibi alanları normalize eder

rewrite task’i için router çağrısı yaptırır

LLM rewrite uygular

rewrite-pipeline cleanup/postprocess uygular

final rewrite response döndürür

Bu dosya sadece cleanup yapan bir wrapper olmamalıdır.

Rewrite service için bağlayıcı kurallar
Kural 1

Rewrite service gerçek rewrite use-case sahibidir.

Kural 2

Rewrite ile cleanup aynı şey değildir.

Kural 3

Rewrite service task-aware routing kullanmalıdır.

Kural 4

Rewrite response ayrı standard contract taşır.

Girdi sözleşmesi
[
  'content' => 'string',
  'instruction' => 'string',
  'lang' => 'string',
  'tone' => 'string',
  'mode' => 'polish|expand|shorten|translate|restructure',
  'preserve_html' => true,
  'target_length' => 'short|medium|long',
  'provider' => 'string|null',
  'model' => 'string|null',
]
Çıktı sözleşmesi
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
    'timing' => [],
  ],
  'error' => null,
]
rewrite-service.php için pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main rewrite entry point.
 */
function aig_rewrite_service_run(array $input): array
{
    $startedAt = microtime(true);

    $normalized = aig_rewrite_service_normalize_input($input);
    $validation = aig_rewrite_service_validate_input($normalized);

    if (!$validation['ok']) {
        return aig_rewrite_service_build_error(
            $validation['error']['code'],
            $validation['error']['message'],
            ['stage' => 'validate']
        );
    }

    // Route for rewrite task
    if (!function_exists('aig_router_select')) {
        return aig_rewrite_service_build_error(
            'missing_router',
            'Rewrite router is unavailable.',
            ['stage' => 'route']
        );
    }

    $route = aig_router_select([
        'task' => 'article_rewrite',
        'preferred_provider' => $normalized['provider'],
        'preferred_model' => $normalized['model'],
        'lang' => $normalized['lang'],
        'mode' => $normalized['mode'],
    ]);

    if (empty($route['ok'])) {
        return aig_rewrite_service_build_error(
            $route['error']['code'] ?? 'rewrite_route_failed',
            $route['error']['message'] ?? 'Rewrite route selection failed.',
            ['stage' => 'route']
        );
    }

    // Build rewrite messages
    $messages = aig_rewrite_service_build_messages($normalized);

    // Generate rewritten content
    if (!function_exists('aig_llm_generate_messages')) {
        return aig_rewrite_service_build_error(
            'missing_llm_generate',
            'Rewrite LLM helper is unavailable.',
            ['stage' => 'generate']
        );
    }

    $result = aig_llm_generate_messages([
        'provider' => $route['provider'] ?? '',
        'model' => $route['model'] ?? '',
        'messages' => $messages,
        'task' => 'article_rewrite',
        'options' => $route['options'] ?? [],
    ]);

    if (empty($result['ok'])) {
        return aig_rewrite_service_build_error(
            $result['error']['code'] ?? 'rewrite_failed',
            $result['error']['message'] ?? 'Rewrite generation failed.',
            [
                'stage' => 'generate',
                'provider' => $route['provider'] ?? '',
                'model' => $route['model'] ?? '',
            ]
        );
    }

    $rewritten = trim((string) ($result['content'] ?? ''));
    if ($rewritten === '') {
        return aig_rewrite_service_build_error(
            'empty_rewrite',
            'Rewrite returned empty content.',
            ['stage' => 'generate']
        );
    }

    // Optional cleanup pipeline
    if (function_exists('aig_rewrite_pipeline_cleanup')) {
        $rewritten = aig_rewrite_pipeline_cleanup($rewritten, [
            'preserve_html' => $normalized['preserve_html'],
            'mode' => $normalized['mode'],
            'lang' => $normalized['lang'],
        ]);
    }

    return [
        'ok' => true,
        'rewrite' => [
            'content' => $rewritten,
            'html' => !empty($normalized['preserve_html']) ? $rewritten : nl2br(esc_html($rewritten)),
            'summary' => '',
            'lang' => $normalized['lang'],
        ],
        'meta' => [
            'provider' => $result['provider'] ?? ($route['provider'] ?? ''),
            'model' => $result['model'] ?? ($route['model'] ?? ''),
            'usage' => is_array($result['usage'] ?? null) ? $result['usage'] : [],
            'timing' => [
                'rewrite_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ],
        ],
        'error' => null,
    ];
}

function aig_rewrite_service_normalize_input(array $input): array
{
    return [
        'content' => trim((string) ($input['content'] ?? '')),
        'instruction' => trim((string) ($input['instruction'] ?? 'Improve clarity and readability.')),
        'lang' => trim((string) ($input['lang'] ?? 'tr')),
        'tone' => trim((string) ($input['tone'] ?? 'analytical')),
        'mode' => trim((string) ($input['mode'] ?? 'polish')),
        'preserve_html' => !empty($input['preserve_html']),
        'target_length' => trim((string) ($input['target_length'] ?? 'long')),
        'provider' => !empty($input['provider']) ? (string) $input['provider'] : null,
        'model' => !empty($input['model']) ? (string) $input['model'] : null,
    ];
}

function aig_rewrite_service_validate_input(array $input): array
{
    if ($input['content'] === '') {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_content',
                'message' => 'Rewrite content is required.',
            ],
        ];
    }

    return ['ok' => true];
}

function aig_rewrite_service_build_messages(array $input): array
{
    $system = "You are an expert editorial rewriting AI. Rewrite the text in {$input['lang']} with better clarity, structure, and readability while preserving the original meaning.";
    $user = "Mode: {$input['mode']}\n"
          . "Tone: {$input['tone']}\n"
          . "Target Length: {$input['target_length']}\n"
          . "Preserve HTML: " . ($input['preserve_html'] ? 'yes' : 'no') . "\n"
          . "Instruction: {$input['instruction']}\n\n"
          . "Text to rewrite:\n{$input['content']}";

    return [
        ['role' => 'system', 'content' => $system],
        ['role' => 'user', 'content' => $user],
    ];
}

function aig_rewrite_service_build_error(string $code, string $message, array $meta = []): array
{
    return [
        'ok' => false,
        'rewrite' => [
            'content' => '',
            'html' => '',
            'summary' => '',
            'lang' => 'tr',
        ],
        'meta' => $meta,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ];
}
Bu aşamadaki net kazanım

Artık elimizde doğrudan gerçek koda dönüşebilecek seviyede:

article service contract

article pipeline orchestration contract

rewrite service contract

var.

Bu üçü birlikte, modülün en bozuk görünen çekirdeğini netleştiriyor.

AŞAMA 7

core/ai-article-router.php için resmi seçim sözleşmesi + pseudo-code

core/ai-article-gateway.php için normalize çağrı sözleşmesi + pseudo-code

core/services/selftest-service.php için gerçek health contract + pseudo-code

Bunlar tamamlandığında:

article-service

pipeline

rewrite-service

router

gateway

selftest

çekirdeği kağıt üstünde neredeyse kod yazıma hazır hale gelmiş olacak.

core/ai-article-router.php
Resmi görev tanımı

ai-article-router.php, modülde task-aware provider/model seçim motoru olmalıdır.

Router’ın görevi:

gelen görevin türünü anlamak

uygun provider/model adaylarını toplamak

availability / capability / policy kontrolü yapmak

quality / cost / speed dengesine göre seçim yapmak

fallback chain üretmek

gateway’e açık bir karar objesi döndürmek

Router:

HTTP çağrısı yapmamalı

prompt yazmamalı

article parse etmemeli

panel kararı vermemeli

Router için bağlayıcı kurallar
Kural 1

Router yalnız karar verir, çağrı yapmaz.

Kural 2

Router task tipini zorunlu girdi olarak almalıdır.

Kural 3

Router disabled veya unavailable provider’ı primary seçmemelidir.

Kural 4

Router primary seçimin yanında fallback chain de döndürmelidir.

Kural 5

Router kararı router.json + providers.json + models.json + runtime availability birleşiminden oluşmalıdır.

Router girdi sözleşmesi
[
  'task' => 'article_generate|article_rewrite|seo_generate|title_generate|summary_generate',
  'preferred_provider' => 'string|null',
  'preferred_model' => 'string|null',
  'lang' => 'string',
  'length' => 'short|medium|long|null',
  'mode' => 'string|null',
  'quality_profile' => 'quality|balanced|speed|cost|null',
  'request_meta' => [],
]
Router çıktı sözleşmesi
[
  'ok' => true,
  'provider' => 'openrouter',
  'model' => 'anthropic/claude-x',
  'fallback_chain' => [
    ['provider' => 'openai', 'model' => 'gpt-4.1-mini'],
    ['provider' => 'groq', 'model' => 'llama-3.3-70b'],
  ],
  'options' => [
    'temperature' => 0.7,
    'max_tokens' => 2400,
    'timeout' => 45,
  ],
  'meta' => [
    'task' => 'article_generate',
    'selection_reason' => 'best_quality_available',
    'quality_profile' => 'quality',
  ],
  'error' => null,
]
Router için önerilen ana fonksiyonlar
1. aig_router_select(array $input): array

Ana giriş fonksiyonu.

2. aig_router_normalize_input(array $input): array

Task ve tercihleri normalize eder.

3. aig_router_load_policy(): array

router.json verisini okur.

4. aig_router_load_models(): array

models.json verisini okur.

5. aig_router_load_providers(): array

providers.json verisini okur.

6. aig_router_collect_candidates(array $input, array $policy, array $models, array $providers): array

Task’e uygun adayları toplar.

7. aig_router_filter_available_candidates(array $candidates): array

Disabled/unavailable adayları eler.

8. aig_router_score_candidates(array $candidates, array $input): array

Kalite/maliyet/hız skorlarını hesaplar.

9. aig_router_pick_primary_and_fallbacks(array $scored, array $input): array

Primary ve fallback zincirini üretir.

10. aig_router_build_error(...)

Standart hata üretir.

ai-article-router.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main router selection entry.
 */
function aig_router_select(array $input): array
{
    $normalized = aig_router_normalize_input($input);

    if ($normalized['task'] === '') {
        return aig_router_build_error(
            'missing_task',
            'Router task is required.',
            ['stage' => 'normalize']
        );
    }

    $policy    = aig_router_load_policy();
    $models    = aig_router_load_models();
    $providers = aig_router_load_providers();

    $candidates = aig_router_collect_candidates($normalized, $policy, $models, $providers);
    if (empty($candidates)) {
        return aig_router_build_error(
            'no_candidates',
            'No routing candidates found for task.',
            ['stage' => 'collect_candidates', 'task' => $normalized['task']]
        );
    }

    $available = aig_router_filter_available_candidates($candidates);
    if (empty($available)) {
        return aig_router_build_error(
            'no_available_candidates',
            'No available provider/model candidates remain after availability checks.',
            ['stage' => 'availability', 'task' => $normalized['task']]
        );
    }

    $scored = aig_router_score_candidates($available, $normalized);
    if (empty($scored)) {
        return aig_router_build_error(
            'routing_score_failed',
            'Could not score routing candidates.',
            ['stage' => 'score']
        );
    }

    $selection = aig_router_pick_primary_and_fallbacks($scored, $normalized);
    if (empty($selection['ok'])) {
        return $selection;
    }

    return [
        'ok' => true,
        'provider' => $selection['provider'],
        'model' => $selection['model'],
        'fallback_chain' => $selection['fallback_chain'],
        'options' => $selection['options'],
        'meta' => [
            'task' => $normalized['task'],
            'selection_reason' => $selection['selection_reason'],
            'quality_profile' => $normalized['quality_profile'],
        ],
        'error' => null,
    ];
}

function aig_router_normalize_input(array $input): array
{
    return [
        'task' => trim((string) ($input['task'] ?? '')),
        'preferred_provider' => !empty($input['preferred_provider']) ? (string) $input['preferred_provider'] : null,
        'preferred_model' => !empty($input['preferred_model']) ? (string) $input['preferred_model'] : null,
        'lang' => trim((string) ($input['lang'] ?? 'tr')),
        'length' => !empty($input['length']) ? (string) $input['length'] : null,
        'mode' => !empty($input['mode']) ? (string) $input['mode'] : null,
        'quality_profile' => trim((string) ($input['quality_profile'] ?? aig_router_default_quality_profile($input['task'] ?? ''))),
        'request_meta' => is_array($input['request_meta'] ?? null) ? $input['request_meta'] : [],
    ];
}

function aig_router_default_quality_profile(string $task): string
{
    switch ($task) {
        case 'article_generate':
        case 'article_rewrite':
            return 'quality';
        case 'seo_generate':
            return 'balanced';
        case 'title_generate':
        case 'summary_generate':
            return 'speed';
        default:
            return 'balanced';
    }
}

function aig_router_load_policy(): array
{
    return function_exists('aig_storage_read_json')
        ? (aig_storage_read_json('router.json') ?: [])
        : [];
}

function aig_router_load_models(): array
{
    return function_exists('aig_storage_read_json')
        ? (aig_storage_read_json('models.json') ?: [])
        : [];
}

function aig_router_load_providers(): array
{
    return function_exists('aig_storage_read_json')
        ? (aig_storage_read_json('providers.json') ?: [])
        : [];
}

function aig_router_collect_candidates(array $input, array $policy, array $models, array $providers): array
{
    $task = $input['task'];
    $routes = $policy['tasks'][$task]['candidates'] ?? [];

    $candidates = [];

    // 1. explicit preferred model/provider first
    if (!empty($input['preferred_provider']) && !empty($input['preferred_model'])) {
        $candidates[] = [
            'provider' => $input['preferred_provider'],
            'model' => $input['preferred_model'],
            'source' => 'explicit_preference',
        ];
    }

    // 2. policy-based candidates
    foreach ($routes as $route) {
        if (empty($route['provider']) || empty($route['model'])) {
            continue;
        }
        $candidates[] = [
            'provider' => (string) $route['provider'],
            'model' => (string) $route['model'],
            'source' => 'router_policy',
        ];
    }

    // 3. enrich with model/provider metadata
    foreach ($candidates as &$candidate) {
        $providerKey = $candidate['provider'];
        $modelKey = $candidate['model'];

        $candidate['provider_config'] = $providers[$providerKey] ?? [];
        $candidate['model_config'] = $models[$providerKey][$modelKey] ?? ($models[$modelKey] ?? []);
        $candidate['task'] = $task;
    }

    return $candidates;
}

function aig_router_filter_available_candidates(array $candidates): array
{
    $result = [];

    foreach ($candidates as $candidate) {
        $provider = $candidate['provider'];
        $model = $candidate['model'];
        $providerConfig = $candidate['provider_config'] ?? [];
        $modelConfig = $candidate['model_config'] ?? [];

        if (empty($provider) || empty($model)) {
            continue;
        }

        if (array_key_exists('enabled', $providerConfig) && !$providerConfig['enabled']) {
            continue;
        }

        if (function_exists('aig_provider_registry_is_available')) {
            if (!aig_provider_registry_is_available($provider)) {
                continue;
            }
        }

        $capabilities = $modelConfig['tasks'] ?? [];
        $task = $candidate['task'] ?? '';

        if (!empty($capabilities) && $task && !in_array($task, $capabilities, true)) {
            continue;
        }

        $result[] = $candidate;
    }

    return $result;
}

function aig_router_score_candidates(array $candidates, array $input): array
{
    $profile = $input['quality_profile'] ?? 'balanced';

    foreach ($candidates as &$candidate) {
        $modelConfig = $candidate['model_config'] ?? [];

        $quality = (int) ($modelConfig['quality_score'] ?? 70);
        $speed   = (int) ($modelConfig['speed_score'] ?? 70);
        $cost    = (int) ($modelConfig['cost_score'] ?? 70); // higher can mean cheaper if standardized that way

        switch ($profile) {
            case 'quality':
                $score = ($quality * 0.6) + ($speed * 0.2) + ($cost * 0.2);
                break;
            case 'speed':
                $score = ($speed * 0.6) + ($quality * 0.2) + ($cost * 0.2);
                break;
            case 'cost':
                $score = ($cost * 0.6) + ($quality * 0.2) + ($speed * 0.2);
                break;
            case 'balanced':
            default:
                $score = ($quality * 0.4) + ($speed * 0.3) + ($cost * 0.3);
                break;
        }

        $candidate['route_score'] = $score;
    }

    usort($candidates, function ($a, $b) {
        return ($b['route_score'] <=> $a['route_score']);
    });

    return $candidates;
}

function aig_router_pick_primary_and_fallbacks(array $scored, array $input): array
{
    if (empty($scored)) {
        return aig_router_build_error(
            'empty_scored_routes',
            'No scored routes available.',
            ['stage' => 'pick']
        );
    }

    $primary = $scored[0];
    $fallbacks = [];

    for ($i = 1; $i < count($scored); $i++) {
        $fallbacks[] = [
            'provider' => $scored[$i]['provider'],
            'model' => $scored[$i]['model'],
        ];
    }

    return [
        'ok' => true,
        'provider' => $primary['provider'],
        'model' => $primary['model'],
        'fallback_chain' => $fallbacks,
        'options' => [
            'temperature' => $primary['model_config']['temperature'] ?? aig_router_default_temperature_for_task($input['task'] ?? ''),
            'max_tokens' => $primary['model_config']['max_tokens'] ?? aig_router_default_max_tokens_for_task($input['task'] ?? ''),
            'timeout' => $primary['provider_config']['timeout'] ?? aig_router_default_timeout_for_task($input['task'] ?? ''),
        ],
        'selection_reason' => 'highest_route_score',
    ];
}

function aig_router_default_temperature_for_task(string $task): float
{
    switch ($task) {
        case 'article_generate':
            return 0.7;
        case 'article_rewrite':
            return 0.5;
        case 'seo_generate':
            return 0.4;
        default:
            return 0.5;
    }
}

function aig_router_default_max_tokens_for_task(string $task): int
{
    switch ($task) {
        case 'article_generate':
            return 2400;
        case 'article_rewrite':
            return 2200;
        case 'seo_generate':
            return 900;
        default:
            return 1200;
    }
}

function aig_router_default_timeout_for_task(string $task): int
{
    switch ($task) {
        case 'article_generate':
            return 45;
        case 'article_rewrite':
            return 40;
        case 'seo_generate':
            return 25;
        default:
            return 30;
    }
}

function aig_router_build_error(string $code, string $message, array $meta = []): array
{
    return [
        'ok' => false,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
        'meta' => $meta,
    ];
}
core/ai-article-gateway.php
Resmi görev tanımı

ai-article-gateway.php, router tarafından döndürülen kararı gerçek provider çağrısına çeviren normalize execution katmanı olmalıdır.

Gateway’in görevi:

provider instance almak

request payload hazırlamak

timeout / retry uygulamak

provider cevabını normalize etmek

usage alanlarını ortaklaştırmak

hata kodlarını standartlaştırmak

failover için okunabilir meta üretmek

Gateway:

task kararı vermemeli

prompt yazmamalı

article parse etmemeli

UI ile konuşmamalı

Gateway için bağlayıcı kurallar
Kural 1

Gateway provider-specific response’u üst katmana sızdırmamalı.

Kural 2

Retry ve timeout merkezi olmalı.

Kural 3

Standart hata kodları kullanılmalı.

Kural 4

Usage alanı normalize edilmeli.

Kural 5

Fallback bilgisi meta alanında görünür olmalı.

Gateway girdi sözleşmesi
[
  'provider' => 'string',
  'model' => 'string',
  'messages' => [],
  'task' => 'string',
  'options' => [
    'temperature' => 0.7,
    'max_tokens' => 2400,
    'timeout' => 45,
    'retry' => 1,
  ],
]
Gateway çıktı sözleşmesi
[
  'ok' => true,
  'content' => 'generated text',
  'provider' => 'openrouter',
  'model' => 'anthropic/claude-x',
  'usage' => [
    'prompt_tokens' => 0,
    'completion_tokens' => 0,
    'total_tokens' => 0,
  ],
  'meta' => [
    'duration_ms' => 0,
    'retries' => 0,
    'fallback_used' => false,
  ],
  'raw' => [],
  'error' => null,
]
Gateway için önerilen ana fonksiyonlar
1. aig_gateway_generate(array $input): array

Ana giriş fonksiyonu.

2. aig_gateway_normalize_input(array $input): array

Gelen input’u normalize eder.

3. aig_gateway_get_provider_instance(string $providerKey)

Registry’den provider örneği alır.

4. aig_gateway_call_provider($provider, array $input): array

Gerçek provider çağrısı.

5. aig_gateway_normalize_provider_response(array $response, array $input, int $durationMs, int $retries): array

Provider cevabını standartlaştırır.

6. aig_gateway_normalize_error(...)

Hata kodlarını standartlaştırır.

7. aig_gateway_should_retry(array $error): bool

Retry politikasını belirler.

ai-article-gateway.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main normalized gateway generate entry.
 */
function aig_gateway_generate(array $input): array
{
    $normalized = aig_gateway_normalize_input($input);

    if ($normalized['provider'] === '' || $normalized['model'] === '') {
        return aig_gateway_build_error(
            'missing_provider_or_model',
            'Provider and model are required for gateway generation.',
            ['stage' => 'normalize']
        );
    }

    $provider = aig_gateway_get_provider_instance($normalized['provider']);
    if (!$provider) {
        return aig_gateway_build_error(
            'missing_provider',
            'Provider instance could not be resolved.',
            ['stage' => 'provider_instance', 'provider' => $normalized['provider']]
        );
    }

    $maxRetries = (int) ($normalized['options']['retry'] ?? 1);
    $attempt = 0;
    $lastError = null;

    do {
        $attempt++;
        $startedAt = microtime(true);

        $response = aig_gateway_call_provider($provider, $normalized);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        if (!empty($response['ok'])) {
            return aig_gateway_normalize_provider_response(
                $response,
                $normalized,
                $durationMs,
                $attempt - 1
            );
        }

        $lastError = aig_gateway_normalize_error($response['error'] ?? [], $normalized);

        if (!aig_gateway_should_retry($lastError)) {
            break;
        }
    } while ($attempt <= $maxRetries);

    return [
        'ok' => false,
        'content' => '',
        'provider' => $normalized['provider'],
        'model' => $normalized['model'],
        'usage' => [
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
        ],
        'meta' => [
            'duration_ms' => 0,
            'retries' => max(0, $attempt - 1),
            'fallback_used' => false,
        ],
        'raw' => [],
        'error' => $lastError ?: [
            'code' => 'gateway_failed',
            'message' => 'Gateway failed without a normalized error.',
            'retryable' => false,
        ],
    ];
}

function aig_gateway_normalize_input(array $input): array
{
    $options = is_array($input['options'] ?? null) ? $input['options'] : [];

    return [
        'provider' => trim((string) ($input['provider'] ?? '')),
        'model' => trim((string) ($input['model'] ?? '')),
        'messages' => is_array($input['messages'] ?? null) ? $input['messages'] : [],
        'task' => trim((string) ($input['task'] ?? '')),
        'options' => [
            'temperature' => $options['temperature'] ?? 0.5,
            'max_tokens' => $options['max_tokens'] ?? 1200,
            'timeout' => $options['timeout'] ?? 30,
            'retry' => $options['retry'] ?? 1,
        ],
    ];
}

function aig_gateway_get_provider_instance(string $providerKey)
{
    if (function_exists('aig_provider_registry_make')) {
        return aig_provider_registry_make($providerKey);
    }
    return null;
}

function aig_gateway_call_provider($provider, array $input): array
{
    if (!method_exists($provider, 'chat')) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'provider_method_missing',
                'message' => 'Provider chat method is missing.',
            ],
        ];
    }

    return $provider->chat(
        $input['messages'],
        [
            'model' => $input['model'],
            'temperature' => $input['options']['temperature'],
            'max_tokens' => $input['options']['max_tokens'],
            'timeout' => $input['options']['timeout'],
            'task' => $input['task'],
        ]
    );
}

function aig_gateway_normalize_provider_response(array $response, array $input, int $durationMs, int $retries): array
{
    $usage = $response['usage'] ?? [];

    return [
        'ok' => true,
        'content' => (string) ($response['content'] ?? ''),
        'provider' => (string) ($response['provider'] ?? $input['provider']),
        'model' => (string) ($response['model'] ?? $input['model']),
        'usage' => [
            'prompt_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($usage['completion_tokens'] ?? 0),
            'total_tokens' => (int) ($usage['total_tokens'] ?? 0),
        ],
        'meta' => [
            'duration_ms' => $durationMs,
            'retries' => $retries,
            'fallback_used' => false,
        ],
        'raw' => $response['raw'] ?? [],
        'error' => null,
    ];
}

function aig_gateway_normalize_error(array $error, array $input): array
{
    $code = (string) ($error['code'] ?? 'gateway_error');
    $message = (string) ($error['message'] ?? 'Unknown gateway/provider error.');

    $retryableCodes = [
        'timeout',
        'http_error',
        'rate_limited',
        'provider_unavailable',
    ];

    return [
        'code' => $code,
        'message' => $message,
        'retryable' => in_array($code, $retryableCodes, true),
    ];
}

function aig_gateway_should_retry(array $error): bool
{
    return !empty($error['retryable']);
}

function aig_gateway_build_error(string $code, string $message, array $meta = []): array
{
    return [
        'ok' => false,
        'content' => '',
        'provider' => '',
        'model' => '',
        'usage' => [
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
        ],
        'meta' => $meta,
        'raw' => [],
        'error' => [
            'code' => $code,
            'message' => $message,
            'retryable' => false,
        ],
    ];
}
core/services/selftest-service.php
Resmi görev tanımı

selftest-service.php, modülde gerçek sağlık raporu üreten resmi kontrol servisi olmalıdır.

Bu servis:

static checks

contract checks

runtime checks

katmanlarını birleştirir.

Amaç:

loader eksiklerini görmek

provider/registry/router/gateway zincirini doğrulamak

storage/log erişimini doğrulamak

news/pipeline/rewrite/seo sağlık bilgisini üretmek

health.json için güvenilir özet oluşturmak

Selftest service için bağlayıcı kurallar
Kural 1

Selftest yalnız dosya varlığını değil, gerçek contract sağlığını da kontrol etmelidir.

Kural 2

Yanlış constant/fonksiyon ismi bekleyerek false negative üretmemelidir.

Kural 3

Quick ve full mod desteklenmelidir.

Kural 4

Sonuç shape’i tek standartta dönmelidir.

Kural 5

health.json güncellemesi kontrollü olmalıdır.

Selftest girdi sözleşmesi
[
  'mode' => 'quick|full',
]
Selftest çıktı sözleşmesi
[
  'ok' => true,
  'summary' => [
    'total' => 0,
    'passed' => 0,
    'warnings' => 0,
    'failed' => 0,
  ],
  'checks' => [],
  'meta' => [
    'mode' => 'quick',
    'generated_at' => '',
    'build' => '',
  ],
  'error' => null,
]
Selftest için önerilen ana fonksiyonlar
1. aig_selftest_service_run(array $input): array

Ana giriş fonksiyonu.

2. aig_selftest_service_normalize_input(array $input): array

Mode normalize eder.

3. aig_selftest_service_collect_static_checks(): array

Dosya/storage/log statik kontroller.

4. aig_selftest_service_collect_contract_checks(): array

Registry/router/gateway/function contract kontrolleri.

5. aig_selftest_service_collect_runtime_checks(string $mode): array

Quick/full runtime testleri.

6. aig_selftest_service_summarize(array $checks): array

Toplam pass/warn/fail sayısı üretir.

7. aig_selftest_service_write_health(array $result): void

health.json günceller.

selftest-service.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main selftest entry point.
 */
function aig_selftest_service_run(array $input): array
{
    $normalized = aig_selftest_service_normalize_input($input);

    $checks = [];

    $checks = array_merge($checks, aig_selftest_service_collect_static_checks());
    $checks = array_merge($checks, aig_selftest_service_collect_contract_checks());
    $checks = array_merge($checks, aig_selftest_service_collect_runtime_checks($normalized['mode']));

    $summary = aig_selftest_service_summarize($checks);

    $result = [
        'ok' => ($summary['failed'] === 0),
        'summary' => $summary,
        'checks' => $checks,
        'meta' => [
            'mode' => $normalized['mode'],
            'generated_at' => gmdate('c'),
            'build' => defined('AIG_MODULE_BUILD') ? AIG_MODULE_BUILD : '',
        ],
        'error' => null,
    ];

    aig_selftest_service_write_health($result);

    return $result;
}

function aig_selftest_service_normalize_input(array $input): array
{
    $mode = trim((string) ($input['mode'] ?? 'quick'));
    if (!in_array($mode, ['quick', 'full'], true)) {
        $mode = 'quick';
    }

    return ['mode' => $mode];
}

function aig_selftest_service_collect_static_checks(): array
{
    $checks = [];

    $checks[] = aig_selftest_check_file_exists('core_pipeline_file', defined('AIG_CORE_DIR') ? AIG_CORE_DIR . '/ai-article-pipeline.php' : '');
    $checks[] = aig_selftest_check_file_exists('router_file', defined('AIG_CORE_DIR') ? AIG_CORE_DIR . '/ai-article-router.php' : '');
    $checks[] = aig_selftest_check_file_exists('gateway_file', defined('AIG_CORE_DIR') ? AIG_CORE_DIR . '/ai-article-gateway.php' : '');
    $checks[] = aig_selftest_check_file_exists('providers_json', defined('AIG_STORAGE_DIR') ? AIG_STORAGE_DIR . '/providers.json' : '');
    $checks[] = aig_selftest_check_file_exists('router_json', defined('AIG_STORAGE_DIR') ? AIG_STORAGE_DIR . '/router.json' : '');
    $checks[] = aig_selftest_check_file_exists('models_json', defined('AIG_STORAGE_DIR') ? AIG_STORAGE_DIR . '/models.json' : '');
    $checks[] = aig_selftest_check_file_exists('news_sources_json', defined('AIG_DATA_DIR') ? AIG_DATA_DIR . '/news-sources.json' : '');

    $checks[] = aig_selftest_check_writable_dir('logs_dir_writable', defined('AIG_LOG_DIR') ? AIG_LOG_DIR : '');
    $checks[] = aig_selftest_check_writable_dir('storage_dir_writable', defined('AIG_STORAGE_DIR') ? AIG_STORAGE_DIR : '');

    return $checks;
}

function aig_selftest_service_collect_contract_checks(): array
{
    $checks = [];

    $checks[] = aig_selftest_check_function_exists('article_service_exists', 'aig_article_service_generate');
    $checks[] = aig_selftest_check_function_exists('rewrite_service_exists', 'aig_rewrite_service_run');
    $checks[] = aig_selftest_check_function_exists('pipeline_exists', 'aig_article_pipeline_run');
    $checks[] = aig_selftest_check_function_exists('router_exists', 'aig_router_select');
    $checks[] = aig_selftest_check_function_exists('gateway_exists', 'aig_gateway_generate');

    $registryOk = function_exists('aig_provider_registry_all') || function_exists('aig_provider_registry_make');
    $checks[] = [
        'group' => 'contract',
        'name' => 'provider_registry_contract',
        'status' => $registryOk ? 'pass' : 'fail',
        'message' => $registryOk ? 'Provider registry contract is available.' : 'Provider registry contract is missing.',
        'meta' => [],
    ];

    return $checks;
}

function aig_selftest_service_collect_runtime_checks(string $mode): array
{
    $checks = [];

    // Quick runtime check: router basic selection
    if (function_exists('aig_router_select')) {
        $route = aig_router_select(['task' => 'title_generate', 'lang' => 'tr']);
        $checks[] = [
            'group' => 'runtime',
            'name' => 'router_basic_selection',
            'status' => !empty($route['ok']) ? 'pass' : 'warning',
            'message' => !empty($route['ok']) ? 'Router returned a route.' : 'Router did not return a valid route.',
            'meta' => [
                'task' => 'title_generate',
            ],
        ];
    }

    // Quick runtime check: news source parsing
    if (function_exists('aig_storage_read_json') && defined('AIG_DATA_DIR')) {
        $newsSourcePath = AIG_DATA_DIR . '/news-sources.json';
        $parsed = file_exists($newsSourcePath);
        $checks[] = [
            'group' => 'runtime',
            'name' => 'news_sources_access',
            'status' => $parsed ? 'pass' : 'warning',
            'message' => $parsed ? 'News sources file is accessible.' : 'News sources file is not accessible.',
            'meta' => [],
        ];
    }

    if ($mode === 'full') {
        // Optional deeper checks
        if (function_exists('aig_router_select') && function_exists('aig_gateway_generate')) {
            $route = aig_router_select(['task' => 'summary_generate', 'lang' => 'tr']);
            if (!empty($route['ok'])) {
                $gateway = aig_gateway_generate([
                    'provider' => $route['provider'],
                    'model' => $route['model'],
                    'task' => 'summary_generate',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Return a short response.'],
                        ['role' => 'user', 'content' => 'Say hello in Turkish.'],
                    ],
                    'options' => $route['options'] ?? [],
                ]);

                $checks[] = [
                    'group' => 'runtime',
                    'name' => 'gateway_live_generate',
                    'status' => !empty($gateway['ok']) ? 'pass' : 'warning',
                    'message' => !empty($gateway['ok']) ? 'Gateway live generate succeeded.' : 'Gateway live generate failed.',
                    'meta' => [
                        'provider' => $route['provider'] ?? '',
                        'model' => $route['model'] ?? '',
                    ],
                ];
            }
        }
    }

    return $checks;
}

function aig_selftest_service_summarize(array $checks): array
{
    $summary = [
        'total' => count($checks),
        'passed' => 0,
        'warnings' => 0,
        'failed' => 0,
    ];

    foreach ($checks as $check) {
        $status = $check['status'] ?? 'info';
        if ($status === 'pass') {
            $summary['passed']++;
        } elseif ($status === 'warning') {
            $summary['warnings']++;
        } elseif ($status === 'fail') {
            $summary['failed']++;
        }
    }

    return $summary;
}

function aig_selftest_service_write_health(array $result): void
{
    if (!function_exists('aig_storage_write_json')) {
        return;
    }

    $health = [
        'last_run_at' => $result['meta']['generated_at'] ?? gmdate('c'),
        'overall_status' => !empty($result['ok']) ? 'pass' : 'fail',
        'summary' => $result['summary'] ?? [],
        'build' => $result['meta']['build'] ?? '',
    ];

    aig_storage_write_json('health.json', $health);
}

function aig_selftest_check_file_exists(string $name, string $path): array
{
    $exists = ($path !== '' && file_exists($path));

    return [
        'group' => 'static',
        'name' => $name,
        'status' => $exists ? 'pass' : 'fail',
        'message' => $exists ? 'File exists.' : 'Required file is missing.',
        'meta' => ['path' => $path],
    ];
}

function aig_selftest_check_writable_dir(string $name, string $path): array
{
    $ok = ($path !== '' && is_dir($path) && is_writable($path));

    return [
        'group' => 'static',
        'name' => $name,
        'status' => $ok ? 'pass' : 'warning',
        'message' => $ok ? 'Directory is writable.' : 'Directory is not writable or missing.',
        'meta' => ['path' => $path],
    ];
}

function aig_selftest_check_function_exists(string $name, string $functionName): array
{
    $exists = function_exists($functionName);

    return [
        'group' => 'contract',
        'name' => $name,
        'status' => $exists ? 'pass' : 'fail',
        'message' => $exists ? "Function {$functionName} exists." : "Function {$functionName} is missing.",
        'meta' => [],
    ];
}
Bu aşamadaki net kazanım

Şu anda elimizde artık şunların hepsi var:

Paket 1

ai-article-generator.php bootstrap iskeleti

ajax-handler.php endpoint contracts

core/services/article-service.php

core/ai-article-pipeline.php

core/services/rewrite-service.php

core/services/selftest-service.php

Paket 2

core/ai-article-router.php

core/ai-article-gateway.php

Yani modülün gerçek çekirdeği artık sözleşme + pseudo-code seviyesinde büyük ölçüde tanımlanmış durumda.

Şimdi tam da doğru yere geldik.
Bu aşamada artık üretim omurgasının bağlam ve kayıt merkezi tarafını tanımlıyoruz:

AŞAMA 8

core/ai-article-provider-registry.php için resmi sözleşme + pseudo-code

core/ai-article-context.php için resmi context pack sözleşmesi + pseudo-code

core/ai-article-outline.php için resmi outline contract + pseudo-code

Bu üçü çok kritik çünkü:

registry olmadan provider sistemi yarım kalır

context olmadan makale yüzeysel kalır

outline olmadan yapı dağılır

core/ai-article-provider-registry.php
Resmi görev tanımı

ai-article-provider-registry.php, modüldeki tüm provider’ların kayıt ve örnekleme merkezi olmalıdır.

Registry’nin görevi:

provider sınıflarını merkezi olarak tanımak

provider key → class eşlemesini tutmak

aktif provider listesini sunmak

provider instance üretmek

availability konusunda ortak giriş kapısı olmak

Registry:

route kararı vermemeli

maliyet puanı hesaplamamalı

prompt üretmemeli

article logic taşımamalı

Registry için bağlayıcı kurallar
Kural 1

Provider kayıtları tek merkezden yönetilmelidir.

Kural 2

Registry bootstrap sırasında garantili yüklenmelidir.

Kural 3

Registry, provider sınıfı yoksa sessiz geçmemeli; kontrollü başarısız olmalıdır.

Kural 4

Registry availability sorgusunda config + class + enabled durumunu birlikte değerlendirebilmelidir.

Kural 5

Registry yalnız provider yönetir; routing yapmaz.

Registry girdi/çıktı mantığı

Registry genelde servis gibi request almaz; ama aşağıdaki temel API’lere sahip olmalıdır:

Temel fonksiyonlar

aig_provider_registry_all(): array

aig_provider_registry_keys(): array

aig_provider_registry_has(string $key): bool

aig_provider_registry_make(string $key)

aig_provider_registry_is_available(string $key): bool

Registry iç sözleşme örneği
[
  'openai' => [
    'class' => 'AIG_Provider_OpenAI',
    'label' => 'OpenAI',
    'enabled' => true,
  ],
  'groq' => [
    'class' => 'AIG_Provider_Groq',
    'label' => 'Groq',
    'enabled' => true,
  ],
]

Not: enabled bilgisi runtime config ile birleşebilir; sabit registry içinde kalıcı olmak zorunda değildir.

ai-article-provider-registry.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return the full static provider registry map.
 */
function aig_provider_registry_all(): array
{
    return [
        'openai' => [
            'class' => 'AIG_Provider_OpenAI',
            'label' => 'OpenAI',
        ],
        'groq' => [
            'class' => 'AIG_Provider_Groq',
            'label' => 'Groq',
        ],
        'gemini' => [
            'class' => 'AIG_Provider_Gemini',
            'label' => 'Gemini',
        ],
        'deepseek' => [
            'class' => 'AIG_Provider_DeepSeek',
            'label' => 'DeepSeek',
        ],
        'mistral' => [
            'class' => 'AIG_Provider_Mistral',
            'label' => 'Mistral',
        ],
        'ollama' => [
            'class' => 'AIG_Provider_Ollama',
            'label' => 'Ollama',
        ],
        'openrouter' => [
            'class' => 'AIG_Provider_OpenRouter',
            'label' => 'OpenRouter',
        ],
    ];
}

/**
 * Return provider keys.
 */
function aig_provider_registry_keys(): array
{
    return array_keys(aig_provider_registry_all());
}

/**
 * Check whether provider key is registered.
 */
function aig_provider_registry_has(string $key): bool
{
    $all = aig_provider_registry_all();
    return isset($all[$key]);
}

/**
 * Create provider instance from registry.
 */
function aig_provider_registry_make(string $key)
{
    $all = aig_provider_registry_all();

    if (!isset($all[$key])) {
        return null;
    }

    $class = (string) ($all[$key]['class'] ?? '');
    if ($class === '' || !class_exists($class)) {
        return null;
    }

    return new $class();
}

/**
 * Check runtime availability of provider.
 */
function aig_provider_registry_is_available(string $key): bool
{
    if (!aig_provider_registry_has($key)) {
        return false;
    }

    $provider = aig_provider_registry_make($key);
    if (!$provider) {
        return false;
    }

    // optional provider-level availability check
    if (method_exists($provider, 'is_available')) {
        return (bool) $provider->is_available();
    }

    // fallback: check config if available
    if (function_exists('aig_storage_read_json')) {
        $providers = aig_storage_read_json('providers.json') ?: [];
        if (isset($providers[$key]['enabled']) && !$providers[$key]['enabled']) {
            return false;
        }
    }

    return true;
}
Registry için mimari not

Bu dosyanın değeri küçümsenmemeli.
Çünkü provider sistemi dağınık olduğunda:

router güvenilmez olur

gateway null provider ile kalır

selftest yanlış alarm verir

debug zorlaşır

Bu yüzden registry, küçük görünse de çekirdek altyapı taşıdır.

core/ai-article-context.php
Resmi görev tanımı

ai-article-context.php, modülün bağlam kurucu merkezi olmalıdır.

Bu dosyanın görevi:

topic/category/lang girdisini almak

news katmanını çalıştırmak

normalize haberleri çekmek

fact pack üretmek

source listesi çıkarmak

entity/keyword yoğunluğunu artırmak

LLM için prompt-ready context pack oluşturmak

Bu dosya:

provider seçmemeli

article generate etmemeli

AJAX bilmemeli

SEO üretmemeli

Context için bağlayıcı kurallar
Kural 1

Context yalnız ham haber listesi değil, zenginleştirilmiş bağlam üretmelidir.

Kural 2

Context pack LLM’e doğrudan yararlı olacak şekilde tasarlanmalıdır.

Kural 3

Topic/category/lang bilgisi context içinde görünür olmalıdır.

Kural 4

Source/fact/entity/keyword alanları mümkün olduğunca açık olmalıdır.

Kural 5

Context katmanı prompt yazmaz; prompt-ready veri üretir.

Context çıktı sözleşmesi
[
  'ok' => true,
  'context' => [
    'topic' => '',
    'category' => '',
    'lang' => 'tr',
    'editorial_angle' => '',
    'sources' => [],
    'normalized_news' => [],
    'fact_pack' => [
      'facts' => [],
      'entities' => [],
      'keywords' => [],
      'signals' => [],
    ],
    'summary_block' => '',
  ],
  'meta' => [
    'source_count' => 0,
    'fact_count' => 0,
  ],
  'error' => null,
]
Context için önerilen ana fonksiyonlar
1. aig_article_context_build(array $input): array

Ana giriş.

2. aig_article_context_collect_news(array $input): array

News collect çağrısı.

3. aig_article_context_normalize_news(array $news, array $input): array

Normalize edilmiş haber havuzu.

4. aig_article_context_build_fact_pack(array $normalized, array $input): array

Fact/entity/keyword çıkarımı.

5. aig_article_context_build_editorial_angle(array $factPack, array $input): string

Makalenin editorial yönünü kurar.

6. aig_article_context_build_summary_block(array $normalized, array $factPack): string

LLM için kısa bağlam özeti.

7. aig_article_context_extract_sources(array $normalized): array

Kaynak listesi.

ai-article-context.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main context builder entry.
 */
function aig_article_context_build(array $input): array
{
    $topic = trim((string) ($input['topic'] ?? ''));
    $category = trim((string) ($input['category'] ?? 'general'));
    $lang = trim((string) ($input['lang'] ?? 'tr'));

    if ($topic === '') {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_topic',
                'message' => 'Topic is required to build article context.',
            ],
        ];
    }

    // 1. Collect news
    $newsResult = aig_article_context_collect_news($input);
    if (empty($newsResult['ok'])) {
        return [
            'ok' => false,
            'error' => $newsResult['error'] ?? [
                'code' => 'news_collect_failed',
                'message' => 'News collect failed.',
            ],
        ];
    }

    $rawNews = is_array($newsResult['items'] ?? null) ? $newsResult['items'] : [];

    // 2. Normalize news
    $normalized = aig_article_context_normalize_news($rawNews, $input);

    // 3. Build fact pack
    $factPack = aig_article_context_build_fact_pack($normalized, $input);

    // 4. Editorial angle
    $editorialAngle = aig_article_context_build_editorial_angle($factPack, $input);

    // 5. Summary block
    $summaryBlock = aig_article_context_build_summary_block($normalized, $factPack);

    // 6. Source list
    $sources = aig_article_context_extract_sources($normalized);

    return [
        'ok' => true,
        'context' => [
            'topic' => $topic,
            'category' => $category,
            'lang' => $lang,
            'editorial_angle' => $editorialAngle,
            'sources' => $sources,
            'normalized_news' => $normalized,
            'fact_pack' => $factPack,
            'summary_block' => $summaryBlock,
        ],
        'meta' => [
            'source_count' => count($sources),
            'fact_count' => count($factPack['facts'] ?? []),
        ],
        'error' => null,
    ];
}

/**
 * Collect raw news items.
 */
function aig_article_context_collect_news(array $input): array
{
    if (!function_exists('aig_news_collect')) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_news_collector',
                'message' => 'News collector function is missing.',
            ],
        ];
    }

    return aig_news_collect([
        'topic' => $input['topic'] ?? '',
        'category' => $input['category'] ?? 'general',
        'lang' => $input['lang'] ?? 'tr',
    ]);
}

/**
 * Normalize collected news items.
 */
function aig_article_context_normalize_news(array $news, array $input): array
{
    if (function_exists('aig_news_normalize_batch')) {
        $result = aig_news_normalize_batch($news, [
            'lang' => $input['lang'] ?? 'tr',
            'category' => $input['category'] ?? 'general',
        ]);
        if (is_array($result)) {
            return $result;
        }
    }

    // fallback shallow normalization
    $normalized = [];

    foreach ($news as $item) {
        $normalized[] = [
            'title' => (string) ($item['title'] ?? ''),
            'url' => (string) ($item['url'] ?? ''),
            'source' => (string) ($item['source'] ?? ''),
            'summary' => (string) ($item['summary'] ?? ''),
            'published_at' => (string) ($item['published_at'] ?? ''),
            'language' => (string) ($item['language'] ?? ($input['lang'] ?? 'tr')),
            'category' => (string) ($item['category'] ?? ($input['category'] ?? 'general')),
            'image' => (string) ($item['image'] ?? ''),
        ];
    }

    return $normalized;
}

/**
 * Build fact pack from normalized news.
 */
function aig_article_context_build_fact_pack(array $normalized, array $input): array
{
    if (function_exists('aig_news_fact_pack_build')) {
        $result = aig_news_fact_pack_build($normalized, $input);
        if (is_array($result)) {
            return $result;
        }
    }

    // fallback simple fact pack
    $facts = [];
    $entities = [];
    $keywords = [];

    foreach ($normalized as $item) {
        if (!empty($item['title'])) {
            $facts[] = $item['title'];
        }
        if (!empty($item['source'])) {
            $entities[] = $item['source'];
        }
    }

    $entities = array_values(array_unique($entities));

    return [
        'facts' => array_slice($facts, 0, 12),
        'entities' => array_slice($entities, 0, 10),
        'keywords' => array_slice($keywords, 0, 12),
        'signals' => [],
    ];
}

/**
 * Derive editorial angle from fact pack and input.
 */
function aig_article_context_build_editorial_angle(array $factPack, array $input): string
{
    $category = (string) ($input['category'] ?? 'general');

    switch ($category) {
        case 'tech':
            return 'Explain the most important technological developments, why they matter, and what they may change next.';
        case 'finance':
            return 'Focus on market implications, economic meaning, and likely next-step effects.';
        case 'ai':
            return 'Highlight model, product, and ecosystem implications with balanced analysis.';
        default:
            return 'Synthesize the main developments, explain their significance, and present a structured editorial overview.';
    }
}

/**
 * Build compact summary block for prompt use.
 */
function aig_article_context_build_summary_block(array $normalized, array $factPack): string
{
    $lines = [];

    $facts = array_slice($factPack['facts'] ?? [], 0, 5);
    foreach ($facts as $fact) {
        $lines[] = '- ' . trim((string) $fact);
    }

    if (empty($lines)) {
        foreach (array_slice($normalized, 0, 5) as $item) {
            if (!empty($item['title'])) {
                $lines[] = '- ' . trim((string) $item['title']);
            }
        }
    }

    return implode("\n", $lines);
}

/**
 * Extract visible source list from normalized news.
 */
function aig_article_context_extract_sources(array $normalized): array
{
    $sources = [];

    foreach ($normalized as $item) {
        $source = trim((string) ($item['source'] ?? ''));
        $url = trim((string) ($item['url'] ?? ''));

        if ($source === '' && $url === '') {
            continue;
        }

        $key = md5($source . '|' . $url);
        $sources[$key] = [
            'source' => $source,
            'url' => $url,
        ];
    }

    return array_values($sources);
}
Context için mimari not

Bu dosya güçsüz kalırsa article’lar:

genel geçer olur

kısa kalır

“hakiki haber analizi” yerine boş sentez hissi verir

Yani article kalitesinin gerçek başlangıç noktası çoğu zaman prompt değil, context yoğunluğudur.

core/ai-article-outline.php
Resmi görev tanımı

ai-article-outline.php, article için iskelet ve section planı üretmelidir.

Bu dosyanın görevi:

template’e göre yazı iskeleti kurmak

bağlamı başlıklara dönüştürmek

giriş/gelişme/analiz/sonuç akışını düzenlemek

article’ın dağılmasını önlemek

Bu dosya:

article body yazmamalı

provider çağırmamalı

SEO üretmemeli

Outline için bağlayıcı kurallar
Kural 1

Outline yalnız yapı üretir, içerik üretmez.

Kural 2

Template-aware çalışmalıdır.

Kural 3

Context içindeki fact/theme/signal verisini section planına çevirebilmelidir.

Kural 4

Her template için minimum section standardı olmalıdır.

Outline çıktı sözleşmesi
[
  'ok' => true,
  'outline' => [
    'template' => 'news_analysis',
    'title_hint' => '',
    'sections' => [
      [
        'key' => 'intro',
        'heading' => 'Giriş',
        'goal' => 'Konuyu ve ana çerçeveyi aç',
      ],
    ],
  ],
  'meta' => [
    'section_count' => 0,
  ],
  'error' => null,
]
Outline için önerilen ana fonksiyonlar
1. aig_article_outline_build(array $context, array $input): array

Ana giriş.

2. aig_article_outline_resolve_template(array $input): string

Template belirler.

3. aig_article_outline_build_sections(string $template, array $context, array $input): array

Section planını üretir.

4. aig_article_outline_build_title_hint(array $context, array $input): string

Title yönü verir.

ai-article-outline.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main outline builder.
 */
function aig_article_outline_build(array $context, array $input): array
{
    $template = aig_article_outline_resolve_template($input);
    $sections = aig_article_outline_build_sections($template, $context, $input);
    $titleHint = aig_article_outline_build_title_hint($context, $input);

    if (empty($sections)) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'empty_outline',
                'message' => 'Outline section generation returned empty output.',
            ],
        ];
    }

    return [
        'ok' => true,
        'outline' => [
            'template' => $template,
            'title_hint' => $titleHint,
            'sections' => $sections,
        ],
        'meta' => [
            'section_count' => count($sections),
        ],
        'error' => null,
    ];
}

/**
 * Resolve requested or default template.
 */
function aig_article_outline_resolve_template(array $input): string
{
    $template = trim((string) ($input['template'] ?? 'news_analysis'));

    $allowed = [
        'news_analysis',
        'breaking_news',
        'roundup',
        'guide',
        'review',
    ];

    if (!in_array($template, $allowed, true)) {
        $template = 'news_analysis';
    }

    return $template;
}

/**
 * Build template-aware sections.
 */
function aig_article_outline_build_sections(string $template, array $context, array $input): array
{
    switch ($template) {
        case 'breaking_news':
            return [
                [
                    'key' => 'intro',
                    'heading' => 'Gelişmenin Özeti',
                    'goal' => 'Olayı hızlı ve açık şekilde sun.',
                ],
                [
                    'key' => 'what_happened',
                    'heading' => 'Neler Yaşandı?',
                    'goal' => 'Temel gelişmeleri kronolojik olarak açıkla.',
                ],
                [
                    'key' => 'why_it_matters',
                    'heading' => 'Neden Önemli?',
                    'goal' => 'Olayın etkisini ve önemini anlat.',
                ],
                [
                    'key' => 'outlook',
                    'heading' => 'Bundan Sonra Ne Olabilir?',
                    'goal' => 'Yakın dönem beklentilerini özetle.',
                ],
            ];

        case 'roundup':
            return [
                [
                    'key' => 'intro',
                    'heading' => 'Genel Çerçeve',
                    'goal' => 'Gündemin genel fotoğrafını çiz.',
                ],
                [
                    'key' => 'top_items',
                    'heading' => 'Öne Çıkan Başlıklar',
                    'goal' => 'En önemli gelişmeleri gruplandır.',
                ],
                [
                    'key' => 'patterns',
                    'heading' => 'Ortak Eğilimler',
                    'goal' => 'Haberler arasındaki ortak sinyalleri anlat.',
                ],
                [
                    'key' => 'conclusion',
                    'heading' => 'Sonuç',
                    'goal' => 'Genel değerlendirme yap.',
                ],
            ];

        case 'guide':
            return [
                [
                    'key' => 'intro',
                    'heading' => 'Giriş',
                    'goal' => 'Konunun kapsamını tanımla.',
                ],
                [
                    'key' => 'core_explanation',
                    'heading' => 'Temel Açıklama',
                    'goal' => 'Konunun ana mantığını açıkla.',
                ],
                [
                    'key' => 'practical_points',
                    'heading' => 'Pratik Noktalar',
                    'goal' => 'Okuyucu için uygulanabilir çıkarımlar sun.',
                ],
                [
                    'key' => 'final_takeaway',
                    'heading' => 'Özet Sonuç',
                    'goal' => 'Kısa ve net sonuç çıkar.',
                ],
            ];

        case 'review':
            return [
                [
                    'key' => 'intro',
                    'heading' => 'İlk Bakış',
                    'goal' => 'İncelenen konuyu tanıt.',
                ],
                [
                    'key' => 'strengths',
                    'heading' => 'Güçlü Yönler',
                    'goal' => 'Olumlu tarafları açıkla.',
                ],
                [
                    'key' => 'weaknesses',
                    'heading' => 'Zayıf Yönler',
                    'goal' => 'Eksikleri ve riskleri anlat.',
                ],
                [
                    'key' => 'verdict',
                    'heading' => 'Genel Değerlendirme',
                    'goal' => 'Dengeli sonuç ver.',
                ],
            ];

        case 'news_analysis':
        default:
            return [
                [
                    'key' => 'intro',
                    'heading' => 'Giriş',
                    'goal' => 'Konuyu ve ana bağlamı tanıt.',
                ],
                [
                    'key' => 'main_developments',
                    'heading' => 'Öne Çıkan Gelişmeler',
                    'goal' => 'Temel haberleri düzenli biçimde açıkla.',
                ],
                [
                    'key' => 'why_it_matters',
                    'heading' => 'Neden Önemli?',
                    'goal' => 'Bu gelişmelerin etkisini analiz et.',
                ],
                [
                    'key' => 'forward_look',
                    'heading' => 'İleriye Dönük Beklentiler',
                    'goal' => 'Muhtemel sonraki adımları değerlendir.',
                ],
                [
                    'key' => 'conclusion',
                    'heading' => 'Sonuç',
                    'goal' => 'Dengeli ve net kapanış yap.',
                ],
            ];
    }
}

/**
 * Build title hint from context.
 */
function aig_article_outline_build_title_hint(array $context, array $input): string
{
    $topic = (string) ($context['topic'] ?? ($input['topic'] ?? ''));
    $category = (string) ($context['category'] ?? ($input['category'] ?? 'general'));

    if ($category === 'tech') {
        return $topic . ' — Kapsamlı Analiz';
    }

    if ($category === 'finance') {
        return $topic . ' — Piyasa ve Etki Analizi';
    }

    if ($category === 'ai') {
        return $topic . ' — Yapay Zekâ Gelişmeleri ve Etkileri';
    }

    return $topic . ' — Editoryal Değerlendirme';
}
Outline için mimari not

Bu dosya iyi tasarlanmazsa sonuçlar:

paragraflar yığılmış gibi görünür

makale yönsüz olur

giriş ve sonuç zayıf kalır

okuyucuya “gelişme akışı” hissi vermez

Yani outline, article’ın estetik iskeletidir.

Bu aşamadaki net kazanım

Şu an elimizde artık şunlar da var:

Paket 2 tamamlanmaya çok yakın

core/ai-article-router.php

core/ai-article-gateway.php

core/ai-article-provider-registry.php

Paket 3’ün çekirdeği başladı

core/ai-article-context.php

core/ai-article-outline.php

Bu noktada article üretim zincirinin büyük taşları kağıt üstünde resmileşti.

AŞAMA 9

core/news/news-sources.php için kaynak sözleşmesi + pseudo-code

core/news/news-collector.php için collect sözleşmesi + pseudo-code

core/news/news-fact-pack.php için fact/entity/keyword sözleşmesi + pseudo-code

Bu aşama çok önemli çünkü context katmanı ancak şu üç şey sağlamsa güçlü olur:

doğru kaynak tanımı

deterministik toplama

zengin fact pack

core/news/news-sources.php
Resmi görev tanımı

news-sources.php, modüldeki haber kaynaklarının tanım ve erişim merkezi olmalıdır.

Bu dosyanın görevi:

data/news-sources.json dosyasını okumak

kategori bazlı kaynakları döndürmek

aktif/pasif kaynak filtresi yapmak

gerekirse dile ve source type’a göre filtrelemek

collector’a tek biçimli kaynak listesi vermek

Bu dosya:

HTTP isteği yapmamalı

haber normalize etmemeli

article logic taşımamalı

fact pack üretmemeli

Sources için bağlayıcı kurallar
Kural 1

Kaynak tanımı tek resmi yerden okunmalıdır: data/news-sources.json

Kural 2

Kaynaklar kategori bazlı erişilebilir olmalıdır.

Kural 3

Aktif/pasif kaynak filtresi standart olmalıdır.

Kural 4

Kaynak şeması bozuksa sessiz geçmek yerine kontrollü hata/veri temizleme yapılmalıdır.

Kural 5

Collector, kendi içinde dağınık kaynak listeleri taşımamalıdır; sources katmanını kullanmalıdır.

news-sources.json için önerilen mantıksal şema

Bu gerçek dosyada birebir böyle olmak zorunda değil, ama resmi hedef şema bu mantıkta olmalı:

{
  "schema_version": "1.0.0",
  "categories": {
    "tech": [
      {
        "id": "techcrunch_rss",
        "label": "TechCrunch",
        "type": "rss",
        "enabled": true,
        "lang": "en",
        "url": "https://techcrunch.com/feed/"
      }
    ],
    "ai": [
      {
        "id": "openai_blog",
        "label": "OpenAI Blog",
        "type": "rss",
        "enabled": true,
        "lang": "en",
        "url": "https://openai.com/news/rss.xml"
      }
    ]
  }
}
Sources çıktı sözleşmesi

Kategori bazlı kaynak okuma çıktısı şu mantıkta olmalıdır:

[
  'ok' => true,
  'sources' => [
    [
      'id' => 'techcrunch_rss',
      'label' => 'TechCrunch',
      'type' => 'rss',
      'enabled' => true,
      'lang' => 'en',
      'url' => 'https://techcrunch.com/feed/',
      'category' => 'tech',
    ],
  ],
  'meta' => [
    'category' => 'tech',
    'count' => 1,
  ],
  'error' => null,
]
news-sources.php için önerilen ana fonksiyonlar
1. aig_news_sources_load(): array

JSON dosyasını okur.

2. aig_news_sources_for_category(string $category, array $filters = []): array

Kategoriye göre kaynak döndürür.

3. aig_news_sources_filter(array $sources, array $filters = []): array

Enabled/lang/type filtreleri uygular.

4. aig_news_sources_flatten_all(): array

Tüm kategorileri tek listeye indirir.

5. aig_news_sources_build_error(...)

Standart hata üretir.

news-sources.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load raw news source JSON.
 */
function aig_news_sources_load(): array
{
    if (!defined('AIG_DATA_DIR')) {
        return aig_news_sources_build_error(
            'missing_data_dir',
            'Data directory constant is missing.',
            ['stage' => 'load']
        );
    }

    $path = AIG_DATA_DIR . '/news-sources.json';
    if (!file_exists($path)) {
        return aig_news_sources_build_error(
            'missing_news_sources_json',
            'news-sources.json file is missing.',
            ['path' => $path]
        );
    }

    $raw = file_get_contents($path);
    $json = json_decode((string) $raw, true);

    if (!is_array($json)) {
        return aig_news_sources_build_error(
            'invalid_news_sources_json',
            'news-sources.json could not be parsed.',
            ['path' => $path]
        );
    }

    return [
        'ok' => true,
        'data' => $json,
        'meta' => [
            'path' => $path,
        ],
        'error' => null,
    ];
}

/**
 * Return filtered sources for a given category.
 */
function aig_news_sources_for_category(string $category, array $filters = []): array
{
    $loaded = aig_news_sources_load();
    if (empty($loaded['ok'])) {
        return $loaded;
    }

    $data = $loaded['data'];
    $category = trim($category);

    $items = $data['categories'][$category] ?? [];
    if (!is_array($items)) {
        $items = [];
    }

    $sources = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $sources[] = [
            'id' => (string) ($item['id'] ?? ''),
            'label' => (string) ($item['label'] ?? ''),
            'type' => (string) ($item['type'] ?? 'rss'),
            'enabled' => array_key_exists('enabled', $item) ? (bool) $item['enabled'] : true,
            'lang' => (string) ($item['lang'] ?? ''),
            'url' => (string) ($item['url'] ?? ''),
            'category' => $category,
        ];
    }

    $sources = aig_news_sources_filter($sources, $filters);

    return [
        'ok' => true,
        'sources' => array_values($sources),
        'meta' => [
            'category' => $category,
            'count' => count($sources),
        ],
        'error' => null,
    ];
}

/**
 * Filter sources by enabled/lang/type.
 */
function aig_news_sources_filter(array $sources, array $filters = []): array
{
    $onlyEnabled = array_key_exists('enabled', $filters) ? (bool) $filters['enabled'] : true;
    $lang = trim((string) ($filters['lang'] ?? ''));
    $type = trim((string) ($filters['type'] ?? ''));

    $filtered = [];

    foreach ($sources as $source) {
        if ($onlyEnabled && empty($source['enabled'])) {
            continue;
        }

        if ($lang !== '' && !empty($source['lang']) && $source['lang'] !== $lang) {
            continue;
        }

        if ($type !== '' && !empty($source['type']) && $source['type'] !== $type) {
            continue;
        }

        if (empty($source['url'])) {
            continue;
        }

        $filtered[] = $source;
    }

    return $filtered;
}

/**
 * Flatten all category sources into one list.
 */
function aig_news_sources_flatten_all(): array
{
    $loaded = aig_news_sources_load();
    if (empty($loaded['ok'])) {
        return $loaded;
    }

    $data = $loaded['data'];
    $categories = $data['categories'] ?? [];
    $all = [];

    foreach ($categories as $category => $items) {
        if (!is_array($items)) {
            continue;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $all[] = [
                'id' => (string) ($item['id'] ?? ''),
                'label' => (string) ($item['label'] ?? ''),
                'type' => (string) ($item['type'] ?? 'rss'),
                'enabled' => array_key_exists('enabled', $item) ? (bool) $item['enabled'] : true,
                'lang' => (string) ($item['lang'] ?? ''),
                'url' => (string) ($item['url'] ?? ''),
                'category' => (string) $category,
            ];
        }
    }

    return [
        'ok' => true,
        'sources' => $all,
        'meta' => [
            'count' => count($all),
        ],
        'error' => null,
    ];
}

function aig_news_sources_build_error(string $code, string $message, array $meta = []): array
{
    return [
        'ok' => false,
        'sources' => [],
        'meta' => $meta,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ];
}
core/news/news-collector.php
Resmi görev tanımı

news-collector.php, kaynaklardan ham haber verisi toplayan toplama motoru olmalıdır.

Bu dosyanın görevi:

kategoriye uygun kaynakları sources katmanından almak

RSS/API kaynaklarını dolaşmak

ham item listesi toplamak

cache uygunsa cache kullanmak

kontrollü timeout ve limit uygulamak

toplama sonucunu tek biçimde döndürmek

Bu dosya:

article body üretmemeli

LLM çağırmamalı

SEO üretmemeli

normalize işini sahiplenmemeli

Collector için bağlayıcı kurallar
Kural 1

Collector yalnız raw/ham source item toplar.

Kural 2

Collector kendi kaynak listesini hardcode etmemelidir.

Kural 3

Collector cache katmanıyla uyumlu çalışmalıdır.

Kural 4

Her kaynaktan gelen veri ortak minimum alanlara indirgenebilmelidir.

Kural 5

Collector başarısız kaynakları loglayabilir ama tüm akışı tek hata yüzünden çökertmemelidir.

Collector çıktı sözleşmesi
[
  'ok' => true,
  'items' => [
    [
      'title' => '',
      'url' => '',
      'source' => '',
      'summary' => '',
      'published_at' => '',
      'language' => '',
      'category' => '',
      'image' => '',
      'raw' => [],
    ],
  ],
  'meta' => [
    'category' => 'tech',
    'source_count' => 5,
    'item_count' => 20,
    'cache_used' => false,
  ],
  'error' => null,
]
Collector için önerilen ana fonksiyonlar
1. aig_news_collect(array $input): array

Ana giriş.

2. aig_news_collect_resolve_sources(array $input): array

Kategoriye göre kaynakları bulur.

3. aig_news_collect_try_cache(array $input): array|null

Cache varsa okur.

4. aig_news_collect_from_source(array $source, array $input): array

Tek kaynaktan toplar.

5. aig_news_collect_parse_rss(string $url, array $source, array $input): array

RSS toplama helper’ı.

6. aig_news_collect_merge_items(array $batches): array

Tüm kaynak item’larını birleştirir.

7. aig_news_collect_dedupe(array $items): array

Duplicate temizler.

8. aig_news_collect_store_cache(array $input, array $result): void

Cache yazar.

news-collector.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main news collection entry.
 */
function aig_news_collect(array $input): array
{
    $category = trim((string) ($input['category'] ?? 'general'));
    $lang = trim((string) ($input['lang'] ?? 'tr'));

    // 1. cache first
    $cached = aig_news_collect_try_cache($input);
    if (is_array($cached) && !empty($cached['ok'])) {
        $cached['meta']['cache_used'] = true;
        return $cached;
    }

    // 2. resolve sources
    $sourcesResult = aig_news_collect_resolve_sources($input);
    if (empty($sourcesResult['ok'])) {
        return [
            'ok' => false,
            'items' => [],
            'meta' => [
                'category' => $category,
                'source_count' => 0,
                'item_count' => 0,
                'cache_used' => false,
            ],
            'error' => $sourcesResult['error'] ?? [
                'code' => 'sources_resolve_failed',
                'message' => 'Could not resolve news sources.',
            ],
        ];
    }

    $sources = $sourcesResult['sources'];
    $batches = [];

    foreach ($sources as $source) {
        $batch = aig_news_collect_from_source($source, $input);
        if (!empty($batch['ok']) && is_array($batch['items'] ?? null)) {
            $batches[] = $batch['items'];
        }
    }

    // 3. merge and dedupe
    $items = aig_news_collect_merge_items($batches);
    $items = aig_news_collect_dedupe($items);

    $result = [
        'ok' => true,
        'items' => $items,
        'meta' => [
            'category' => $category,
            'lang' => $lang,
            'source_count' => count($sources),
            'item_count' => count($items),
            'cache_used' => false,
        ],
        'error' => null,
    ];

    // 4. store cache
    aig_news_collect_store_cache($input, $result);

    return $result;
}

/**
 * Resolve enabled sources for given category.
 */
function aig_news_collect_resolve_sources(array $input): array
{
    if (!function_exists('aig_news_sources_for_category')) {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_news_sources',
                'message' => 'News sources resolver is missing.',
            ],
        ];
    }

    return aig_news_sources_for_category(
        (string) ($input['category'] ?? 'general'),
        [
            'enabled' => true,
            // Dil filtresi çok sert uygulanmamalı; yabancı kaynakları da alabiliriz.
        ]
    );
}

/**
 * Try reading cached collection result.
 */
function aig_news_collect_try_cache(array $input): ?array
{
    if (!function_exists('aig_news_cache_get')) {
        return null;
    }

    return aig_news_cache_get([
        'topic' => $input['topic'] ?? '',
        'category' => $input['category'] ?? 'general',
        'lang' => $input['lang'] ?? 'tr',
    ]);
}

/**
 * Collect items from one source definition.
 */
function aig_news_collect_from_source(array $source, array $input): array
{
    $type = (string) ($source['type'] ?? 'rss');
    $url = (string) ($source['url'] ?? '');

    if ($url === '') {
        return ['ok' => false, 'items' => []];
    }

    switch ($type) {
        case 'rss':
        default:
            return aig_news_collect_parse_rss($url, $source, $input);
    }
}

/**
 * Parse RSS feed into raw item list.
 */
function aig_news_collect_parse_rss(string $url, array $source, array $input): array
{
    // Gerçek kodda fetch + XML parse yapılacak.
    // Burada resmi davranış iskeleti veriyoruz.

    $items = [];

    if (function_exists('fetch_feed')) {
        $feed = fetch_feed($url);

        if (!is_wp_error($feed)) {
            $max = min((int) $feed->get_item_quantity(10), 10);
            $rssItems = $feed->get_items(0, $max);

            foreach ($rssItems as $rssItem) {
                $items[] = [
                    'title' => (string) $rssItem->get_title(),
                    'url' => (string) $rssItem->get_link(),
                    'source' => (string) ($source['label'] ?? ''),
                    'summary' => (string) $rssItem->get_description(),
                    'published_at' => (string) $rssItem->get_date(DATE_ATOM),
                    'language' => (string) ($source['lang'] ?? ''),
                    'category' => (string) ($source['category'] ?? ($input['category'] ?? 'general')),
                    'image' => '',
                    'raw' => [],
                ];
            }
        }
    }

    return [
        'ok' => true,
        'items' => $items,
    ];
}

/**
 * Merge batch arrays into one array.
 */
function aig_news_collect_merge_items(array $batches): array
{
    $items = [];

    foreach ($batches as $batch) {
        if (!is_array($batch)) {
            continue;
        }
        foreach ($batch as $item) {
            if (!is_array($item)) {
                continue;
            }
            $items[] = $item;
        }
    }

    return $items;
}

/**
 * Remove duplicates by title+url.
 */
function aig_news_collect_dedupe(array $items): array
{
    $seen = [];
    $result = [];

    foreach ($items as $item) {
        $key = md5(
            trim((string) ($item['title'] ?? '')) . '|' .
            trim((string) ($item['url'] ?? ''))
        );

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $result[] = $item;
    }

    return $result;
}

/**
 * Store collection result in cache layer.
 */
function aig_news_collect_store_cache(array $input, array $result): void
{
    if (!function_exists('aig_news_cache_set')) {
        return;
    }

    aig_news_cache_set([
        'topic' => $input['topic'] ?? '',
        'category' => $input['category'] ?? 'general',
        'lang' => $input['lang'] ?? 'tr',
    ], $result);
}
Collector için mimari not

Collector iyi tasarlanmazsa şu sorunlar büyür:

aynı haber tekrar tekrar gelir

konu dışı içerik çok olur

cache kaotik hale gelir

context gürültülü veriyle dolar

Yani collector, context’in veri kalitesini doğrudan belirler.

core/news/news-fact-pack.php
Resmi görev tanımı

news-fact-pack.php, normalize edilmiş haberlerden fact/entity/keyword/signal yoğunluğu çıkaran katman olmalıdır.

Bu dosyanın görevi:

normalize haberleri incelemek

öne çıkan olay cümleleri toplamak

kaynak başlıklarından ana sinyalleri çıkarmak

entity listesi oluşturmak

keyword listesi oluşturmak

LLM’e daha zengin bağlam verecek yoğunlaştırılmış data üretmek

Bu dosya:

article body yazmamalı

prompt yazmamalı

provider çağırmamalı

SEO üretmemeli

Fact pack için bağlayıcı kurallar
Kural 1

Fact pack, ham haber listesinin yerine geçmez; onu yoğunlaştırır.

Kural 2

Facts, entities, keywords ve signals ayrı tutulmalıdır.

Kural 3

Duplicate ve gürültü azaltılmalıdır.

Kural 4

LLM’e prompt-ready ama prompt olmayan veri üretmelidir.

Kural 5

Fakt yoğunluğu arttırılmalı, ama uydurma/halüsinatif veri üretilmemelidir.

Fact pack çıktı sözleşmesi
[
  'facts' => [],
  'entities' => [],
  'keywords' => [],
  'signals' => [],
]

Daha zengin sürümde:

[
  'facts' => [
    '...'
  ],
  'entities' => [
    'OpenAI',
    'NVIDIA'
  ],
  'keywords' => [
    'AI chips',
    'model releases'
  ],
  'signals' => [
    'Competition is accelerating in foundation models.',
    'Hardware demand remains a major theme.'
  ],
]
Fact pack için önerilen ana fonksiyonlar
1. aig_news_fact_pack_build(array $normalized, array $input = []): array

Ana giriş.

2. aig_news_fact_pack_extract_facts(array $normalized): array

Başlıklardan/özetlerden fact listesi çıkarır.

3. aig_news_fact_pack_extract_entities(array $normalized): array

Entity listesi çıkarır.

4. aig_news_fact_pack_extract_keywords(array $normalized, array $input = []): array

Keyword listesi çıkarır.

5. aig_news_fact_pack_extract_signals(array $normalized): array

Trend/signal cümleleri üretir.

6. aig_news_fact_pack_unique_clean(array $items, int $limit): array

Temizleme ve limit uygulama.

news-fact-pack.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main fact pack builder.
 */
function aig_news_fact_pack_build(array $normalized, array $input = []): array
{
    $facts = aig_news_fact_pack_extract_facts($normalized);
    $entities = aig_news_fact_pack_extract_entities($normalized);
    $keywords = aig_news_fact_pack_extract_keywords($normalized, $input);
    $signals = aig_news_fact_pack_extract_signals($normalized);

    return [
        'facts' => aig_news_fact_pack_unique_clean($facts, 12),
        'entities' => aig_news_fact_pack_unique_clean($entities, 12),
        'keywords' => aig_news_fact_pack_unique_clean($keywords, 14),
        'signals' => aig_news_fact_pack_unique_clean($signals, 8),
    ];
}

/**
 * Extract fact-like items from titles and summaries.
 */
function aig_news_fact_pack_extract_facts(array $normalized): array
{
    $facts = [];

    foreach ($normalized as $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $summary = trim((string) ($item['summary'] ?? ''));

        if ($title !== '') {
            $facts[] = $title;
        }

        if ($summary !== '') {
            // gerçek kodda daha akıllı cümle kısaltma yapılabilir
            $facts[] = mb_substr(strip_tags($summary), 0, 200);
        }
    }

    return $facts;
}

/**
 * Extract simple entity signals.
 */
function aig_news_fact_pack_extract_entities(array $normalized): array
{
    $entities = [];

    foreach ($normalized as $item) {
        $source = trim((string) ($item['source'] ?? ''));
        if ($source !== '') {
            $entities[] = $source;
        }

        $title = trim((string) ($item['title'] ?? ''));
        if ($title !== '') {
            // Basit placeholder mantık: gerçek kodda daha akıllı entity çıkarımı olabilir.
            preg_match_all('/\b[A-ZÇĞİÖŞÜ][A-Za-zÇĞİÖŞÜçğıöşü0-9\-\.\&]{2,}\b/u', $title, $m);
            foreach (($m[0] ?? []) as $entity) {
                $entities[] = trim($entity);
            }
        }
    }

    return $entities;
}

/**
 * Extract keywords from category/topic and item titles.
 */
function aig_news_fact_pack_extract_keywords(array $normalized, array $input = []): array
{
    $keywords = [];

    $topic = trim((string) ($input['topic'] ?? ''));
    $category = trim((string) ($input['category'] ?? ''));

    if ($topic !== '') {
        $keywords[] = $topic;
    }

    if ($category !== '') {
        $keywords[] = $category;
    }

    foreach ($normalized as $item) {
        $title = trim((string) ($item['title'] ?? ''));
        if ($title === '') {
            continue;
        }

        // Basit strateji: başlıktan küçük anahtar parçalar türetme.
        $parts = preg_split('/[\s,:;|\/\-]+/u', mb_strtolower($title));
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || mb_strlen($part) < 4) {
                continue;
            }
            $keywords[] = $part;
        }
    }

    return $keywords;
}

/**
 * Build higher-level signals from repeated themes.
 */
function aig_news_fact_pack_extract_signals(array $normalized): array
{
    $signals = [];
    $count = count($normalized);

    if ($count >= 5) {
        $signals[] = 'Multiple overlapping developments suggest a broader sector trend rather than an isolated headline.';
    }

    $sources = [];
    foreach ($normalized as $item) {
        $source = trim((string) ($item['source'] ?? ''));
        if ($source !== '') {
            $sources[] = $source;
        }
    }

    if (count(array_unique($sources)) >= 3) {
        $signals[] = 'Coverage from multiple sources indicates that the topic has wider newsroom visibility and likely higher relevance.';
    }

    return $signals;
}

/**
 * Unique-clean-limit helper.
 */
function aig_news_fact_pack_unique_clean(array $items, int $limit = 10): array
{
    $clean = [];
    $seen = [];

    foreach ($items as $item) {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $item));
        if ($text === '') {
            continue;
        }

        $key = mb_strtolower($text);
        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $clean[] = $text;

        if (count($clean) >= $limit) {
            break;
        }
    }

    return $clean;
}
Fact pack için mimari not

Fact pack zayıfsa article’lar:

yüzeysel olur

aynı cümleleri tekrar eder

haber analizi yerine boş geçiş cümleleri üretir

Fact pack güçlü olduğunda ise LLM:

daha somut yazabilir

daha derli toplu analiz yapabilir

gereksiz soyutluğu azaltır

Bu aşamadaki net kazanım

Artık context zincirinin news tarafında da güçlü bir çerçevemiz var:

core/news/news-sources.php

core/news/news-collector.php

core/news/news-fact-pack.php

Bu, ai-article-context.php ile birlikte düşünülünce artık article üretiminin veri hazırlık tarafı büyük ölçüde resmileşti.

Şu anda kağıt üstünde tamamlanan ana omurga
Bootstrap / giriş

ai-article-generator.php

ajax-handler.php

Services

article-service.php

rewrite-service.php

selftest-service.php

AI çağrı omurgası

ai-article-router.php

ai-article-gateway.php

ai-article-provider-registry.php

Article üretim omurgası

ai-article-pipeline.php

ai-article-context.php

ai-article-outline.php

News veri hazırlık omurgası

news-sources.php

news-collector.php

news-fact-pack.php

Bu artık ciddi şekilde “gerçek kod çıkarılabilir” seviyeye geldi.

Şimdi news akışını ve rewrite hattını kapatan üç kritik dosyayı resmileştiriyoruz:

AŞAMA 10

core/news/news-normalizer.php için normalize contract + pseudo-code

core/news/news-cache.php için cache contract + pseudo-code

core/pipelines/rewrite-pipeline.php için cleanup/postprocess contract + pseudo-code

Bu üçü önemli çünkü:

collector ham veri getirir

normalizer ortak şemaya indirir

cache sistemi tekrarları ve yavaşlığı azaltır

rewrite-pipeline ise gerçek rewrite sonrası cilayı yapar

core/news/news-normalizer.php
Resmi görev tanımı

news-normalizer.php, farklı kaynaklardan gelen ham haberleri tek ortak şemaya dönüştüren katman olmalıdır.

Bu dosyanın görevi:

RSS/API/özel kaynaklardan gelen farklı item yapıları tekleştirmek

alan adlarını normalize etmek

boş/gürültülü item’ları elemek

tarih/başlık/özet/source/category/dil gibi alanları ortaklaştırmak

context ve fact pack için temiz veri hazırlamak

Bu dosya:

haber toplamaz

article yazmaz

prompt kurmaz

provider çağırmaz

Normalizer için bağlayıcı kurallar
Kural 1

Collector çıktısı doğrudan context’e gitmemeli; önce normalize edilmelidir.

Kural 2

Tüm item’lar ortak minimum alanlara sahip olmalıdır.

Kural 3

Boş veya zayıf item’lar filtrelenebilmelidir.

Kural 4

Normalize işlemi kaynak tipinden bağımsız tek şemaya inmeyi hedeflemelidir.

Kural 5

Normalizer editorial karar vermez; yalnız veri düzenler.

Normalize edilmiş item resmi şeması
[
  'title' => '',
  'url' => '',
  'source' => '',
  'summary' => '',
  'published_at' => '',
  'language' => '',
  'category' => '',
  'image' => '',
  'author' => '',
  'tags' => [],
  'raw' => [],
]
Normalizer çıktı sözleşmesi

Tek item normalizasyonu:

[
  'ok' => true,
  'item' => [
    'title' => '',
    'url' => '',
    'source' => '',
    'summary' => '',
    'published_at' => '',
    'language' => '',
    'category' => '',
    'image' => '',
    'author' => '',
    'tags' => [],
    'raw' => [],
  ],
  'error' => null,
]

Batch normalizasyonu:

[
  'ok' => true,
  'items' => [],
  'meta' => [
    'count' => 0,
    'dropped' => 0,
  ],
  'error' => null,
]
Normalizer için önerilen ana fonksiyonlar
1. aig_news_normalize_item(array $item, array $context = []): array

Tek item normalize eder.

2. aig_news_normalize_batch(array $items, array $context = []): array

Batch normalize eder.

3. aig_news_normalize_pick_title(array $item): string

Title seçimi.

4. aig_news_normalize_pick_summary(array $item): string

Summary seçimi.

5. aig_news_normalize_pick_url(array $item): string

URL seçimi.

6. aig_news_normalize_pick_published_at(array $item): string

Tarih seçimi.

7. aig_news_normalize_is_valid(array $normalized): bool

Zayıf item eleme kontrolü.

news-normalizer.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalize a single raw news item into common schema.
 */
function aig_news_normalize_item(array $item, array $context = []): array
{
    $normalized = [
        'title' => aig_news_normalize_pick_title($item),
        'url' => aig_news_normalize_pick_url($item),
        'source' => trim((string) ($item['source'] ?? $context['source'] ?? '')),
        'summary' => aig_news_normalize_pick_summary($item),
        'published_at' => aig_news_normalize_pick_published_at($item),
        'language' => trim((string) ($item['language'] ?? $item['lang'] ?? $context['lang'] ?? '')),
        'category' => trim((string) ($item['category'] ?? $context['category'] ?? 'general')),
        'image' => trim((string) ($item['image'] ?? $item['image_url'] ?? '')),
        'author' => trim((string) ($item['author'] ?? '')),
        'tags' => is_array($item['tags'] ?? null) ? array_values($item['tags']) : [],
        'raw' => $item,
    ];

    if (!aig_news_normalize_is_valid($normalized)) {
        return [
            'ok' => false,
            'item' => [],
            'error' => [
                'code' => 'invalid_normalized_item',
                'message' => 'Normalized item is missing required fields.',
            ],
        ];
    }

    return [
        'ok' => true,
        'item' => $normalized,
        'error' => null,
    ];
}

/**
 * Normalize a batch of raw items.
 */
function aig_news_normalize_batch(array $items, array $context = []): array
{
    $normalized = [];
    $dropped = 0;

    foreach ($items as $item) {
        if (!is_array($item)) {
            $dropped++;
            continue;
        }

        $result = aig_news_normalize_item($item, $context);
        if (empty($result['ok'])) {
            $dropped++;
            continue;
        }

        $normalized[] = $result['item'];
    }

    return [
        'ok' => true,
        'items' => $normalized,
        'meta' => [
            'count' => count($normalized),
            'dropped' => $dropped,
        ],
        'error' => null,
    ];
}

/**
 * Pick best available title field.
 */
function aig_news_normalize_pick_title(array $item): string
{
    $title = trim((string) ($item['title'] ?? $item['headline'] ?? ''));
    return preg_replace('/\s+/u', ' ', $title) ?: '';
}

/**
 * Pick best available summary field.
 */
function aig_news_normalize_pick_summary(array $item): string
{
    $summary = trim((string) (
        $item['summary']
        ?? $item['description']
        ?? $item['excerpt']
        ?? ''
    ));

    $summary = strip_tags($summary);
    $summary = preg_replace('/\s+/u', ' ', $summary);

    return (string) $summary;
}

/**
 * Pick best available URL.
 */
function aig_news_normalize_pick_url(array $item): string
{
    return trim((string) ($item['url'] ?? $item['link'] ?? ''));
}

/**
 * Pick best available publication datetime.
 */
function aig_news_normalize_pick_published_at(array $item): string
{
    return trim((string) (
        $item['published_at']
        ?? $item['pubDate']
        ?? $item['date']
        ?? ''
    ));
}

/**
 * Minimum validity gate for normalized items.
 */
function aig_news_normalize_is_valid(array $normalized): bool
{
    if (trim((string) ($normalized['title'] ?? '')) === '') {
        return false;
    }

    if (trim((string) ($normalized['url'] ?? '')) === '') {
        return false;
    }

    return true;
}
Normalizer için mimari not

Normalizer zayıfsa:

fact pack gürültülü olur

context karışır

aynı haber farklı şekillerde tekrarlanır

article kalitesi düşer

Yani normalizer, collector ile context arasındaki temizlik kapısıdır.

core/news/news-cache.php
Resmi görev tanımı

news-cache.php, news collect sonuçlarının okunması ve yazılması için resmi cache katmanı olmalıdır.

Bu dosyanın görevi:

topic/category/lang tabanlı cache key üretmek

cache dosyasını okumak

TTL/geçerlilik kontrolü yapmak

geçerliyse collector yerine cache döndürmek

yeni collect sonucu cache’e yazmak

Bu dosya:

haber toplamaz

normalize etmez

article logic taşımaz

cache dışında storage politikası belirlemez

Cache için bağlayıcı kurallar
Kural 1

Collector önce cache’i deneyebilir; cache mantığı collector içine dağılmamalıdır.

Kural 2

Cache key deterministik olmalıdır.

Kural 3

TTL politikası tek merkezden kontrol edilebilir olmalıdır.

Kural 4

Cache bozuksa sessiz ve güvenli şekilde bypass edilmelidir.

Kural 5

Cache dosya yapısı JSON-first olmalıdır.

Cache key mantığı

Örnek key girdileri:

topic

category

lang

Örnek deterministik key:

md5(topic|category|lang)
Cache çıktı sözleşmesi
Cache hit örneği
[
  'ok' => true,
  'items' => [],
  'meta' => [
    'cache_used' => true,
    'cache_key' => '',
    'cached_at' => '',
  ],
  'error' => null,
]
Cache miss

null dönebilir veya:

[
  'ok' => false,
  'error' => [
    'code' => 'cache_miss',
    'message' => 'Cache miss.',
  ],
]

En pratik yaklaşım: miss durumunda null.

Cache için önerilen ana fonksiyonlar
1. aig_news_cache_get(array $context): ?array

Cache oku.

2. aig_news_cache_set(array $context, array $result): bool

Cache yaz.

3. aig_news_cache_build_key(array $context): string

Cache key üret.

4. aig_news_cache_build_path(string $key): string

Dosya yolu üret.

5. aig_news_cache_is_fresh(array $payload): bool

TTL/geçerlilik kontrolü.

6. aig_news_cache_ttl_seconds(): int

TTL merkezi.

news-cache.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Read news cache result if fresh.
 */
function aig_news_cache_get(array $context): ?array
{
    $key = aig_news_cache_build_key($context);
    $path = aig_news_cache_build_path($key);

    if (!file_exists($path)) {
        return null;
    }

    $raw = file_get_contents($path);
    $payload = json_decode((string) $raw, true);

    if (!is_array($payload)) {
        return null;
    }

    if (!aig_news_cache_is_fresh($payload)) {
        return null;
    }

    return [
        'ok' => true,
        'items' => is_array($payload['items'] ?? null) ? $payload['items'] : [],
        'meta' => [
            'cache_used' => true,
            'cache_key' => $key,
            'cached_at' => (string) ($payload['cached_at'] ?? ''),
        ],
        'error' => null,
    ];
}

/**
 * Write news collection result to cache.
 */
function aig_news_cache_set(array $context, array $result): bool
{
    $key = aig_news_cache_build_key($context);
    $path = aig_news_cache_build_path($key);

    $dir = dirname($path);
    if (!is_dir($dir)) {
        if (!wp_mkdir_p($dir)) {
            return false;
        }
    }

    $payload = [
        'cached_at' => gmdate('c'),
        'context' => [
            'topic' => (string) ($context['topic'] ?? ''),
            'category' => (string) ($context['category'] ?? 'general'),
            'lang' => (string) ($context['lang'] ?? 'tr'),
        ],
        'items' => is_array($result['items'] ?? null) ? $result['items'] : [],
    ];

    return (bool) file_put_contents(
        $path,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
}

/**
 * Deterministic cache key builder.
 */
function aig_news_cache_build_key(array $context): string
{
    $topic = trim((string) ($context['topic'] ?? ''));
    $category = trim((string) ($context['category'] ?? 'general'));
    $lang = trim((string) ($context['lang'] ?? 'tr'));

    return md5($topic . '|' . $category . '|' . $lang);
}

/**
 * Cache file path builder.
 */
function aig_news_cache_build_path(string $key): string
{
    $base = defined('AIG_DATA_DIR')
        ? (AIG_DATA_DIR . '/news-cache')
        : (__DIR__ . '/../../data/news-cache');

    return rtrim($base, '/\\') . '/' . $key . '.json';
}

/**
 * Freshness check using TTL.
 */
function aig_news_cache_is_fresh(array $payload): bool
{
    $cachedAt = strtotime((string) ($payload['cached_at'] ?? ''));
    if (!$cachedAt) {
        return false;
    }

    $age = time() - $cachedAt;
    return $age >= 0 && $age <= aig_news_cache_ttl_seconds();
}

/**
 * Central cache TTL.
 */
function aig_news_cache_ttl_seconds(): int
{
    return 60 * 30; // 30 minutes
}
Cache için mimari not

Cache iyi olmazsa:

collector gereksiz yavaşlar

her generate isteği ağ yükü yapar

panel deneyimi ağırlaşır

aynı sonuç için gereksiz fetch olur

Ama cache aşırı agresif olursa da:

haberler bayatlar

güncellik bozulur

Bu yüzden cache, collector’ın performans freni değil, dengeleyici amortisör olmalıdır.

core/pipelines/rewrite-pipeline.php
Resmi görev tanımı

rewrite-pipeline.php, gerçek rewrite service’ten dönen metin üzerinde cleanup ve postprocess işlemleri yapan katman olmalıdır.

Bu dosyanın görevi:

gereksiz boşlukları temizlemek

başlık yapısını toparlamak

isteniyorsa HTML koruma / minimal düzeltme yapmak

metni daha temiz teslim etmek

Bu dosya:

gerçek rewrite isteğini yönetmemeli

provider route seçmemeli

LLM çağırmamalı

rewrite service’in yerine geçmemelidir

Rewrite-pipeline için bağlayıcı kurallar
Kural 1

Rewrite-pipeline = cleanup/postprocess katmanı.

Kural 2

Gerçek rewrite kararı ve LLM çağrısı rewrite-service’tedir.

Kural 3

Preserve HTML modu ile plain text modu ayrılmalıdır.

Kural 4

Aşırı agresif düzeltmeler anlamı bozmamalıdır.

Kural 5

Pipeline no-op gibi de davranabilmeli; zorla içerik uydurmamalıdır.

Rewrite-pipeline girdi sözleşmesi
[
  'content' => 'string',
  'preserve_html' => true,
  'mode' => 'polish|expand|shorten|translate|restructure',
  'lang' => 'tr',
]
Rewrite-pipeline çıktı sözleşmesi

En basit haliyle doğrudan temizlenmiş string döndürebilir.
Daha resmi kullanım için:

[
  'ok' => true,
  'content' => '',
  'meta' => [
    'cleanup_applied' => true,
  ],
  'error' => null,
]

Biz burada service içinden kullanılacak pratik helper yaklaşımını esas alacağız.

Rewrite-pipeline için önerilen ana fonksiyonlar
1. aig_rewrite_pipeline_cleanup(string $content, array $options = []): string

Ana cleanup girişi.

2. aig_rewrite_pipeline_cleanup_text(string $content): string

Plain text temizlik.

3. aig_rewrite_pipeline_cleanup_html(string $content): string

HTML koruyarak temizlik.

4. aig_rewrite_pipeline_normalize_headings(string $content, bool $preserveHtml): string

Başlıkları düzenler.

5. aig_rewrite_pipeline_normalize_spacing(string $content): string

Boşluk/satır düzeni.

rewrite-pipeline.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cleanup rewritten content after service-level rewrite.
 */
function aig_rewrite_pipeline_cleanup(string $content, array $options = []): string
{
    $preserveHtml = !empty($options['preserve_html']);

    if ($preserveHtml) {
        $content = aig_rewrite_pipeline_cleanup_html($content);
    } else {
        $content = aig_rewrite_pipeline_cleanup_text($content);
    }

    $content = aig_rewrite_pipeline_normalize_headings($content, $preserveHtml);
    $content = aig_rewrite_pipeline_normalize_spacing($content);

    return trim($content);
}

/**
 * Cleanup plain text output.
 */
function aig_rewrite_pipeline_cleanup_text(string $content): string
{
    // strip excessive whitespace
    $content = preg_replace("/[ \t]+/u", " ", $content);
    $content = preg_replace("/\n{3,}/u", "\n\n", $content);

    return (string) $content;
}

/**
 * Cleanup HTML output while preserving basic structure.
 */
function aig_rewrite_pipeline_cleanup_html(string $content): string
{
    // minimal cleanup only
    $content = preg_replace("/[ \t]+/u", " ", $content);
    $content = preg_replace("/>\s+</u", "><", $content);
    $content = preg_replace("/\n{3,}/u", "\n\n", $content);

    return (string) $content;
}

/**
 * Normalize heading formatting in text or HTML mode.
 */
function aig_rewrite_pipeline_normalize_headings(string $content, bool $preserveHtml): string
{
    if ($preserveHtml) {
        // very conservative in HTML mode
        return $content;
    }

    // Example: collapse repeated heading lines
    $lines = preg_split("/\r\n|\n|\r/u", $content);
    $result = [];
    $previous = null;

    foreach ($lines as $line) {
        $clean = trim($line);

        if ($clean !== '' && $previous !== null && mb_strtolower($clean) === mb_strtolower($previous)) {
            continue;
        }

        $result[] = $line;
        $previous = $clean !== '' ? $clean : $previous;
    }

    return implode("\n", $result);
}

/**
 * Normalize spacing globally.
 */
function aig_rewrite_pipeline_normalize_spacing(string $content): string
{
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $content = preg_replace("/\n{3,}/u", "\n\n", $content);
    $content = preg_replace("/[ \t]+\n/u", "\n", $content);

    return (string) $content;
}
Rewrite-pipeline için mimari not

Bu dosya geçmişte kolayca yanlış konumlandırılabilecek bir dosya.
Çünkü adı “rewrite pipeline” olunca insanlar bunu gerçek rewrite motoru sanabiliyor.

Ama resmi karar şu olmalı:

rewrite-service = gerçek yeniden yazım

rewrite-pipeline = son temizlik ve teslim hazırlığı

Bu ayrım korunmazsa kullanıcı “rewrite çalıştı mı?” sorusuna net cevap alamaz.

Bu aşamadaki net kazanım

Artık news ve rewrite tarafında da omurga ciddi biçimde tamamlandı:

News

news-sources.php

news-collector.php

news-normalizer.php

news-cache.php

news-fact-pack.php

Rewrite

rewrite-service.php

rewrite-pipeline.php

Bu, article generation zincirinin veri ve postprocess tarafını büyük ölçüde kapatıyor.

Şu anda kağıt üstünde resmileşen büyük omurga
Bootstrap / giriş

ai-article-generator.php

ajax-handler.php

Services

article-service.php

rewrite-service.php

selftest-service.php

AI karar ve çağrı

ai-article-router.php

ai-article-gateway.php

ai-article-provider-registry.php

Article üretim çekirdeği

ai-article-pipeline.php

ai-article-context.php

ai-article-outline.php

News katmanı

news-sources.php

news-collector.php

news-normalizer.php

news-cache.php

news-fact-pack.php

Rewrite postprocess

rewrite-pipeline.php

Bu artık gerçekten “dosya üretimine geçilebilir” bir ön mimari set.

AŞAMA 11

core/services/seo-service.php için resmi sözleşme + pseudo-code

core/pipelines/seo-pipeline.php için enrichment contract + pseudo-code

core/ai-article-quality.php için quality score/flags contract + pseudo-code

Bu üçü çok önemli çünkü makale yalnız üretilmiş olmakla bitmiyor:

SEO ile zenginleşmeli

kalite ile ölçülmeli

sonuç güvenilir biçimde değerlendirilmeli

core/services/seo-service.php
Resmi görev tanımı

seo-service.php, modülde SEO üretim use-case’inin resmi giriş kapısı olmalıdır.

Bu dosyanın görevi:

title/content/category/lang girdisini almak

SEO pipeline’ı çağırmak

meta title, meta description, FAQ, schema, keywords gibi alanları üretmek

final SEO response contract’ını döndürmek

Bu dosya:

article generate etmemeli

provider route kararını kendi başına vermemeli

panel mantığı taşımamalı

doğrudan UI çıktısı üretmemeli

SEO service için bağlayıcı kurallar
Kural 1

SEO üretimi için tek resmi giriş kapısı bu service olmalıdır.

Kural 2

SEO service, article generate’den bağımsız da çağrılabilir olmalıdır.

Kural 3

SEO service yalnız normalize input ile çalışmalıdır.

Kural 4

SEO service, kısmi başarıyı da standard response ile döndürebilmelidir.

Kural 5

SEO service, helper dosyalarını birleştiren orkestrasyon katmanıdır; builder’ların yerine geçmez.

SEO service girdi sözleşmesi
[
  'title' => 'string',
  'content' => 'string',
  'category' => 'string',
  'lang' => 'string',
  'topic' => 'string|null',
  'provider' => 'string|null',
  'model' => 'string|null',
]
SEO service çıktı sözleşmesi
[
  'ok' => true,
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
    'timing' => [],
  ],
  'error' => null,
]
SEO service için önerilen ana fonksiyonlar
1. aig_seo_service_generate(array $input): array

Ana giriş.

2. aig_seo_service_normalize_input(array $input): array

Input normalize eder.

3. aig_seo_service_validate_input(array $input): array

Minimum alan kontrolü yapar.

4. aig_seo_service_finalize(array $pipelineResult): array

Final response contract’a indirger.

5. aig_seo_service_build_error(...)

Standart hata döndürür.

seo-service.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main SEO generation entry point.
 */
function aig_seo_service_generate(array $input): array
{
    $startedAt = microtime(true);

    $normalized = aig_seo_service_normalize_input($input);
    $validation = aig_seo_service_validate_input($normalized);

    if (!$validation['ok']) {
        return aig_seo_service_build_error(
            $validation['error']['code'],
            $validation['error']['message'],
            ['stage' => 'validate']
        );
    }

    if (!function_exists('aig_seo_pipeline_run')) {
        return aig_seo_service_build_error(
            'missing_seo_pipeline',
            'SEO pipeline function is missing.',
            ['stage' => 'pipeline']
        );
    }

    $pipelineResult = aig_seo_pipeline_run($normalized);

    if (empty($pipelineResult['ok'])) {
        return aig_seo_service_build_error(
            $pipelineResult['error']['code'] ?? 'seo_pipeline_failed',
            $pipelineResult['error']['message'] ?? 'SEO pipeline failed.',
            [
                'stage' => 'pipeline',
                'pipeline_meta' => $pipelineResult['meta'] ?? [],
            ]
        );
    }

    $result = aig_seo_service_finalize($pipelineResult);
    $result['meta']['timing']['seo_service_ms'] = (int) round((microtime(true) - $startedAt) * 1000);

    return $result;
}

function aig_seo_service_normalize_input(array $input): array
{
    return [
        'title' => trim((string) ($input['title'] ?? '')),
        'content' => trim((string) ($input['content'] ?? '')),
        'category' => trim((string) ($input['category'] ?? 'general')),
        'lang' => trim((string) ($input['lang'] ?? 'tr')),
        'topic' => !empty($input['topic']) ? trim((string) $input['topic']) : null,
        'provider' => !empty($input['provider']) ? (string) $input['provider'] : null,
        'model' => !empty($input['model']) ? (string) $input['model'] : null,
    ];
}

function aig_seo_service_validate_input(array $input): array
{
    if ($input['title'] === '' && $input['content'] === '') {
        return [
            'ok' => false,
            'error' => [
                'code' => 'missing_seo_input',
                'message' => 'SEO generation requires title and/or content.',
            ],
        ];
    }

    return ['ok' => true];
}

function aig_seo_service_finalize(array $pipelineResult): array
{
    return [
        'ok' => true,
        'seo' => [
            'meta_title' => (string) ($pipelineResult['seo']['meta_title'] ?? ''),
            'meta_description' => (string) ($pipelineResult['seo']['meta_description'] ?? ''),
            'faq' => is_array($pipelineResult['seo']['faq'] ?? null) ? $pipelineResult['seo']['faq'] : [],
            'schema' => is_array($pipelineResult['seo']['schema'] ?? null) ? $pipelineResult['seo']['schema'] : [],
            'keywords' => is_array($pipelineResult['seo']['keywords'] ?? null) ? $pipelineResult['seo']['keywords'] : [],
        ],
        'meta' => is_array($pipelineResult['meta'] ?? null) ? $pipelineResult['meta'] : [
            'provider' => '',
            'model' => '',
            'usage' => [],
            'timing' => [],
        ],
        'error' => null,
    ];
}

function aig_seo_service_build_error(string $code, string $message, array $meta = []): array
{
    return [
        'ok' => false,
        'seo' => [
            'meta_title' => '',
            'meta_description' => '',
            'faq' => [],
            'schema' => [],
            'keywords' => [],
        ],
        'meta' => $meta,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ];
}
core/pipelines/seo-pipeline.php
Resmi görev tanımı

seo-pipeline.php, article içeriği üzerinden SEO enrichment orkestrasyonu yapan katman olmalıdır.

Bu dosyanın görevi:

meta builder

faq builder

schema builder

keyword çıkarımı

gibi alt parçaları çağırarak tek SEO çıktısı üretmektir.

Bu dosya:

article generate etmemeli

WordPress save mantığı taşımamalı

panel logic taşımamalı

gerçek routing merkezi olmamalıdır

SEO pipeline için bağlayıcı kurallar
Kural 1

SEO pipeline, builder/helper katmanlarını bir araya getirir.

Kural 2

Meta/FAQ/schema/keywords ayrı üretilir, sonra tek SEO object’te birleşir.

Kural 3

Bir builder başarısız olsa bile mümkünse kısmi başarı döndürülebilir.

Kural 4

SEO enrichment article’ın yerine geçmez; article sonrası çalışır.

Kural 5

SEO pipeline, clean input ile çalışmalıdır.

SEO pipeline çıktı sözleşmesi
[
  'ok' => true,
  'seo' => [
    'meta_title' => '',
    'meta_description' => '',
    'faq' => [],
    'schema' => [],
    'keywords' => [],
  ],
  'meta' => [
    'builder_status' => [],
  ],
  'error' => null,
]
SEO pipeline için önerilen ana fonksiyonlar
1. aig_seo_pipeline_run(array $input): array

Ana giriş.

2. aig_seo_pipeline_generate_meta(array $input): array

Meta title/description üretir.

3. aig_seo_pipeline_generate_faq(array $input): array

FAQ üretir.

4. aig_seo_pipeline_generate_schema(array $input): array

Schema üretir.

5. aig_seo_pipeline_extract_keywords(array $input): array

Keyword çıkarır.

6. aig_seo_pipeline_finalize(...)

Final SEO object döndürür.

seo-pipeline.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main SEO enrichment pipeline.
 */
function aig_seo_pipeline_run(array $input): array
{
    $metaResult = aig_seo_pipeline_generate_meta($input);
    $faqResult = aig_seo_pipeline_generate_faq($input);
    $schemaResult = aig_seo_pipeline_generate_schema($input);
    $keywordsResult = aig_seo_pipeline_extract_keywords($input);

    $seo = [
        'meta_title' => (string) ($metaResult['meta_title'] ?? ''),
        'meta_description' => (string) ($metaResult['meta_description'] ?? ''),
        'faq' => is_array($faqResult['faq'] ?? null) ? $faqResult['faq'] : [],
        'schema' => is_array($schemaResult['schema'] ?? null) ? $schemaResult['schema'] : [],
        'keywords' => is_array($keywordsResult['keywords'] ?? null) ? $keywordsResult['keywords'] : [],
    ];

    return [
        'ok' => true,
        'seo' => $seo,
        'meta' => [
            'builder_status' => [
                'meta' => !empty($metaResult['ok']) ? 'pass' : 'warning',
                'faq' => !empty($faqResult['ok']) ? 'pass' : 'warning',
                'schema' => !empty($schemaResult['ok']) ? 'pass' : 'warning',
                'keywords' => !empty($keywordsResult['ok']) ? 'pass' : 'warning',
            ],
        ],
        'error' => null,
    ];
}

/**
 * Meta generation wrapper.
 */
function aig_seo_pipeline_generate_meta(array $input): array
{
    if (function_exists('aig_meta_builder_generate')) {
        $result = aig_meta_builder_generate($input);
        if (is_array($result)) {
            return $result;
        }
    }

    // fallback deterministic meta
    $title = trim((string) ($input['title'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));

    return [
        'ok' => true,
        'meta_title' => $title,
        'meta_description' => mb_substr(strip_tags($content), 0, 155),
    ];
}

/**
 * FAQ generation wrapper.
 */
function aig_seo_pipeline_generate_faq(array $input): array
{
    if (function_exists('aig_faq_builder_generate')) {
        $result = aig_faq_builder_generate($input);
        if (is_array($result)) {
            return $result;
        }
    }

    return [
        'ok' => true,
        'faq' => [],
    ];
}

/**
 * Schema generation wrapper.
 */
function aig_seo_pipeline_generate_schema(array $input): array
{
    if (function_exists('aig_schema_builder_generate')) {
        $result = aig_schema_builder_generate($input);
        if (is_array($result)) {
            return $result;
        }
    }

    return [
        'ok' => true,
        'schema' => [],
    ];
}

/**
 * Basic keyword extraction.
 */
function aig_seo_pipeline_extract_keywords(array $input): array
{
    $keywords = [];

    $title = mb_strtolower(trim((string) ($input['title'] ?? '')));
    $category = mb_strtolower(trim((string) ($input['category'] ?? '')));
    $topic = mb_strtolower(trim((string) ($input['topic'] ?? '')));

    if ($topic !== '') {
        $keywords[] = $topic;
    }
    if ($category !== '') {
        $keywords[] = $category;
    }

    if ($title !== '') {
        $parts = preg_split('/[\s,:;|\/\-]+/u', $title);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || mb_strlen($part) < 4) {
                continue;
            }
            $keywords[] = $part;
        }
    }

    $keywords = array_values(array_unique($keywords));

    return [
        'ok' => true,
        'keywords' => array_slice($keywords, 0, 12),
    ];
}
SEO pipeline için mimari not

Bu dosya zayıf olursa:

SEO dağınık helper’lara yayılır

meta ve schema birbiriyle uyumsuz olur

panelde “SEO var gibi” ama aslında yarım bir durum oluşur

Yani SEO pipeline, builder’lar arası koordinasyon katmanıdır.

core/ai-article-quality.php
Resmi görev tanımı

ai-article-quality.php, article ve rewrite çıktıları için kalite skoru ve uyarı bayrakları üreten katman olmalıdır.

Bu dosyanın görevi:

article içeriğini ölçmek

uzunluk, yapı, dil tutarlılığı, tekrar, relevance, fact density gibi alanlarda skorlamak

warning flag üretmek

minimum eşik altında kalan içerikleri işaretlemek

Bu dosya:

article generate etmemeli

rewrite yapmamalı

SEO üretmemeli

save kararı tek başına vermemelidir

Quality için bağlayıcı kurallar
Kural 1

“İçerik geldi” başarı sayılmaz; kalite ayrıca ölçülmelidir.

Kural 2

Quality katmanı skorlama ve işaretleme yapar, içerik üretmez.

Kural 3

Article ve rewrite kalite değerlendirmesi ayrı olabilir.

Kural 4

Flag sistemi score kadar önemlidir.

Kural 5

Fallback ile gelen içerik, kalite açısından ayrıca işaretlenebilmelidir.

Quality çıktı sözleşmesi
[
  'ok' => true,
  'score' => 84,
  'threshold' => 70,
  'passed' => true,
  'metrics' => [
    'length' => 80,
    'structure' => 85,
    'relevance' => 82,
    'language_consistency' => 78,
    'fact_density' => 76,
    'redundancy' => 88,
    'readability' => 83,
  ],
  'flags' => [
    'minor_english_residue',
  ],
]
Quality için önerilen ana fonksiyonlar
1. aig_quality_evaluate_article(array $article, array $input = []): array

Ana article kalite ölçümü.

2. aig_quality_score_length(array $article, array $input = []): int

Uzunluk skoru.

3. aig_quality_score_structure(array $article): int

Yapı skoru.

4. aig_quality_score_relevance(array $article, array $input = []): int

Konu uyumu.

5. aig_quality_score_language_consistency(array $article, array $input = []): int

Dil tutarlılığı.

6. aig_quality_score_fact_density(array $article): int

Fact yoğunluğu.

7. aig_quality_score_redundancy(array $article): int

Tekrar oranı.

8. aig_quality_score_readability(array $article): int

Okunabilirlik.

9. aig_quality_collect_flags(array $article, array $metrics, array $input = []): array

Flag üretimi.

ai-article-quality.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main article quality evaluator.
 */
function aig_quality_evaluate_article(array $article, array $input = []): array
{
    $metrics = [
        'length' => aig_quality_score_length($article, $input),
        'structure' => aig_quality_score_structure($article),
        'relevance' => aig_quality_score_relevance($article, $input),
        'language_consistency' => aig_quality_score_language_consistency($article, $input),
        'fact_density' => aig_quality_score_fact_density($article),
        'redundancy' => aig_quality_score_redundancy($article),
        'readability' => aig_quality_score_readability($article),
    ];

    $score = (int) round(
        ($metrics['length'] * 0.15) +
        ($metrics['structure'] * 0.15) +
        ($metrics['relevance'] * 0.20) +
        ($metrics['language_consistency'] * 0.15) +
        ($metrics['fact_density'] * 0.15) +
        ($metrics['redundancy'] * 0.10) +
        ($metrics['readability'] * 0.10)
    );

    $threshold = 70;
    $flags = aig_quality_collect_flags($article, $metrics, $input);

    return [
        'ok' => true,
        'score' => $score,
        'threshold' => $threshold,
        'passed' => ($score >= $threshold),
        'metrics' => $metrics,
        'flags' => $flags,
    ];
}

/**
 * Length score by target length bucket.
 */
function aig_quality_score_length(array $article, array $input = []): int
{
    $content = trim((string) ($article['content'] ?? ''));
    $len = mb_strlen($content);
    $target = (string) ($input['length'] ?? 'long');

    if ($target === 'short') {
        if ($len >= 1200) return 95;
        if ($len >= 700) return 80;
        return 50;
    }

    if ($target === 'medium') {
        if ($len >= 2500) return 95;
        if ($len >= 1500) return 80;
        return 55;
    }

    // long
    if ($len >= 4000) return 95;
    if ($len >= 2500) return 80;
    return 45;
}

/**
 * Structure score from sections/headings.
 */
function aig_quality_score_structure(array $article): int
{
    $sections = is_array($article['sections'] ?? null) ? $article['sections'] : [];
    $content = trim((string) ($article['content'] ?? ''));

    if (count($sections) >= 4) {
        return 90;
    }

    if (preg_match('/\n\n/u', $content)) {
        return 75;
    }

    return 55;
}

/**
 * Relevance score using topic presence.
 */
function aig_quality_score_relevance(array $article, array $input = []): int
{
    $topic = mb_strtolower(trim((string) ($input['topic'] ?? $article['topic'] ?? '')));
    $content = mb_strtolower(trim((string) ($article['content'] ?? '')));

    if ($topic === '' || $content === '') {
        return 65;
    }

    if (mb_strpos($content, $topic) !== false) {
        return 90;
    }

    return 70;
}

/**
 * Language consistency score.
 */
function aig_quality_score_language_consistency(array $article, array $input = []): int
{
    $targetLang = (string) ($input['lang'] ?? $article['lang'] ?? 'tr');
    $content = (string) ($article['content'] ?? '');

    if ($content === '') {
        return 40;
    }

    // Basit sezgisel örnek: Türkçe hedefte yoğun İngilizce residue cezası
    if ($targetLang === 'tr') {
        $englishMarkers = preg_match_all('/\b(the|and|with|from|model|release|company|today|market)\b/i', $content);
        if ($englishMarkers >= 12) return 55;
        if ($englishMarkers >= 5) return 72;
        return 88;
    }

    return 80;
}

/**
 * Fact density approximation.
 */
function aig_quality_score_fact_density(array $article): int
{
    $content = (string) ($article['content'] ?? '');
    if ($content === '') {
        return 40;
    }

    $numberCount = preg_match_all('/\b\d+\b/u', $content);
    $sourceCount = is_array($article['sources'] ?? null) ? count($article['sources']) : 0;

    $score = 60 + min(20, $numberCount * 2) + min(15, $sourceCount * 3);
    return min(95, $score);
}

/**
 * Redundancy score (higher is better => less repetition).
 */
function aig_quality_score_redundancy(array $article): int
{
    $content = trim((string) ($article['content'] ?? ''));
    if ($content === '') {
        return 40;
    }

    $paragraphs = preg_split('/\n{2,}/u', $content);
    $normalized = [];
    foreach ($paragraphs as $p) {
        $p = mb_strtolower(trim($p));
        if ($p !== '') {
            $normalized[] = $p;
        }
    }

    $unique = array_unique($normalized);
    if (count($normalized) === 0) {
        return 60;
    }

    $ratio = count($unique) / count($normalized);

    if ($ratio >= 0.95) return 90;
    if ($ratio >= 0.80) return 78;
    return 60;
}

/**
 * Readability approximation.
 */
function aig_quality_score_readability(array $article): int
{
    $content = trim((string) ($article['content'] ?? ''));
    if ($content === '') {
        return 40;
    }

    $avgSentenceLength = 0;
    $sentences = preg_split('/[.!?]+/u', $content);
    $sentences = array_filter(array_map('trim', $sentences));

    if (count($sentences) > 0) {
        $wordCount = 0;
        foreach ($sentences as $s) {
            $wordCount += count(preg_split('/\s+/u', $s));
        }
        $avgSentenceLength = $wordCount / max(1, count($sentences));
    }

    if ($avgSentenceLength > 0 && $avgSentenceLength <= 22) return 88;
    if ($avgSentenceLength <= 30) return 78;
    return 65;
}

/**
 * Produce warning flags.
 */
function aig_quality_collect_flags(array $article, array $metrics, array $input = []): array
{
    $flags = [];
    $content = (string) ($article['content'] ?? '');

    if (($metrics['length'] ?? 100) < 55) {
        $flags[] = 'too_short';
    }

    if (($metrics['structure'] ?? 100) < 65) {
        $flags[] = 'weak_structure';
    }

    if (($metrics['language_consistency'] ?? 100) < 70) {
        $flags[] = 'language_mismatch';
    }

    if (($metrics['fact_density'] ?? 100) < 65) {
        $flags[] = 'low_fact_density';
    }

    if (($metrics['redundancy'] ?? 100) < 65) {
        $flags[] = 'high_redundancy';
    }

    if (mb_strlen(trim($content)) === 0) {
        $flags[] = 'empty_content';
    }

    return $flags;
}
Quality için mimari not

Kalite motoru olmadan sistem şu tuzağa düşer:

“Bir şey döndü, demek ki tamam.”

Bu çok tehlikeli. Çünkü:

fallback içerik

yüzeysel rewrite

dil bozukluğu

zayıf yapı

sessizce başarı gibi görünür.

Quality katmanı bu yüzden lüks değil, zorunlu editoryal denetim katmanıdır.

Bu aşamadaki net kazanım

Şimdi article zincirinin son ayakları da resmileşti:

core/services/seo-service.php

core/pipelines/seo-pipeline.php

core/ai-article-quality.php

Böylece article üretimi artık kağıt üstünde şu tam hatta oturdu:

Tam hat

bootstrap

ajax

article-service

article-pipeline

context

outline

router

gateway

provider registry

news sources

collector

normalizer

cache

fact pack

rewrite-service

rewrite-pipeline

seo-service

seo-pipeline

quality

selftest

Bu artık gerçekten büyük bir çekirdek rehber oldu.

AŞAMA 12

core/providers/provider-interface.php için resmi sözleşme

core/providers/provider-base-openai-compat.php için ortak temel sınıf

core/providers/provider-openai.php için örnek gerçek provider pseudo-code

Bu üçü tamamlandığında diğer provider dosyaları da aynı kalıptan üretilebilir.

core/providers/provider-interface.php
Resmi görev tanımı

Bu dosya, modüldeki tüm AI provider sınıflarının uyması gereken ortak sözleşmeyi tanımlar.

Amaç:

provider çeşitliliğini kaosa çevirmemek

gateway’in her provider ile aynı temel dilde konuşmasını sağlamak

availability / chat / generate / embeddings gibi alanlarda ortak yapı kurmak

Bu interface:

routing yapmaz

HTTP çağrısı yapmaz

article mantığı taşımaz

yalnız zorunlu provider davranışlarını tanımlar

Provider interface için bağlayıcı kurallar
Kural 1

Tüm provider sınıfları aynı temel interface’i uygulamalıdır.

Kural 2

Gateway provider-specific method isimleri bilmemelidir.

Kural 3

Provider availability kontrolü ortak interface içinde bulunmalıdır.

Kural 4

Chat/generate dönüşleri normalize edilebilir olmalıdır.

Kural 5

Embeddings her provider için zorunlu gerçek destek olmayabilir, ama method sözleşmesi bulunmalıdır.

Önerilen resmi interface
<?php
if (!defined('ABSPATH')) {
    exit;
}

interface AIG_Provider_Interface
{
    /**
     * Stable provider key, e.g. openai, groq, gemini.
     */
    public function get_key(): string;

    /**
     * Human-readable label.
     */
    public function get_label(): string;

    /**
     * Whether this provider is currently usable.
     */
    public function is_available(): bool;

    /**
     * Low-level text generation helper.
     */
    public function generate(array $payload): array;

    /**
     * Chat/messages-based generation.
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Embeddings support.
     */
    public function embeddings(array $input, array $options = []): array;
}
Interface için mimari not

Bu interface küçük görünür ama çok kritik bir karar taşır:

“Provider sistemi tek tek özel case’lerle değil, ortak contract ile yaşar.”

Bu olmazsa:

gateway her provider için ayrı if/else çöplüğüne döner

registry yalnız isim listesi olur

selftest gerçek provider contract’ını test edemez

core/providers/provider-base-openai-compat.php
Resmi görev tanımı

Bu dosya, OpenAI-benzeri API tasarımını kullanan provider’lar için ortak temel sınıf olmalıdır.

Amaç:

tekrarlanan HTTP çağrı mantığını merkezileştirmek

auth header

endpoint seçimi

request body inşası

response içinden content çıkarımı

usage normalize edilmesi

ortak hata handling

gibi tekrarları tek yerde toplamak.

Bu sınıf:

route kararı vermez

article logic bilmez

panel bilmez

yalnız OpenAI-compatible provider altyapısı sağlar

Base provider için bağlayıcı kurallar
Kural 1

OpenAI-compatible provider’lar ortak temel sınıftan yararlanmalıdır.

Kural 2

Her child provider yalnız kendine özgü endpoint/base-url/model farklarını taşır.

Kural 3

HTTP request ve normalize mantığı merkezileştirilmelidir.

Kural 4

Secret handling ve error normalization bu katmanda güvenli yapılmalıdır.

Kural 5

Base class doğrudan routing/business logic sahibi olmamalıdır.

provider-base-openai-compat.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

abstract class AIG_Provider_Base_OpenAI_Compat implements AIG_Provider_Interface
{
    /**
     * Provider key, e.g. openai, groq, deepseek.
     */
    abstract public function get_key(): string;

    /**
     * Provider label.
     */
    abstract public function get_label(): string;

    /**
     * Base URL for provider API.
     */
    abstract protected function get_base_url(): string;

    /**
     * Default chat endpoint path.
     */
    protected function get_chat_endpoint(): string
    {
        return '/chat/completions';
    }

    /**
     * API key lookup.
     */
    protected function get_api_key(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];
            $key = $this->get_key();

            if (!empty($providers[$key]['api_key'])) {
                return (string) $providers[$key]['api_key'];
            }
        }

        return '';
    }

    /**
     * Availability means:
     * - class exists
     * - provider enabled if config exists
     * - api key exists (or provider supports no-key local mode)
     */
    public function is_available(): bool
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];
            $key = $this->get_key();

            if (isset($providers[$key]['enabled']) && !$providers[$key]['enabled']) {
                return false;
            }
        }

        return $this->get_api_key() !== '';
    }

    /**
     * Generic generate wrapper. For compatible providers,
     * this can map payload -> chat.
     */
    public function generate(array $payload): array
    {
        $messages = $payload['messages'] ?? [];

        if (empty($messages) && !empty($payload['prompt'])) {
            $messages = [
                ['role' => 'user', 'content' => (string) $payload['prompt']],
            ];
        }

        return $this->chat($messages, $payload['options'] ?? []);
    }

    /**
     * Standard chat request for OpenAI-compatible APIs.
     */
    public function chat(array $messages, array $options = []): array
    {
        if (!$this->is_available()) {
            return $this->build_error(
                'provider_unavailable',
                $this->get_label() . ' is unavailable or missing configuration.'
            );
        }

        $url = rtrim($this->get_base_url(), '/') . $this->get_chat_endpoint();
        $body = $this->build_chat_body($messages, $options);
        $headers = $this->build_headers();

        $response = $this->http_post_json($url, $body, $headers, (int) ($options['timeout'] ?? 30));

        if (empty($response['ok'])) {
            return $response;
        }

        return $this->normalize_chat_response($response['data'] ?? []);
    }

    /**
     * Default embeddings fallback: unsupported.
     */
    public function embeddings(array $input, array $options = []): array
    {
        return $this->build_error(
            'embeddings_not_supported',
            $this->get_label() . ' embeddings are not implemented for this provider.'
        );
    }

    /**
     * Build headers with auth.
     */
    protected function build_headers(): array
    {
        return [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->get_api_key(),
        ];
    }

    /**
     * Build chat body in OpenAI-compatible shape.
     */
    protected function build_chat_body(array $messages, array $options = []): array
    {
        return [
            'model' => (string) ($options['model'] ?? ''),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.5,
            'max_tokens' => $options['max_tokens'] ?? 1200,
        ];
    }

    /**
     * HTTP helper.
     */
    protected function http_post_json(string $url, array $body, array $headers, int $timeout = 30): array
    {
        $args = [
            'headers' => $headers,
            'body' => wp_json_encode($body),
            'timeout' => $timeout,
        ];

        $res = wp_remote_post($url, $args);

        if (is_wp_error($res)) {
            return $this->build_error(
                'http_error',
                $res->get_error_message()
            );
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        $rawBody = (string) wp_remote_retrieve_body($res);
        $json = json_decode($rawBody, true);

        if ($code < 200 || $code >= 300) {
            return $this->build_error(
                'http_error',
                'HTTP ' . $code . ' returned by ' . $this->get_label(),
                [
                    'http_code' => $code,
                ]
            );
        }

        if (!is_array($json)) {
            return $this->build_error(
                'invalid_response',
                $this->get_label() . ' returned non-JSON or invalid JSON response.'
            );
        }

        return [
            'ok' => true,
            'data' => $json,
            'error' => null,
        ];
    }

    /**
     * Normalize provider response into shared provider contract.
     */
    protected function normalize_chat_response(array $data): array
    {
        $content = '';
        $usage = [
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
        ];

        if (!empty($data['choices'][0]['message']['content'])) {
            $content = (string) $data['choices'][0]['message']['content'];
        }

        if (!empty($data['usage']) && is_array($data['usage'])) {
            $usage = [
                'prompt_tokens' => (int) ($data['usage']['prompt_tokens'] ?? 0),
                'completion_tokens' => (int) ($data['usage']['completion_tokens'] ?? 0),
                'total_tokens' => (int) ($data['usage']['total_tokens'] ?? 0),
            ];
        }

        return [
            'ok' => true,
            'content' => $content,
            'provider' => $this->get_key(),
            'model' => (string) ($data['model'] ?? ''),
            'usage' => $usage,
            'raw' => $data,
            'error' => null,
        ];
    }

    /**
     * Shared normalized error response.
     */
    protected function build_error(string $code, string $message, array $meta = []): array
    {
        return [
            'ok' => false,
            'content' => '',
            'provider' => $this->get_key(),
            'model' => '',
            'usage' => [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
            ],
            'raw' => [],
            'meta' => $meta,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];
    }
}
Base provider için mimari not

Bu sınıfın ana faydası şudur:

OpenAI, Groq, DeepSeek, OpenRouter gibi benzer davranan sağlayıcıları tekrar tekrar sıfırdan yazmamak.

Bu olmazsa provider klasörü zamanla:

kopyala-yapıştır

küçük farklarla büyüyen kaotik dosyalar

zor debug edilen farklı response şemaları

haline gelir.

core/providers/provider-openai.php
Resmi görev tanımı

Bu dosya OpenAI için somut provider adapter’ıdır.

Görevi:

OpenAI key/config okumak

base URL sağlamak

gerekirse endpoint override etmek

inherited temel sınıfı kullanarak çalışmak

Bu dosya minimal olmalıdır.
Çünkü ağır mantık base class’ta olmalıdır.

provider-openai.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

class AIG_Provider_OpenAI extends AIG_Provider_Base_OpenAI_Compat
{
    public function get_key(): string
    {
        return 'openai';
    }

    public function get_label(): string
    {
        return 'OpenAI';
    }

    protected function get_base_url(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['openai']['base_url'])) {
                return (string) $providers['openai']['base_url'];
            }
        }

        return 'https://api.openai.com/v1';
    }

    protected function get_api_key(): string
    {
        // 1. storage/providers.json
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['openai']['api_key'])) {
                return (string) $providers['openai']['api_key'];
            }
        }

        // 2. optional env fallback
        $env = getenv('OPENAI_API_KEY');
        if (is_string($env) && $env !== '') {
            return $env;
        }

        return '';
    }

    protected function build_chat_body(array $messages, array $options = []): array
    {
        return [
            'model' => (string) ($options['model'] ?? 'gpt-4.1-mini'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.5,
            'max_tokens' => $options['max_tokens'] ?? 1200,
        ];
    }
}
provider-openai.php için mimari not

Bu örnek dosya bilinçli olarak sade.

Çünkü hedef şu:

OpenAI provider kısa olsun

özel farklar yalnız burada dursun

bütün tekrar base class’ta kalsın

Aynı şablonla sonra şunlar kolayca üretilebilir:

provider-groq.php

provider-deepseek.php

provider-openrouter.php

provider-mistral.php

Şimdi bu provider yapısıyla ne kazanıyoruz?

Artık zincir şu şekilde gerçekten kapanıyor:

Router

provider=model kararını verir

Registry

ilgili provider sınıfını üretir

Gateway

provider’a normalize çağrı yapar

Provider

dış API ile konuşur

Yani artık çoklu model mimarisi “sadece fikir” değil, somut teknik iskelet haline geliyor.

AŞAMA 13

core/ai-article-settings.php sözleşmesi + pseudo-code

storage JSON helper sözleşmesi + pseudo-code

feature map access sözleşmesi + pseudo-code

Bu üçü birlikte şunu çözüyor:

ayarlar tek yerden okunur

JSON dosyaları güvenli ve tutarlı yazılır

feature flags dağınık if/else yerine merkezi okunur

core/ai-article-settings.php
Resmi görev tanımı

ai-article-settings.php, modülün ana ayar erişim katmanı olmalıdır.

Bu dosyanın görevi:

storage/settings.json dosyasını resmi kaynak olarak okumak

varsayılan ayarlarla birleştirmek

belirli bir ayarı güvenli şekilde döndürmek

ayar güncelleme helper’ı sunmak

modülün farklı katmanlarına tek tip settings erişimi sağlamak

Bu dosya:

panel render etmemeli

article generate etmemeli

provider HTTP çağrısı yapmamalı

business logic sahibi olmamalı

Settings için bağlayıcı kurallar
Kural 1

Ayarların resmi kaynağı storage/settings.json olmalıdır.

Kural 2

Kod içinde dağınık file_get_contents(settings.json) kullanımı olmamalıdır.

Kural 3

Her settings erişimi varsayılanlarla birleşmiş, normalize edilmiş veri döndürmelidir.

Kural 4

Bozuk veya eksik settings dosyası modülü çöktürmemeli; defaults ile toparlanmalıdır.

Kural 5

Settings erişimi tek merkezden yapılmalıdır.

Settings resmi mantıksal şema örneği

Bu birebir final şema olmak zorunda değil; ama hedef mantık şu düzeyde olmalı:

{
  "version": "1.0.0",
  "article": {
    "default_lang": "tr",
    "default_tone": "analytical",
    "default_length": "long",
    "default_template": "news_analysis",
    "auto_rewrite": true,
    "auto_seo": true
  },
  "news": {
    "cache_ttl_seconds": 1800,
    "max_items_per_source": 10
  },
  "routing": {
    "default_quality_profile": "balanced"
  },
  "quality": {
    "threshold": 70
  }
}
Settings için önerilen ana fonksiyonlar
1. aig_settings_defaults(): array

Varsayılan ayarlar.

2. aig_settings_get_all(): array

Defaults + settings.json birleşik sonucu.

3. aig_settings_get(string $path, $default = null)

Noktalı yol ile ayar alma.
Örnek: article.default_lang

4. aig_settings_set_all(array $data): bool

Tüm settings’i yaz.

5. aig_settings_merge_with_defaults(array $data): array

Eksik alanları defaults ile tamamla.

ai-article-settings.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default module settings.
 */
function aig_settings_defaults(): array
{
    return [
        'version' => '1.0.0',
        'article' => [
            'default_lang' => 'tr',
            'default_tone' => 'analytical',
            'default_length' => 'long',
            'default_template' => 'news_analysis',
            'auto_rewrite' => true,
            'auto_seo' => true,
        ],
        'news' => [
            'cache_ttl_seconds' => 1800,
            'max_items_per_source' => 10,
        ],
        'routing' => [
            'default_quality_profile' => 'balanced',
        ],
        'quality' => [
            'threshold' => 70,
        ],
    ];
}

/**
 * Return fully merged settings.
 */
function aig_settings_get_all(): array
{
    $defaults = aig_settings_defaults();

    if (!function_exists('aig_storage_read_json')) {
        return $defaults;
    }

    $stored = aig_storage_read_json('settings.json');
    if (!is_array($stored)) {
        return $defaults;
    }

    return aig_settings_merge_with_defaults($stored);
}

/**
 * Merge stored settings with defaults.
 */
function aig_settings_merge_with_defaults(array $data): array
{
    $defaults = aig_settings_defaults();

    if (function_exists('array_replace_recursive')) {
        return array_replace_recursive($defaults, $data);
    }

    // Conservative fallback
    return array_merge($defaults, $data);
}

/**
 * Dot-path getter. Example: article.default_lang
 */
function aig_settings_get(string $path, $default = null)
{
    $settings = aig_settings_get_all();

    if ($path === '') {
        return $settings;
    }

    $segments = explode('.', $path);
    $value = $settings;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

/**
 * Save full settings payload.
 */
function aig_settings_set_all(array $data): bool
{
    if (!function_exists('aig_storage_write_json')) {
        return false;
    }

    $merged = aig_settings_merge_with_defaults($data);
    return aig_storage_write_json('settings.json', $merged);
}
Settings için mimari not

Bu dosya küçük görünür ama merkezi önemi büyüktür.
Çünkü settings tek merkezde değilse:

router başka default kullanır

article-service başka default kullanır

panel başka default gösterir

selftest başka dosya arar

Sonuç: sessiz tutarsızlık.

Storage JSON Helper Katmanı
Resmi görev tanımı

Bu katman, storage/*.json dosyaları için tek resmi okuma/yazma kapısı olmalıdır.

Amaç:

tüm JSON okuma/yazma işlemlerini tek biçimde yapmak

bozuk JSON riskini azaltmak

atomic write yaklaşımına yaklaşmak

tekrar eden file_exists/json_decode/file_put_contents kodlarını tek yerde toplamak

Bu katman:

business logic taşımaz

article logic bilmez

panel bilmez

yalnız storage I/O standardı sağlar

Storage helper için bağlayıcı kurallar
Kural 1

storage/*.json erişimi helper üzerinden yapılmalıdır.

Kural 2

Bozuk JSON durumunda helper güvenli fallback döndürmelidir.

Kural 3

Write işlemleri mümkün olduğunca güvenli yapılmalıdır.

Kural 4

Path çözümü merkezi olmalıdır.

Kural 5

Storage helper business logic sahibi olmamalıdır.

Önerilen ana fonksiyonlar
1. aig_storage_base_dir(): string

Storage klasörünü döndürür.

2. aig_storage_path(string $filename): string

Tam dosya yolunu üretir.

3. aig_storage_read_json(string $filename): ?array

JSON oku.

4. aig_storage_write_json(string $filename, array $data): bool

JSON yaz.

5. aig_storage_exists(string $filename): bool

Dosya var mı.

6. aig_storage_read_or_default(string $filename, array $default): array

Yoksa default dön.

Storage helper pseudo-code

Bunu ayrı bir dosyada tutmak en temiz yol olur. Örnek isim:

core/ai-storage.php
veya

core/ai-article-storage.php

Aşağıda sözleşme kodu:

<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return base storage directory.
 */
function aig_storage_base_dir(): string
{
    if (defined('AIG_STORAGE_DIR')) {
        return AIG_STORAGE_DIR;
    }

    return __DIR__ . '/../storage';
}

/**
 * Build full path for a storage JSON file.
 */
function aig_storage_path(string $filename): string
{
    return rtrim(aig_storage_base_dir(), '/\\') . '/' . ltrim($filename, '/\\');
}

/**
 * Check whether storage file exists.
 */
function aig_storage_exists(string $filename): bool
{
    return file_exists(aig_storage_path($filename));
}

/**
 * Read JSON file safely.
 */
function aig_storage_read_json(string $filename): ?array
{
    $path = aig_storage_path($filename);

    if (!file_exists($path)) {
        return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return null;
    }

    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return null;
    }

    return $json;
}

/**
 * Read JSON or return default.
 */
function aig_storage_read_or_default(string $filename, array $default): array
{
    $data = aig_storage_read_json($filename);
    return is_array($data) ? $data : $default;
}

/**
 * Write JSON safely.
 */
function aig_storage_write_json(string $filename, array $data): bool
{
    $path = aig_storage_path($filename);
    $dir = dirname($path);

    if (!is_dir($dir) && !wp_mkdir_p($dir)) {
        return false;
    }

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }

    $tmp = $path . '.tmp';
    $written = file_put_contents($tmp, $json);

    if ($written === false) {
        return false;
    }

    return rename($tmp, $path);
}
Storage helper için mimari not

Bu katman yazılmadan modülde şu sorunlar büyür:

her dosya kendi JSON okuma stilini yazar

biri null, biri [], biri false döndürür

bozuk JSON’da davranışlar ayrışır

debug zorlaşır

Bu yüzden storage helper, görünmez ama kritik altyapıdır.

Feature Map Access Katmanı
Resmi görev tanımı

Feature map katmanı, modüldeki aktif/pasif özellikleri merkezi olarak okur.

Amaç:

feature-map.json içindeki bayrakları merkezi okumak

generate/rewrite/seo/selftest gibi alanlarda feature gate uygulamak

dağınık if ($someFlag) mantıklarını tek kaynakta toplamak

Bu katman:

feature politikası okur

kararın tek kaynağı olur

ama business logic’in yerine geçmez

Feature map için bağlayıcı kurallar
Kural 1

Feature flag’ler storage/feature-map.json içinden okunmalıdır.

Kural 2

Kod içinde gömülü bayrak mantığı minimum olmalıdır.

Kural 3

Eksik feature map dosyasında güvenli defaults kullanılmalıdır.

Kural 4

Feature access helper düz ve sade olmalıdır.

Önerilen ana fonksiyonlar
1. aig_feature_map_defaults(): array

Varsayılan flag’ler.

2. aig_feature_map_get_all(): array

Tam feature map.

3. aig_feature_is_enabled(string $key, bool $default = true): bool

Tek bir flag sorgusu.

4. aig_feature_map_set_all(array $data): bool

Feature map kaydet.

Feature map pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default feature flags.
 */
function aig_feature_map_defaults(): array
{
    return [
        'article_generation_disabled' => false,
        'rewrite_disabled' => false,
        'seo_disabled' => false,
        'selftest_runtime_checks_enabled' => true,
        'quality_gate_enabled' => true,
        'news_cache_enabled' => true,
    ];
}

/**
 * Return merged feature map.
 */
function aig_feature_map_get_all(): array
{
    $defaults = aig_feature_map_defaults();

    if (!function_exists('aig_storage_read_json')) {
        return $defaults;
    }

    $stored = aig_storage_read_json('feature-map.json');
    if (!is_array($stored)) {
        return $defaults;
    }

    return array_replace($defaults, $stored);
}

/**
 * Check single feature flag.
 */
function aig_feature_is_enabled(string $key, bool $default = true): bool
{
    $map = aig_feature_map_get_all();

    if (!array_key_exists($key, $map)) {
        return $default;
    }

    return (bool) $map[$key];
}

/**
 * Save full feature map.
 */
function aig_feature_map_set_all(array $data): bool
{
    if (!function_exists('aig_storage_write_json')) {
        return false;
    }

    $merged = array_replace(aig_feature_map_defaults(), $data);
    return aig_storage_write_json('feature-map.json', $merged);
}
Feature map için mimari not

Bu katman güçlü olduğunda:

article generate’i tek yerden kapatabilirsin

rewrite hattını kontrollü disable edebilirsin

SEO veya quality gate’i geçici kapatabilirsin

selftest runtime testlerini yönetebilirsin

Yani feature map, modülün kontrollü aç/kapa sinir sistemi olur.

Bu aşamadaki net kazanım

Artık pseudo-code seviyesinde yalnız üst katman değil, alt veri omurgası da resmileşti:

Storage / config çekirdeği

ai-article-settings.php

storage JSON helper

feature map access

Bu çok kritik çünkü artık üstte yazdığımız dosyalar:

settings okuyabiliyor

providers/models/router/health yazabiliyor

feature gate uygulayabiliyor

Şu an elimizde pseudo-code düzeyinde tamamlanan büyük omurga
Bootstrap / entry

ai-article-generator.php

ajax-handler.php

Config / storage

ai-article-settings.php

storage helpers

feature map helpers

Services

article-service.php

rewrite-service.php

seo-service.php

selftest-service.php

AI routing / execution

ai-article-router.php

ai-article-gateway.php

ai-article-provider-registry.php

provider-interface.php

provider-base-openai-compat.php

provider-openai.php

Article generation

ai-article-pipeline.php

ai-article-context.php

ai-article-outline.php

News

news-sources.php

news-collector.php

news-normalizer.php

news-cache.php

news-fact-pack.php

Rewrite / SEO / Quality

rewrite-pipeline.php

seo-pipeline.php

ai-article-quality.php

Bu artık gerçekten büyük ölçüde kod yazıma hazır mimari iskelet.

AŞAMA 14

core/providers/provider-groq.php

core/providers/provider-openrouter.php

core/providers/provider-deepseek.php

Bunlar aynı base class’tan türeyecek ama kendi:

key

label

base url

default model

config okuma farkları

olacak.

core/providers/provider-groq.php
Resmi görev tanımı

Bu dosya Groq için somut provider adapter’ıdır.

Görevi:

Groq provider kimliğini tanımlamak

base URL vermek

API key erişimi sağlamak

gerekirse varsayılan model atamak

ortak base class davranışıyla Groq çağrısı yapmak

Bu dosya:

router kararı vermez

prompt yazmaz

article logic taşımaz

Groq provider için bağlayıcı kurallar
Kural 1

Groq provider yalnız Groq’a özgü farkları taşımalıdır.

Kural 2

HTTP ve normalize mantığı base class’ta kalmalıdır.

Kural 3

Config önceliği: storage → env → boş.

Kural 4

Groq route ve capability kararı router’dan gelmelidir.

provider-groq.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

class AIG_Provider_Groq extends AIG_Provider_Base_OpenAI_Compat
{
    public function get_key(): string
    {
        return 'groq';
    }

    public function get_label(): string
    {
        return 'Groq';
    }

    protected function get_base_url(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['groq']['base_url'])) {
                return (string) $providers['groq']['base_url'];
            }
        }

        return 'https://api.groq.com/openai/v1';
    }

    protected function get_api_key(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['groq']['api_key'])) {
                return (string) $providers['groq']['api_key'];
            }
        }

        $env = getenv('GROQ_API_KEY');
        if (is_string($env) && $env !== '') {
            return $env;
        }

        return '';
    }

    protected function build_chat_body(array $messages, array $options = []): array
    {
        return [
            'model' => (string) ($options['model'] ?? 'llama-3.3-70b-versatile'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.5,
            'max_tokens' => $options['max_tokens'] ?? 1200,
        ];
    }
}
Groq provider için mimari not

Groq genelde:

hızlı

maliyet/performans dengesi iyi

bazı task’lerde çok uygun

olduğu için router tarafında özellikle:

summary

seo

hızlı rewrite

bazı article generate fallback’lerinde

değerli aday olabilir.

Ama bu karar provider dosyasında değil, router’da olmalıdır.

core/providers/provider-openrouter.php
Resmi görev tanımı

Bu dosya OpenRouter için somut provider adapter’ıdır.

Görevi:

OpenRouter key/config okumak

OpenRouter base URL sağlamak

OpenAI-compatible chat endpoint ile çağrı yapmak

gerektiğinde default model kullanmak

Bu dosya OpenRouter’a özgü:

base URL

env key adı

varsayılan model seçimi

farklarını taşır.

OpenRouter provider için bağlayıcı kurallar
Kural 1

OpenRouter provider yalnız OpenRouter’a özgü config farklarını taşımalıdır.

Kural 2

Model routing kararları provider içinde hardcode edilmemelidir.

Kural 3

OpenRouter üzerinde çok farklı upstream model’ler çalışabileceği için router/model config daha önemli hale gelir.

Kural 4

Provider yine de tek normalize contract döndürmelidir.

provider-openrouter.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

class AIG_Provider_OpenRouter extends AIG_Provider_Base_OpenAI_Compat
{
    public function get_key(): string
    {
        return 'openrouter';
    }

    public function get_label(): string
    {
        return 'OpenRouter';
    }

    protected function get_base_url(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['openrouter']['base_url'])) {
                return (string) $providers['openrouter']['base_url'];
            }
        }

        return 'https://openrouter.ai/api/v1';
    }

    protected function get_api_key(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['openrouter']['api_key'])) {
                return (string) $providers['openrouter']['api_key'];
            }
        }

        $env = getenv('OPENROUTER_API_KEY');
        if (is_string($env) && $env !== '') {
            return $env;
        }

        return '';
    }

    protected function build_headers(): array
    {
        $headers = parent::build_headers();

        // İsteğe bağlı referer/title gibi ek header alanları buraya eklenebilir
        // ama güvenli ve kontrollü tutulmalı.
        return $headers;
    }

    protected function build_chat_body(array $messages, array $options = []): array
    {
        return [
            'model' => (string) ($options['model'] ?? 'openai/gpt-4.1-mini'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.5,
            'max_tokens' => $options['max_tokens'] ?? 1200,
        ];
    }
}
OpenRouter provider için mimari not

OpenRouter’ın en büyük gücü:

tek endpoint üstünden çok farklı model ailelerine erişim

olduğu için router tarafında çok değerli bir omurga olabilir.

Ama aynı yüzden dikkat edilmesi gereken şey de şudur:

provider dosyası değil

models.json + router.json

asıl model seçim zekâsını taşımalıdır.

core/providers/provider-deepseek.php
Resmi görev tanımı

Bu dosya DeepSeek için somut provider adapter’ıdır.

Görevi:

DeepSeek provider kimliğini tanımlamak

DeepSeek base URL sağlamak

API key erişimi sağlamak

OpenAI-compatible davranışla chat/generate çağrıları yapmak

DeepSeek provider için bağlayıcı kurallar
Kural 1

DeepSeek provider yalnız kendi provider farklarını taşır.

Kural 2

DeepSeek model seçimi provider içinde sabitlenmek yerine router/model config ile belirlenmelidir.

Kural 3

OpenAI-compatible ise ortak base class kullanımı korunmalıdır.

Kural 4

Config, storage ve env’den güvenli okunmalıdır.

provider-deepseek.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

class AIG_Provider_DeepSeek extends AIG_Provider_Base_OpenAI_Compat
{
    public function get_key(): string
    {
        return 'deepseek';
    }

    public function get_label(): string
    {
        return 'DeepSeek';
    }

    protected function get_base_url(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['deepseek']['base_url'])) {
                return (string) $providers['deepseek']['base_url'];
            }
        }

        return 'https://api.deepseek.com/v1';
    }

    protected function get_api_key(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['deepseek']['api_key'])) {
                return (string) $providers['deepseek']['api_key'];
            }
        }

        $env = getenv('DEEPSEEK_API_KEY');
        if (is_string($env) && $env !== '') {
            return $env;
        }

        return '';
    }

    protected function build_chat_body(array $messages, array $options = []): array
    {
        return [
            'model' => (string) ($options['model'] ?? 'deepseek-chat'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.5,
            'max_tokens' => $options['max_tokens'] ?? 1200,
        ];
    }
}
DeepSeek provider için mimari not

DeepSeek özellikle kullanıcı hedefin olan:

çoklu model

çoklu sağlayıcı

maliyet/kalite dengesi

OpenAI’ye bağımlılığı azaltma

vizyonu için kritik oyunculardan biri.

Ama yine aynı prensip geçerli:

DeepSeek’in nerede kullanılacağına provider dosyası değil, router karar vermeli.

OpenAI-compatible provider ailesi için genel sonuç

Bu aşamadan sonra aynı şablonla rahatça üretilebilecek dosyalar:

provider-mistral.php

provider-ollama.php
(not: ollama yerel yapıysa availability ve auth mantığı biraz ayrışabilir)

gerektiğinde başka compatible provider’lar

Yani artık provider ailesi “her seferinde sıfırdan” değil, ortak soy ağacıyla büyütülebilir.

Çok önemli mimari sonuç

Şu an elimizde provider katmanı için tam bir soy ağacı oluştu:

Sözleşme

provider-interface.php

Ortak temel

provider-base-openai-compat.php

Somut provider’lar

provider-openai.php

provider-groq.php

provider-openrouter.php

provider-deepseek.php

Bu, modülün çoklu provider omurgasını artık gerçekten taşıyabilir hale getiriyor.

AŞAMA 15

core/providers/provider-gemini.php

core/providers/provider-mistral.php

core/providers/provider-ollama.php

Bu aşama önemli çünkü artık çoklu sağlayıcı vizyonu gerçek anlamda genişliyor:

OpenAI

Groq

OpenRouter

DeepSeek

Gemini

Mistral

Ollama

böylece modül tek merkeze bağlı kalmadan çalışabilecek omurgaya yaklaşmış oluyor.

core/providers/provider-gemini.php
Resmi görev tanımı

Bu dosya Gemini için somut provider adapter’ıdır.

Gemini çoğu zaman OpenAI-compatible katmandan birebir yürümeyebilir. Bu yüzden ayrı adapter olarak düşünmek daha sağlıklıdır.

Görevi:

Gemini key/config okumak

Gemini endpoint kurmak

Gemini request body oluşturmak

Gemini response içinden metin çıkarmak

normalize edilmiş provider response döndürmek

Bu dosya:

router kararı vermez

article logic taşımaz

panel mantığı taşımaz

Gemini provider için bağlayıcı kurallar
Kural 1

Gemini provider kendi request/response biçimini kendisi map etmelidir.

Kural 2

Dışarıya yine ortak provider response contract’ı döndürmelidir.

Kural 3

Config erişimi providers.json ve gerekirse env üzerinden yapılmalıdır.

Kural 4

Gateway, Gemini’nin iç alan adlarını bilmek zorunda kalmamalıdır.

provider-gemini.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

class AIG_Provider_Gemini implements AIG_Provider_Interface
{
    public function get_key(): string
    {
        return 'gemini';
    }

    public function get_label(): string
    {
        return 'Gemini';
    }

    public function is_available(): bool
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (isset($providers['gemini']['enabled']) && !$providers['gemini']['enabled']) {
                return false;
            }
        }

        return $this->get_api_key() !== '';
    }

    public function generate(array $payload): array
    {
        $messages = $payload['messages'] ?? [];

        if (empty($messages) && !empty($payload['prompt'])) {
            $messages = [
                ['role' => 'user', 'content' => (string) $payload['prompt']],
            ];
        }

        return $this->chat($messages, $payload['options'] ?? []);
    }

    public function chat(array $messages, array $options = []): array
    {
        if (!$this->is_available()) {
            return $this->build_error(
                'provider_unavailable',
                'Gemini is unavailable or missing configuration.'
            );
        }

        $model = (string) ($options['model'] ?? 'gemini-1.5-pro');
        $url = rtrim($this->get_base_url(), '/') . '/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($this->get_api_key());

        $body = $this->build_body($messages, $options);

        $res = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => (int) ($options['timeout'] ?? 30),
        ]);

        if (is_wp_error($res)) {
            return $this->build_error(
                'http_error',
                $res->get_error_message()
            );
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        $rawBody = (string) wp_remote_retrieve_body($res);
        $json = json_decode($rawBody, true);

        if ($code < 200 || $code >= 300) {
            return $this->build_error(
                'http_error',
                'HTTP ' . $code . ' returned by Gemini.',
                ['http_code' => $code]
            );
        }

        if (!is_array($json)) {
            return $this->build_error(
                'invalid_response',
                'Gemini returned non-JSON or invalid JSON response.'
            );
        }

        return $this->normalize_response($json, $model);
    }

    public function embeddings(array $input, array $options = []): array
    {
        return $this->build_error(
            'embeddings_not_supported',
            'Gemini embeddings are not implemented in this provider adapter.'
        );
    }

    protected function get_base_url(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['gemini']['base_url'])) {
                return (string) $providers['gemini']['base_url'];
            }
        }

        return 'https://generativelanguage.googleapis.com/v1beta';
    }

    protected function get_api_key(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['gemini']['api_key'])) {
                return (string) $providers['gemini']['api_key'];
            }
        }

        $env = getenv('GEMINI_API_KEY');
        if (is_string($env) && $env !== '') {
            return $env;
        }

        return '';
    }

    protected function build_body(array $messages, array $options = []): array
    {
        $parts = [];

        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? 'user');
            $content = (string) ($message['content'] ?? '');

            if ($content === '') {
                continue;
            }

            // Gemini roles differ; map system to user-like content if needed.
            $parts[] = [
                'role' => $role === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $content],
                ],
            ];
        }

        return [
            'contents' => $parts,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.5,
                'maxOutputTokens' => $options['max_tokens'] ?? 1200,
            ],
        ];
    }

    protected function normalize_response(array $data, string $model): array
    {
        $content = '';

        if (!empty($data['candidates'][0]['content']['parts']) && is_array($data['candidates'][0]['content']['parts'])) {
            foreach ($data['candidates'][0]['content']['parts'] as $part) {
                if (!empty($part['text'])) {
                    $content .= (string) $part['text'];
                }
            }
        }

        return [
            'ok' => true,
            'content' => $content,
            'provider' => $this->get_key(),
            'model' => $model,
            'usage' => [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
            ],
            'raw' => $data,
            'error' => null,
        ];
    }

    protected function build_error(string $code, string $message, array $meta = []): array
    {
        return [
            'ok' => false,
            'content' => '',
            'provider' => $this->get_key(),
            'model' => '',
            'usage' => [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
            ],
            'raw' => [],
            'meta' => $meta,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];
    }
}
Gemini provider için mimari not

Gemini’nin asıl önemi şu:

OpenAI-compatible aileden farklı davranan provider’lar için modülde ayrı adapter alanı açıyor

bu sayede mimari “her şeyi tek kalıba zorlayan” kırılgan yapı olmuyor

Yani bu dosya aslında şunu kanıtlıyor:

Modül yalnız compatible API’leri değil, farklı AI ekosistemlerini de taşıyabilecek şekilde tasarlanıyor.

core/providers/provider-mistral.php
Resmi görev tanımı

Bu dosya Mistral için somut provider adapter’ıdır.

Mistral çoğu senaryoda OpenAI-compatible davranış gösterebildiği için base class’tan türetmek mantıklıdır.

Görevi:

provider key/label vermek

base URL ve key okumak

varsayılan model tanımlamak

ortak base class ile çalışmak

provider-mistral.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

class AIG_Provider_Mistral extends AIG_Provider_Base_OpenAI_Compat
{
    public function get_key(): string
    {
        return 'mistral';
    }

    public function get_label(): string
    {
        return 'Mistral';
    }

    protected function get_base_url(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['mistral']['base_url'])) {
                return (string) $providers['mistral']['base_url'];
            }
        }

        return 'https://api.mistral.ai/v1';
    }

    protected function get_api_key(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['mistral']['api_key'])) {
                return (string) $providers['mistral']['api_key'];
            }
        }

        $env = getenv('MISTRAL_API_KEY');
        if (is_string($env) && $env !== '') {
            return $env;
        }

        return '';
    }

    protected function build_chat_body(array $messages, array $options = []): array
    {
        return [
            'model' => (string) ($options['model'] ?? 'mistral-large-latest'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.5,
            'max_tokens' => $options['max_tokens'] ?? 1200,
        ];
    }
}
Mistral provider için mimari not

Mistral çoğu durumda:

balanced route’larda

rewrite ya da SEO’de

bazı quality/cost dengesi senaryolarında

değerli olabilir.

Yine karar provider dosyasında değil, router tarafında kalmalıdır.

core/providers/provider-ollama.php
Resmi görev tanımı

Bu dosya Ollama için somut provider adapter’ıdır.

Ollama diğer cloud provider’lardan biraz farklı düşünülebilir çünkü:

local olabilir

API key gerekmeyebilir

availability daha çok yerel servis erişimine bağlı olabilir

Bu yüzden OpenAI-compatible olsa bile availability ve auth mantığında daha özel davranmak gerekebilir.

Ollama provider için bağlayıcı kurallar
Kural 1

Ollama provider API key zorunlu varsaymamalıdır.

Kural 2

Availability yerel endpoint erişimine göre düşünülmelidir.

Kural 3

Local model mantığı nedeniyle debug/logging daha dikkatli yapılmalıdır.

Kural 4

Cloud provider’larla aynı response contract’ı döndürmelidir.

provider-ollama.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

class AIG_Provider_Ollama extends AIG_Provider_Base_OpenAI_Compat
{
    public function get_key(): string
    {
        return 'ollama';
    }

    public function get_label(): string
    {
        return 'Ollama';
    }

    protected function get_base_url(): string
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['ollama']['base_url'])) {
                return (string) $providers['ollama']['base_url'];
            }
        }

        return 'http://127.0.0.1:11434/v1';
    }

    protected function get_api_key(): string
    {
        // Ollama local mode typically does not require an API key.
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (!empty($providers['ollama']['api_key'])) {
                return (string) $providers['ollama']['api_key'];
            }
        }

        return '';
    }

    public function is_available(): bool
    {
        if (function_exists('aig_storage_read_json')) {
            $providers = aig_storage_read_json('providers.json') ?: [];

            if (isset($providers['ollama']['enabled']) && !$providers['ollama']['enabled']) {
                return false;
            }
        }

        // Basit availability: local endpoint configured.
        $base = $this->get_base_url();
        return $base !== '';
    }

    protected function build_headers(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $apiKey = $this->get_api_key();
        if ($apiKey !== '') {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        return $headers;
    }

    protected function build_chat_body(array $messages, array $options = []): array
    {
        return [
            'model' => (string) ($options['model'] ?? 'llama3'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.5,
            'max_tokens' => $options['max_tokens'] ?? 1200,
        ];
    }
}
Ollama provider için mimari not

Ollama, özellikle senin “tek sağlayıcıya bağlı kalmayalım” vizyonunda çok değerli.

Ama üretim/live kullanımında şu gerçek unutulmamalı:

local availability ayrı bir problem

latency/quality değişken olabilir

selftest ve router bunu cloud provider gibi değerlendirmemeli

Yani Ollama iyi bir esneklik sağlar, ama router tarafında çoğu zaman:

primary değil

fallback / lab / local mode
rolünde daha mantıklı olabilir.

Provider ailesi açısından geldiğimiz nokta

Şu an pseudo-code seviyesinde provider omurgası ciddi biçimde tamamlandı:

Ortak sözleşme

provider-interface.php

Ortak temel sınıf

provider-base-openai-compat.php

Compatible provider’lar

provider-openai.php

provider-groq.php

provider-openrouter.php

provider-deepseek.php

provider-mistral.php

provider-ollama.php

Ayrı adapter

provider-gemini.php

Bu artık çok güçlü bir çoklu sağlayıcı omurgası demek.

Çok önemli mimari sonuç

Artık router tarafı gerçekten aşağıdaki gibi davranabilecek seviyeye geldi:

task = article_generate

primary = openrouter + güçlü model

fallback = openai

tertiary = groq

local fallback = ollama

veya

task = seo_generate

primary = groq

fallback = mistral

backup = openai

Bu vizyon artık soyut değil; pseudo-code düzeyinde somutlaştı.

AŞAMA 16

storage/providers.json resmi şeması

storage/models.json resmi şeması

storage/router.json resmi şeması

Bu üç dosya aslında modülün AI karar/veri omurgasının JSON beyni olacak.

Bunlar net olmazsa:

provider var ama nasıl aktif belli olmaz

model var ama hangi task’e uygun bilinmez

router var ama neye göre karar verdiği bulanık kalır

Yani bu üç JSON dosyası, çoklu sağlayıcı sisteminin çalışan anayasa metni gibi düşünülmeli.

storage/providers.json
Resmi görev tanımı

Bu dosya, provider seviyesinde:

aktif/pasif durumu

base URL

API key

timeout

retry

optional notes / labels

bazı provider-level capability bilgilerini

saklayan resmi config dosyası olmalıdır.

Bu dosya:

router kararını vermez

model-level kalite puanlarını taşımaz

task bazlı route üretmez

Onun işi:

“Hangi provider var, açık mı, nasıl bağlanılır?”

sorusunu cevaplamaktır.

Providers JSON için bağlayıcı kurallar
Kural 1

Her provider tek bir key altında tanımlanmalıdır.

Kural 2

Provider key, registry key ile birebir aynı olmalıdır.

Kural 3

Enabled durumu burada merkezi olarak tutulmalıdır.

Kural 4

API anahtarı boş olabilir ama bu durumda availability false kabul edilebilir.

Kural 5

Provider-level timeout/retry ayarları burada tanımlanabilir.

Kural 6

Provider config ile runtime response shape karıştırılmamalıdır.

Önerilen resmi şema
{
  "schema_version": "1.0.0",
  "providers": {
    "openai": {
      "enabled": true,
      "label": "OpenAI",
      "base_url": "https://api.openai.com/v1",
      "api_key": "",
      "timeout": 45,
      "retry": 1,
      "notes": ""
    },
    "groq": {
      "enabled": true,
      "label": "Groq",
      "base_url": "https://api.groq.com/openai/v1",
      "api_key": "",
      "timeout": 30,
      "retry": 1,
      "notes": ""
    },
    "gemini": {
      "enabled": true,
      "label": "Gemini",
      "base_url": "https://generativelanguage.googleapis.com/v1beta",
      "api_key": "",
      "timeout": 40,
      "retry": 1,
      "notes": ""
    },
    "deepseek": {
      "enabled": true,
      "label": "DeepSeek",
      "base_url": "https://api.deepseek.com/v1",
      "api_key": "",
      "timeout": 35,
      "retry": 1,
      "notes": ""
    },
    "mistral": {
      "enabled": true,
      "label": "Mistral",
      "base_url": "https://api.mistral.ai/v1",
      "api_key": "",
      "timeout": 35,
      "retry": 1,
      "notes": ""
    },
    "openrouter": {
      "enabled": true,
      "label": "OpenRouter",
      "base_url": "https://openrouter.ai/api/v1",
      "api_key": "",
      "timeout": 45,
      "retry": 1,
      "notes": ""
    },
    "ollama": {
      "enabled": false,
      "label": "Ollama",
      "base_url": "http://127.0.0.1:11434/v1",
      "api_key": "",
      "timeout": 60,
      "retry": 0,
      "notes": "Local provider"
    }
  }
}
Providers JSON için mimari not

Bu şemada özellikle şunlar önemlidir:

enabled

Provider’ın sistemde aktif kullanıma açık olup olmadığını belirler.

base_url

Provider adapter’ı için merkezi endpoint bilgisidir.

api_key

Panelden kaydedilebilir veya env ile override edilebilir.

timeout

Provider-level varsayılan timeout.

retry

Gateway retry varsayılanı için provider-level ipucu olabilir.

Çok önemli karar

Ben burada providers isimli kök nesne öneriyorum.

Yani şu yapıyı öneriyorum:

{
  "schema_version": "1.0.0",
  "providers": { ... }
}

Bunun sebebi:

ileride metadata eklemek kolaylaşır

dosya daha ölçeklenebilir olur

çıplak provider map yerine sürüm bilgisi taşınır

storage/models.json
Resmi görev tanımı

Bu dosya, model seviyesinde:

provider bağlantısı

label

kalite/speed/cost skorları

task capability

varsayılan token/temperature bilgileri

structured output desteği

long context desteği

gibi karar verici bilgileri saklar.

Bu dosya router için çok kritiktir.

Providers JSON cevap verir:

“hangi sağlayıcı aktif?”

Models JSON cevap verir:

“hangi model ne iş için uygun?”

Models JSON için bağlayıcı kurallar
Kural 1

Model config provider’dan ayrı tutulmalıdır.

Kural 2

Her model kendi provider key’i ile ilişkilendirilmelidir.

Kural 3

Task capability burada açıkça tanımlanmalıdır.

Kural 4

Router skorlaması için quality/speed/cost alanları burada bulunmalıdır.

Kural 5

Model dosyası yalnız metadata taşır; canlı runtime usage verisi taşımaz.

Önerilen resmi şema

Benim önerim provider bazlı nested yapı:

{
  "schema_version": "1.0.0",
  "models": {
    "openai": {
      "gpt-4.1-mini": {
        "label": "GPT-4.1 Mini",
        "enabled": true,
        "tasks": [
          "article_generate",
          "article_rewrite",
          "seo_generate",
          "title_generate",
          "summary_generate"
        ],
        "quality_score": 88,
        "speed_score": 82,
        "cost_score": 70,
        "max_tokens": 2400,
        "temperature": 0.6,
        "supports_json": true,
        "supports_stream": true,
        "supports_long_context": true
      }
    },
    "groq": {
      "llama-3.3-70b-versatile": {
        "label": "Llama 3.3 70B Versatile",
        "enabled": true,
        "tasks": [
          "article_generate",
          "article_rewrite",
          "seo_generate",
          "summary_generate"
        ],
        "quality_score": 82,
        "speed_score": 94,
        "cost_score": 88,
        "max_tokens": 2200,
        "temperature": 0.5,
        "supports_json": false,
        "supports_stream": true,
        "supports_long_context": true
      }
    },
    "openrouter": {
      "openai/gpt-4.1-mini": {
        "label": "OpenRouter → GPT-4.1 Mini",
        "enabled": true,
        "tasks": [
          "article_generate",
          "article_rewrite",
          "seo_generate",
          "title_generate",
          "summary_generate"
        ],
        "quality_score": 87,
        "speed_score": 78,
        "cost_score": 72,
        "max_tokens": 2400,
        "temperature": 0.6,
        "supports_json": true,
        "supports_stream": true,
        "supports_long_context": true
      },
      "anthropic/claude-sonnet": {
        "label": "OpenRouter → Claude Sonnet",
        "enabled": true,
        "tasks": [
          "article_generate",
          "article_rewrite"
        ],
        "quality_score": 93,
        "speed_score": 74,
        "cost_score": 60,
        "max_tokens": 2600,
        "temperature": 0.6,
        "supports_json": true,
        "supports_stream": true,
        "supports_long_context": true
      }
    },
    "gemini": {
      "gemini-1.5-pro": {
        "label": "Gemini 1.5 Pro",
        "enabled": true,
        "tasks": [
          "article_generate",
          "article_rewrite",
          "seo_generate",
          "summary_generate"
        ],
        "quality_score": 89,
        "speed_score": 76,
        "cost_score": 68,
        "max_tokens": 2400,
        "temperature": 0.5,
        "supports_json": true,
        "supports_stream": false,
        "supports_long_context": true
      }
    },
    "deepseek": {
      "deepseek-chat": {
        "label": "DeepSeek Chat",
        "enabled": true,
        "tasks": [
          "article_generate",
          "article_rewrite",
          "seo_generate",
          "summary_generate"
        ],
        "quality_score": 84,
        "speed_score": 80,
        "cost_score": 90,
        "max_tokens": 2200,
        "temperature": 0.5,
        "supports_json": false,
        "supports_stream": true,
        "supports_long_context": true
      }
    },
    "mistral": {
      "mistral-large-latest": {
        "label": "Mistral Large Latest",
        "enabled": true,
        "tasks": [
          "article_generate",
          "article_rewrite",
          "seo_generate"
        ],
        "quality_score": 85,
        "speed_score": 79,
        "cost_score": 76,
        "max_tokens": 2200,
        "temperature": 0.5,
        "supports_json": true,
        "supports_stream": true,
        "supports_long_context": true
      }
    },
    "ollama": {
      "llama3": {
        "label": "Ollama Llama3",
        "enabled": true,
        "tasks": [
          "summary_generate",
          "seo_generate"
        ],
        "quality_score": 68,
        "speed_score": 70,
        "cost_score": 100,
        "max_tokens": 1600,
        "temperature": 0.4,
        "supports_json": false,
        "supports_stream": true,
        "supports_long_context": false
      }
    }
  }
}
Models JSON için mimari not

Buradaki en kritik alanlar:

tasks

Router’ın bu modeli hangi işlerde kullanabileceğini buradan anlaması lazım.

quality_score

Uzun form article için güçlü modelleri seçmekte kullanılır.

speed_score

Title/summary/SEO gibi hızlı görevlerde önemli olur.

cost_score

Daha ekonomik kullanım için kullanılır.
Burada yüksek skor = daha avantajlı maliyet mantığı tercih edilebilir.

max_tokens

Task bazlı varsayılan token limiti için güçlü ipucudur.

supports_long_context

Uzun bağlamlı article’larda çok önemlidir.

Çok önemli karar

Ben burada model anahtarlarını provider içine nested öneriyorum:

"models": {
  "openai": {
    "gpt-4.1-mini": { ... }
  }
}

Bu yaklaşımın avantajı:

aynı model adı başka provider’da olsa bile çakışmaz

router provider + model eşleşmesini daha net yapar

debug daha okunaklı olur

storage/router.json
Resmi görev tanımı

Bu dosya, task bazlı routing politikasının resmi karar kaynağı olmalıdır.

Bu dosya şunu cevaplar:

“Hangi görev için hangi provider/model öncelikli?
Hangi fallback zinciri kullanılacak?
Quality/speed/cost profili ne olacak?”

Bu dosya:

provider config taşımaz

model metadata’nın yerine geçmez

yalnız routing politikası taşır

Router JSON için bağlayıcı kurallar
Kural 1

Task bazlı route tanımları burada tutulmalıdır.

Kural 2

Primary ve candidate/fallback ilişkisi açık olmalıdır.

Kural 3

Quality profile her task için net olmalıdır.

Kural 4

Router, bu dosyayı körlemesine değil, availability/capability ile birlikte yorumlamalıdır.

Kural 5

Disabled provider/model route’ta yazsa bile runtime’da elenebilmelidir.

Önerilen resmi şema
{
  "schema_version": "1.0.0",
  "tasks": {
    "article_generate": {
      "quality_profile": "quality",
      "candidates": [
        {
          "provider": "openrouter",
          "model": "anthropic/claude-sonnet"
        },
        {
          "provider": "openai",
          "model": "gpt-4.1-mini"
        },
        {
          "provider": "groq",
          "model": "llama-3.3-70b-versatile"
        },
        {
          "provider": "deepseek",
          "model": "deepseek-chat"
        }
      ]
    },
    "article_rewrite": {
      "quality_profile": "quality",
      "candidates": [
        {
          "provider": "openai",
          "model": "gpt-4.1-mini"
        },
        {
          "provider": "openrouter",
          "model": "anthropic/claude-sonnet"
        },
        {
          "provider": "gemini",
          "model": "gemini-1.5-pro"
        }
      ]
    },
    "seo_generate": {
      "quality_profile": "balanced",
      "candidates": [
        {
          "provider": "groq",
          "model": "llama-3.3-70b-versatile"
        },
        {
          "provider": "mistral",
          "model": "mistral-large-latest"
        },
        {
          "provider": "openai",
          "model": "gpt-4.1-mini"
        }
      ]
    },
    "title_generate": {
      "quality_profile": "speed",
      "candidates": [
        {
          "provider": "groq",
          "model": "llama-3.3-70b-versatile"
        },
        {
          "provider": "ollama",
          "model": "llama3"
        },
        {
          "provider": "openai",
          "model": "gpt-4.1-mini"
        }
      ]
    },
    "summary_generate": {
      "quality_profile": "speed",
      "candidates": [
        {
          "provider": "groq",
          "model": "llama-3.3-70b-versatile"
        },
        {
          "provider": "deepseek",
          "model": "deepseek-chat"
        },
        {
          "provider": "ollama",
          "model": "llama3"
        }
      ]
    }
  }
}
Router JSON için mimari not

Buradaki en kritik alan:

quality_profile

Router scoring davranışını yönlendirir.

Örnek:

article_generate → quality

seo_generate → balanced

title_generate → speed

candidates

Sıralı aday listesi verir.

Ama bu liste:

otomatik olarak kör seçilmez

availability + model capability + enabled durumu ile filtrelenir

Yani router.json:

emir değil

güçlü politika önerisi + aday listesi

olarak çalışmalıdır.

Üç JSON dosyası birlikte neyi çözüyor?
providers.json

Hangi sağlayıcı var ve nasıl bağlanılır?

models.json

Hangi model hangi iş için uygun?

router.json

Hangi görevte hangisi önce denensin?

Bu üçü birlikte:

registry

router

gateway

provider

zincirini gerçekten çalışabilir hale getirir.

Bu üç dosya için çok önemli ortak kural
Ortak kural 1

schema_version bulunmalı.

Bu ileride migrate etmek için kritik.

Ortak kural 2

Panel bu dosyaları bilinçsizce serbest metin gibi değil, şema destekli şekilde kaydetmeli.

Ortak kural 3

Selftest bu dosyaları parse edip kritik alanları doğrulamalı.

Ortak kural 4

Kod içindeki sabitler minimuma inmeli; davranış JSON’dan gelmeli.

Şimdi modül açısından geldiğimiz nokta

Artık pseudo-code + JSON sözleşmeleri birlikte düşünülünce elimizde şunlar var:

Kod omurgası

bootstrap

ajax

services

pipeline

context

outline

news katmanı

router

gateway

registry

providers

seo

quality

selftest

settings/storage helpers

JSON omurgası

settings.json

feature-map.json

providers.json

models.json

router.json

health.json

prompt-presets.json

Bu artık gerçekten modülün teknik anayasası seviyesine geldi.

AŞAMA 17

storage/prompt-presets.json resmi şeması

önerilen core/ai-article-prompt-engine.php sözleşmesi + pseudo-code

prompt build akışının final mimari notları

Bu aşama çok önemli çünkü bugünkü ana kök sorunlardan biri tam olarak şuydu:

context var
outline var
payload var
ama gerçek prompt/messages contract net değil

Yani burada artık V6’nın en hayati sinir hattını resmileştiriyoruz.

storage/prompt-presets.json
Resmi görev tanımı

Bu dosya, modülde kullanılan prompt preset’lerinin merkezi JSON kaynağı olmalıdır.

Amaç:

sistem prompt’larını kod içine gömmemek

task bazlı prompt presetleri yönetmek

article / rewrite / seo / title / summary gibi görevler için ayrı istem politikaları tanımlamak

panelden düzenlenebilir ama şemalı bir yapı sunmak

Bu dosya:

runtime response taşımaz

router kararı vermez

provider config taşımaz

yalnız prompt tasarım verisini saklar

Prompt presets için bağlayıcı kurallar
Kural 1

Her task için ayrı preset alanı olabilir.

Kural 2

Preset içinde system/user template mantığı açık olmalıdır.

Kural 3

Dil, ton, yapı, kaynak kullanımı gibi yönlendirmeler burada tanımlanabilir.

Kural 4

Prompt preset ile gerçek runtime context ayrı şeylerdir; preset şablondur, context canlı veridir.

Kural 5

Eksik preset durumunda kod güvenli default’lara dönebilmelidir.

Önerilen resmi şema
{
  "schema_version": "1.0.0",
  "presets": {
    "article_generate": {
      "system": "You are an expert editorial AI. Write a high-quality {lang} article with strong structure, factual grounding, and readable prose.",
      "user_template": "Topic: {topic}\nCategory: {category}\nLanguage: {lang}\nTone: {tone}\nLength: {length}\nTemplate: {template}\n\nEditorial Angle:\n{editorial_angle}\n\nSummary Block:\n{summary_block}\n\nFacts:\n{facts_block}\n\nEntities:\n{entities_block}\n\nKeywords:\n{keywords_block}\n\nOutline:\n{outline_block}\n\nInstructions:\n- Write a complete article\n- Keep the article in {lang}\n- Use a strong introduction and conclusion\n- Avoid shallow repetition\n- Base the content on the provided facts and sources",
      "options": {
        "include_sources": true,
        "include_summary": true
      }
    },
    "article_rewrite": {
      "system": "You are an expert editorial rewriting AI. Rewrite the given text in {lang} with better clarity, structure, and flow while preserving meaning.",
      "user_template": "Mode: {mode}\nTone: {tone}\nTarget Length: {target_length}\nPreserve HTML: {preserve_html}\nInstruction: {instruction}\n\nOriginal Text:\n{content}",
      "options": {
        "preserve_html_supported": true
      }
    },
    "seo_generate": {
      "system": "You are an SEO assistant. Produce concise and useful SEO outputs for the given article.",
      "user_template": "Title: {title}\nCategory: {category}\nLanguage: {lang}\n\nContent:\n{content}\n\nGenerate:\n- meta title\n- meta description\n- FAQ suggestions\n- keyword set"
    },
    "title_generate": {
      "system": "You are an editorial title specialist.",
      "user_template": "Topic: {topic}\nCategory: {category}\nLanguage: {lang}\n\nCreate a strong title."
    },
    "summary_generate": {
      "system": "You are a concise editorial summarizer.",
      "user_template": "Language: {lang}\n\nSummarize the following text:\n{content}"
    }
  }
}
Prompt presets için mimari not

Bu dosyanın asıl önemi şurada:

Eskiden prompt logic kodun içine dağılırsa:

article-service başka yazar

rewrite-service başka yazar

pipeline başka yazar

panel preview başka mantık bekler

Sonuç:

prompt contract kırılır

kalite tutarsız olur

debug zorlaşır

Ama preset sistemi ile:

istem tasarımı merkezi olur

kod ile prompt tasarımı ayrılır

sonraki geliştirmelerde daha güvenli iterasyon yapılır

Önerilen yeni dosya:
core/ai-article-prompt-engine.php
Resmi görev tanımı

Bu dosya modülde prompt/messages üretiminin merkezi motoru olmalıdır.

Bu dosyanın görevi:

task bazlı preset seçmek

preset template içindeki placeholder’ları doldurmak

context/outline/fact/entity/keyword bloklarını birleştirmek

provider’a gönderilecek gerçek messages dizisini üretmek

article/rewrite/seo/title/summary için ortak prompt contract sağlamak

Bu dosya:

provider seçmez

gateway çağırmaz

article parse etmez

UI bilmez

Yani bu katman:

veri + preset → gerçek LLM input

işini yapar.

Prompt engine için bağlayıcı kurallar
Kural 1

Prompt build işlemi merkezi olmalıdır.

Kural 2

Task bazlı preset seçimi bu katmanda yapılmalıdır.

Kural 3

Placeholder doldurma güvenli ve deterministik olmalıdır.

Kural 4

Prompt engine, payload değil gerçek messages contract’ı üretmelidir.

Kural 5

Context, outline, facts, entities, keywords ayrı ayrı prompt’a kontrollü şekilde işlenmelidir.

Kural 6

Prompt engine provider kararını bilmez; yalnız içerik contract üretir.

Prompt engine girdi sözleşmesi

Article generate için mantıksal giriş:

[
  'task' => 'article_generate',
  'topic' => '',
  'category' => '',
  'lang' => 'tr',
  'tone' => 'analytical',
  'length' => 'long',
  'template' => 'news_analysis',
  'context' => [
    'editorial_angle' => '',
    'summary_block' => '',
    'fact_pack' => [
      'facts' => [],
      'entities' => [],
      'keywords' => [],
      'signals' => [],
    ],
    'sources' => [],
  ],
  'outline' => [
    'template' => '',
    'title_hint' => '',
    'sections' => [],
  ],
]

Rewrite için mantıksal giriş:

[
  'task' => 'article_rewrite',
  'content' => '',
  'instruction' => '',
  'lang' => 'tr',
  'tone' => 'analytical',
  'mode' => 'polish',
  'target_length' => 'long',
  'preserve_html' => false,
]
Prompt engine çıktı sözleşmesi
[
  'ok' => true,
  'messages' => [
    ['role' => 'system', 'content' => '...'],
    ['role' => 'user', 'content' => '...']
  ],
  'meta' => [
    'task' => 'article_generate',
    'preset' => 'article_generate',
  ],
  'error' => null,
]
Prompt engine için önerilen ana fonksiyonlar
1. aig_prompt_engine_build(array $input): array

Ana giriş.

2. aig_prompt_engine_load_presets(): array

prompt-presets.json okur.

3. aig_prompt_engine_get_preset(string $task): array

Task preset’ini seçer.

4. aig_prompt_engine_build_article_messages(array $input, array $preset): array

Article generate mesajları üretir.

5. aig_prompt_engine_build_rewrite_messages(array $input, array $preset): array

Rewrite mesajları üretir.

6. aig_prompt_engine_build_seo_messages(array $input, array $preset): array

SEO mesajları üretir.

7. aig_prompt_engine_render_template(string $template, array $vars): string

Placeholder doldurur.

8. aig_prompt_engine_format_outline(array $outline): string

Outline’ı text bloğa çevirir.

9. aig_prompt_engine_format_list_block(array $items, string $prefix = '- '): string

Fact/entity/keyword blokları üretir.

ai-article-prompt-engine.php pseudo-code
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main prompt engine entry.
 */
function aig_prompt_engine_build(array $input): array
{
    $task = trim((string) ($input['task'] ?? ''));

    if ($task === '') {
        return [
            'ok' => false,
            'messages' => [],
            'meta' => ['stage' => 'normalize'],
            'error' => [
                'code' => 'missing_prompt_task',
                'message' => 'Prompt engine task is required.',
            ],
        ];
    }

    $preset = aig_prompt_engine_get_preset($task);
    if (empty($preset)) {
        return [
            'ok' => false,
            'messages' => [],
            'meta' => ['stage' => 'preset'],
            'error' => [
                'code' => 'missing_prompt_preset',
                'message' => 'No prompt preset found for task: ' . $task,
            ],
        ];
    }

    switch ($task) {
        case 'article_generate':
            return aig_prompt_engine_build_article_messages($input, $preset);

        case 'article_rewrite':
            return aig_prompt_engine_build_rewrite_messages($input, $preset);

        case 'seo_generate':
            return aig_prompt_engine_build_seo_messages($input, $preset);

        case 'title_generate':
        case 'summary_generate':
            return aig_prompt_engine_build_simple_messages($input, $preset);

        default:
            return [
                'ok' => false,
                'messages' => [],
                'meta' => ['stage' => 'dispatch'],
                'error' => [
                    'code' => 'unsupported_prompt_task',
                    'message' => 'Unsupported prompt engine task: ' . $task,
                ],
            ];
    }
}

/**
 * Load prompt presets JSON.
 */
function aig_prompt_engine_load_presets(): array
{
    if (function_exists('aig_storage_read_json')) {
        $json = aig_storage_read_json('prompt-presets.json');
        if (is_array($json)) {
            return $json;
        }
    }

    return [
        'schema_version' => '1.0.0',
        'presets' => [],
    ];
}

/**
 * Get one preset by task key.
 */
function aig_prompt_engine_get_preset(string $task): array
{
    $all = aig_prompt_engine_load_presets();
    return is_array($all['presets'][$task] ?? null) ? $all['presets'][$task] : [];
}

/**
 * Build messages for article generation.
 */
function aig_prompt_engine_build_article_messages(array $input, array $preset): array
{
    $context = is_array($input['context'] ?? null) ? $input['context'] : [];
    $factPack = is_array($context['fact_pack'] ?? null) ? $context['fact_pack'] : [];
    $outline = is_array($input['outline'] ?? null) ? $input['outline'] : [];

    $vars = [
        'topic' => (string) ($input['topic'] ?? ''),
        'category' => (string) ($input['category'] ?? 'general'),
        'lang' => (string) ($input['lang'] ?? 'tr'),
        'tone' => (string) ($input['tone'] ?? 'analytical'),
        'length' => (string) ($input['length'] ?? 'long'),
        'template' => (string) ($input['template'] ?? 'news_analysis'),
        'editorial_angle' => (string) ($context['editorial_angle'] ?? ''),
        'summary_block' => (string) ($context['summary_block'] ?? ''),
        'facts_block' => aig_prompt_engine_format_list_block($factPack['facts'] ?? []),
        'entities_block' => aig_prompt_engine_format_list_block($factPack['entities'] ?? []),
        'keywords_block' => aig_prompt_engine_format_list_block($factPack['keywords'] ?? []),
        'signals_block' => aig_prompt_engine_format_list_block($factPack['signals'] ?? []),
        'outline_block' => aig_prompt_engine_format_outline($outline),
    ];

    $system = aig_prompt_engine_render_template((string) ($preset['system'] ?? ''), $vars);
    $user   = aig_prompt_engine_render_template((string) ($preset['user_template'] ?? ''), $vars);

    return [
        'ok' => true,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ],
        'meta' => [
            'task' => 'article_generate',
            'preset' => 'article_generate',
        ],
        'error' => null,
    ];
}

/**
 * Build messages for rewrite.
 */
function aig_prompt_engine_build_rewrite_messages(array $input, array $preset): array
{
    $vars = [
        'content' => (string) ($input['content'] ?? ''),
        'instruction' => (string) ($input['instruction'] ?? 'Improve clarity and readability.'),
        'lang' => (string) ($input['lang'] ?? 'tr'),
        'tone' => (string) ($input['tone'] ?? 'analytical'),
        'mode' => (string) ($input['mode'] ?? 'polish'),
        'target_length' => (string) ($input['target_length'] ?? 'long'),
        'preserve_html' => !empty($input['preserve_html']) ? 'yes' : 'no',
    ];

    $system = aig_prompt_engine_render_template((string) ($preset['system'] ?? ''), $vars);
    $user   = aig_prompt_engine_render_template((string) ($preset['user_template'] ?? ''), $vars);

    return [
        'ok' => true,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ],
        'meta' => [
            'task' => 'article_rewrite',
            'preset' => 'article_rewrite',
        ],
        'error' => null,
    ];
}

/**
 * Build messages for SEO.
 */
function aig_prompt_engine_build_seo_messages(array $input, array $preset): array
{
    $vars = [
        'title' => (string) ($input['title'] ?? ''),
        'content' => (string) ($input['content'] ?? ''),
        'category' => (string) ($input['category'] ?? 'general'),
        'lang' => (string) ($input['lang'] ?? 'tr'),
        'topic' => (string) ($input['topic'] ?? ''),
    ];

    $system = aig_prompt_engine_render_template((string) ($preset['system'] ?? ''), $vars);
    $user   = aig_prompt_engine_render_template((string) ($preset['user_template'] ?? ''), $vars);

    return [
        'ok' => true,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ],
        'meta' => [
            'task' => 'seo_generate',
            'preset' => 'seo_generate',
        ],
        'error' => null,
    ];
}

/**
 * Build simple task messages (title/summary).
 */
function aig_prompt_engine_build_simple_messages(array $input, array $preset): array
{
    $vars = [
        'topic' => (string) ($input['topic'] ?? ''),
        'category' => (string) ($input['category'] ?? 'general'),
        'lang' => (string) ($input['lang'] ?? 'tr'),
        'content' => (string) ($input['content'] ?? ''),
    ];

    $system = aig_prompt_engine_render_template((string) ($preset['system'] ?? ''), $vars);
    $user   = aig_prompt_engine_render_template((string) ($preset['user_template'] ?? ''), $vars);

    return [
        'ok' => true,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ],
        'meta' => [
            'task' => (string) ($input['task'] ?? ''),
            'preset' => (string) ($input['task'] ?? ''),
        ],
        'error' => null,
    ];
}

/**
 * Render template placeholders like {topic}, {lang}.
 */
function aig_prompt_engine_render_template(string $template, array $vars): string
{
    $rendered = $template;

    foreach ($vars as $key => $value) {
        if (!is_scalar($value)) {
            continue;
        }
        $rendered = str_replace('{' . $key . '}', (string) $value, $rendered);
    }

    return $rendered;
}

/**
 * Format outline into readable block text.
 */
function aig_prompt_engine_format_outline(array $outline): string
{
    $sections = is_array($outline['sections'] ?? null) ? $outline['sections'] : [];
    $lines = [];

    foreach ($sections as $index => $section) {
        $heading = trim((string) ($section['heading'] ?? ''));
        $goal = trim((string) ($section['goal'] ?? ''));

        if ($heading === '' && $goal === '') {
            continue;
        }

        $n = $index + 1;
        $lines[] = "{$n}. {$heading}" . ($goal !== '' ? " — {$goal}" : '');
    }

    return implode("\n", $lines);
}

/**
 * Format list items as bullet block.
 */
function aig_prompt_engine_format_list_block(array $items, string $prefix = '- '): string
{
    $lines = [];

    foreach ($items as $item) {
        $text = trim((string) $item);
        if ($text === '') {
            continue;
        }
        $lines[] = $prefix . $text;
    }

    return implode("\n", $lines);
}
Prompt engine’in article pipeline ile doğru ilişkisi

Artık ai-article-pipeline.php içinde şu eski tarz yaklaşım olmamalı:

context üret

outline üret

elle prompt string yaz

başka yerde başka prompt yaz

Onun yerine resmi akış şöyle olmalı:

Doğru akış

context build

outline build

aig_prompt_engine_build([...])

gerçek messages al

router

gateway

provider

Yani article pipeline’ın prompt kısmı artık tek yerde çözülmeli.

Prompt engine’in rewrite service ile doğru ilişkisi

rewrite-service.php içinde de doğrudan user/system string’lerini elle yazmak yerine şu tercih edilmeli:

normalize input

aig_prompt_engine_build([... task => article_rewrite ...])

route

gateway

cleanup pipeline

Bu şekilde rewrite da article ile aynı prompt mimarisine oturur.

Prompt engine’in SEO service ile doğru ilişkisi

Benzer şekilde:

SEO service input’u normalize eder

prompt engine’den seo_generate preset’iyle messages alır

route + gateway ile isterse LLM destekli SEO üretir

deterministic fallback builder’lar ile desteklenir

Bu da SEO tarafını daha düzenli hale getirir.

Çok önemli mimari sonuç

Bu katmanla birlikte modülde şu kritik kırık kapanmaya başlıyor:

Eski sorun

payload var

ama gerçek prompt/messages contract net değil

Yeni resmi yaklaşım

preset var

context var

outline var

placeholder render var

final messages var

Yani artık LLM çağrısına giden veri:

açık

deterministik

task-aware

merkezi

hale geliyor.

Bu, V6’nın en kritik eksiklerinden birinin kapanması demek.

Prompt engine için final mimari notlar
Not 1

Prompt engine provider bağımsız olmalı
Yani “OpenAI için şöyle, Gemini için böyle prompt” mantığı burada yaşamamalı. O fark gerekiyorsa gateway/provider seviyesinde request formatı çözülür.

Not 2

Prompt presets JSON ile kod ayrılmalı
Böylece panel veya docs üzerinden geliştirme kolaylaşır.

Not 3

Prompt engine içerik üretmez
Sadece LLM’e gönderilecek doğru contract’ı hazırlar.

Not 4

Prompt engine article kalite omurgasının parçasıdır
Çünkü zayıf prompt = zayıf output.

Bu aşamadaki net kazanım

Şimdi artık pseudo-code düzeyinde modülün en kritik missing parçası da resmileşti:

prompt-presets.json

ai-article-prompt-engine.php

Bu ikisiyle birlikte:

article pipeline

rewrite service

SEO service

aynı istem mimarisine bağlanabilir.

Şu anda kağıt üstünde tamamlanan büyük teknik omurga
Konfigürasyon

settings

feature map

providers

models

router

prompt presets

Core execution

bootstrap

ajax

services

pipeline

prompt engine

router

gateway

registry

providers

Data preparation

sources

collector

normalizer

cache

fact pack

context

outline

Post-processing

rewrite pipeline

seo pipeline

quality

selftest

Bu artık gerçek anlamda dünyayı gören çoklu sağlayıcılı AI article engine mimarisi seviyesine yaklaştı.

AŞAMA 18
İlk Gerçek Tam Kod Üretim Paketi Planı

Bu aşamada şu 6 dosyayı tek paket olarak ele alıyoruz:

ai-article-generator.php

ajax-handler.php

core/services/article-service.php

core/ai-article-pipeline.php

core/services/rewrite-service.php

core/services/selftest-service.php

Bunlar neden birlikte ele alınmalı?

Çünkü bunlar:

bootstrap

giriş kapıları

article contract

rewrite contract

health contract

aynı omurgayı paylaşır.

Bu pakette yalnız tek dosya düzeltmek yine yarım kalır.
Bu yüzden bunlar tek paket olmak zorunda.

PAKET 1 — STRATEJİK AMAÇ

Bu ilk paketin amacı şudur:

1. Modülün yükleme sırasını resmileştirmek

Bunu ai-article-generator.php çözer.

2. AJAX katmanını inceltmek

Bunu ajax-handler.php çözer.

3. Makale üretiminin tek resmi giriş kapısını oluşturmak

Bunu article-service.php çözer.

4. V6 article orchestration’ı gerçek hale getirmek

Bunu ai-article-pipeline.php çözer.

5. Rewrite’ı gerçek rewrite use-case yapmak

Bunu rewrite-service.php çözer.

6. Sağlık raporunu güvenilir hale getirmek

Bunu selftest-service.php çözer.

PAKET 1 — HEDEF SEMPTOMLAR

Bu paketin çözmesi gereken görünür semptomlar şunlar:

kısa article

çıplak içerik

İngilizce kalıntıların yoğunluğu

rewrite’ın etkisiz görünmesi

bazı katmanların mevcut olduğu halde missing davranması

selftest’in güven vermemesi

AJAX tarafında shape karmaşası

loader sırası kaynaklı class/function eksiklikleri

Bu semptomların hepsi bir anda yüzde yüz bitmeyebilir.
Ama bu paket bittiğinde sistem artık:

tek omurgada çalışan

debug edilebilir

sıradaki paketlere hazır
hale gelmelidir.

PAKET 1 — DOSYA DOSYA ROLLER
1) ai-article-generator.php
Paket içindeki rolü

Bu dosya resmi module loader olacak.

Bu pakette yapılacak kesin işler

constant/path tanımları netleşecek

foundation → providers → routing → news → pipeline → seo → services → integrations → ajax → panel sırası kurulacak

eksik include zinciri temizlenecek

boot log’u standart hale gelecek

“yarım bağlı registry” sorunu kapanacak

Bu pakette yapmaması gereken

generate logic taşımak

settings panel logic taşımak

runtime orchestration yapmak

Bu dosya bittikten sonra beklenen sonuç

include sırası deterministik olacak

missing_* türü runtime sürprizleri azalacak

diğer çekirdek dosyalar güvenle varsayım yapabilecek

2) ajax-handler.php
Paket içindeki rolü

Bu dosya ince action katmanı olacak.

Bu pakette yapılacak kesin işler

endpoint’ler yeniden netlenecek:

article generate

rewrite

seo generate

selftest

nonce/capability doğrulama sade ve merkezi olacak

her endpoint yalnız normalize input + service çağrısı yapacak

response shape standardize edilecek

Bu pakette kaldırılması gereken kokular

service yoksa legacy’ye geç, oradan fallback’e geç mantığı

AJAX içinde prompt yazımı

AJAX içinde route kararı

AJAX içinde content/html/data karma response yönetimi

Bu dosya bittikten sonra beklenen sonuç

panel ile core arasındaki kapı sadeleşecek

UI aynı shape’i bekleyebilecek

debug etmek çok daha kolay olacak

3) core/services/article-service.php
Paket içindeki rolü

Bu dosya makale üretiminin tek use-case girişi olacak.

Bu pakette yapılacak kesin işler

input normalize

validate

settings + feature-map resolve

pipeline çağrısı

final response standardı

service-level timing/meta ekleme

Bu pakette kritik karar

AJAX artık article pipeline’ı doğrudan değil, yalnızca article-service üzerinden çağıracak.

Bu dosya bittikten sonra beklenen sonuç

article generate için tek resmi kapı olacak

future panel/API/queue girişleri aynı service’i kullanabilecek

patch yerine sağlam contract oluşacak

4) core/ai-article-pipeline.php
Paket içindeki rolü

Bu dosya ana orchestration beyni olacak.

Bu pakette yapılacak kesin işler

context build

outline build

prompt engine’den gerçek messages alma

router çağrısı

gateway üzerinden generate

raw output parse

isteğe bağlı rewrite

isteğe bağlı seo

quality/meta ile final article object

En kritik onarım

Bu paketin en önemli teknik görevi budur:

payload üretmek değil,
gerçek messages contract’ı ile LLM’e gitmek

Yani mevcut sistemdeki en büyük kopukluk burada kapatılacak.

Bu dosya bittikten sonra beklenen sonuç

LLM’e eksiksiz ve açık veri gidecek

fallback gereksiz erken devreye girmeyecek

article output daha anlamlı hale gelecek

çıplak İngilizce kalıntı sorunu azalmanın yoluna girecek

5) core/services/rewrite-service.php
Paket içindeki rolü

Bu dosya gerçek rewrite use-case service’i olacak.

Bu pakette yapılacak kesin işler

rewrite input normalize

rewrite task route seçimi

prompt engine ile rewrite messages üretimi

gateway/LLM çağrısı

rewrite-pipeline cleanup

final rewrite response contract

Bu pakette en kritik karar

Cleanup ile rewrite ayrılacak:

rewrite-service = gerçek yeniden yazım

rewrite-pipeline = temizlik/postprocess

Bu dosya bittikten sonra beklenen sonuç

kullanıcı rewrite deyince gerçekten anlamlı fark görecek

no-op hissi azalacak

rewrite ayrı bir use-case olarak netleşecek

6) core/services/selftest-service.php
Paket içindeki rolü

Bu dosya gerçek health raporu üretecek.

Bu pakette yapılacak kesin işler

static checks

contract checks

quick runtime checks

health.json yazımı

false negative oluşturan yanlış isim/sabit beklentilerini bırakma

Bu dosya bittikten sonra beklenen sonuç

sistem sağlığı daha dürüst görülecek

panelde güvenilir test sonucu gösterilebilecek

sonraki paketlerde debug hızlanacak

PAKET 1 — DOSYALAR ARASI ÇAĞRI ZİNCİRİ

Bu çok önemli. İlk tam kod paketinde resmi zincir şu olmalı:

Article generate zinciri

panel/editor.js
→ ajax-handler.php
→ aig_ajax_article_generate()
→ aig_article_service_generate()
→ aig_article_pipeline_run()
→ aig_article_context_build()
→ aig_article_outline_build()
→ aig_prompt_engine_build(task=article_generate)
→ aig_router_select()
→ aig_gateway_generate()
→ provider
→ parse
→ optional rewrite
→ optional seo
→ final response

Rewrite zinciri

panel/editor.js
→ ajax-handler.php
→ aig_ajax_article_rewrite()
→ aig_rewrite_service_run()
→ aig_prompt_engine_build(task=article_rewrite)
→ aig_router_select()
→ aig_gateway_generate()
→ provider
→ aig_rewrite_pipeline_cleanup()
→ final rewrite response

Selftest zinciri

panel/editor.js
→ ajax-handler.php
→ aig_ajax_article_selftest()
→ aig_selftest_service_run()
→ static + contract + runtime checks
→ health.json

PAKET 1 — YAZIM SIRASI

Bu 6 dosya aynı pakette ama yazım sırası da önemli.

Benim kesin teknik sıram şu:

Sıra 1
ai-article-generator.php

Neden?
Çünkü loader sağlam değilse diğer dosyalar güvenle bağlanamaz.

Sıra 2
core/services/article-service.php

Neden?
Makale üretiminin resmi giriş kapısı önce netleşmeli.

Sıra 3
core/ai-article-pipeline.php

Neden?
Service’in çağıracağı gerçek omurga burada.

Sıra 4
core/services/rewrite-service.php

Neden?
Pipeline’ın optional rewrite ayağı buraya bağlanacak.

Sıra 5
core/services/selftest-service.php

Neden?
Yeni omurgayı test edecek sağlık katmanı sonra gelmeli.

Sıra 6
ajax-handler.php

Neden?
En son action layer yeni çekirdeğe bağlanmalı.

Bu sıra çok önemli.
ajax-handler.php en başta yazılırsa eski çekirdeğe göre şekillenmeye devam eder.

PAKET 1 — DOSYA BAŞINA TAM KOD ÜRETİM KRİTERLERİ

Bu da önemli. Her dosya yazılırken şu kalite standardı uygulanmalı:

ai-article-generator.php

tek yükleme sırası

tekrar eden require karmaşası yok

guard’lar net

constants tek yerde

okunabilir blok yapısı

article-service.php

tek giriş fonksiyonu

temiz helper ayrımı

standard error response

standard success response

ai-article-pipeline.php

her stage ayrı helper

açık stage hata raporu

prompt engine kullanımı

parse aşaması net

rewrite-service.php

article logic ile karışmayan rewrite akışı

prompt engine kullanımı

cleanup ayrımı

selftest-service.php

check grupları net

false negative üreten eski isimler yok

health.json standardı var

ajax-handler.php

her endpoint kısa

kopyala-yapıştır business logic yok

wp_send_json standardı net

PAKET 1 — KOD ÜRETİMİNDE DOKUNULMAYACAK ALANLAR

İlk pakette her şeye aynı anda dokunmak hata olur.
Bu yüzden şu alanlar şimdilik adapter/support olarak kalmalı:

provider dosyalarının iç detayları
(ana contract zaten yazıldı, sonra tam kodlanır)

news kaynak JSON’unun zenginleştirilmesi

panel UI görsel makyajı

advanced SEO builders

advanced quality heuristics

integrations hook detayları

queue/post/media yardımcıları

Bu alanlara şimdi dalarsak çekirdek yine yarım kalır.

PAKET 1 — BAŞARI KRİTERLERİ

Bu paket bittiğinde minimum şu sonuçları görmek isteriz:

1

Makale generate akışı tek service/pipeline omurgasında çalışmalı

2

AJAX response shape dağınık olmamalı

3

Rewrite gerçek rewrite gibi davranmalı

4

Selftest yanlış isim/sabit yüzünden saçma fail vermemeli

5

Loader sırası kaynaklı “missing” sürprizleri ciddi azalmalı

6

Prompt engine ile gerçek messages üretimi devreye girmeli

7

Article output fallback ağırlıklı hissettirmemeli

Bu paket sonunda sistem mükemmel olmak zorunda değil.
Ama artık tek omurga üzerinde stabil ilerleyebilir olmak zorunda.

PAKET 1 — RİSKLER

Dürüstçe söyleyeyim, ilk gerçek kod paketinde dikkat edilmesi gereken riskler şunlar:

Risk 1

Eski AJAX action isimleri ile yeni service isimleri arasında kopma olabilir

Risk 2

Eski UI bazı legacy response alanlarını bekliyor olabilir

Risk 3

Settings/feature-map helper dosyaları loader’da doğru sırada yüklenmezse service kırılır

Risk 4

Prompt engine entegre edilmeden pipeline yazılırsa aynı eski hata tekrar eder

Risk 5

Rewrite service gerçek rewrite yerine yine cleanup’a kayabilir

Bu yüzden tam paket yazılırken hepsi birlikte düşünülmeli.

PAKET 1 — FINAL KARAR

Bu paketin resmi hedef cümlesi şudur:

“V6 service/pipeline omurgasını resmileştir,
AJAX ve loader’ı buna bağla,
rewrite ve selftest’i aynı contract’a oturt.”

Bu başarıldığında:

modül parçalı olmaktan çıkar

sonraki paketler güvenle yazılır

artık gerçek tam dosya kodlarına geçmek mantıklı hale gelir


AŞAMA 19
Final fonksiyon matrisi + dosya iç blok planı

Bu aşamada şu 6 dosya için şunları netleştiriyorum:

dosya içindeki ana blok sırası

dosya başına final fonksiyon listesi

fonksiyonlar arası çağrı ilişkisi

dosya yazılırken hangi sıra izlenmeli

Ele aldığımız 6 dosya:

ai-article-generator.php

ajax-handler.php

core/services/article-service.php

core/ai-article-pipeline.php

core/services/rewrite-service.php

core/services/selftest-service.php

Bu belge artık doğrudan tam kod üretimine zemin hazırlar.

1) ai-article-generator.php
Dosya iç blok planı

Bu dosya aşağıdaki blok sırasıyla yazılmalı:

Blok 1 — Guard

ABSPATH kontrolü

gerekiyorsa duplicate load guard

Blok 2 — Module constants

version

build

dir/path/url/storage/log/ui/integration constant’ları

Blok 3 — Foundation require

log

storage helper

settings

feature map

usage

metrics

Blok 4 — Provider require

interface

base provider

concrete providers

registry

Blok 5 — Routing require

router

gateway

llm

core adapter

Blok 6 — News/context require

helpers

cache

sources

normalizer

collector

fact-pack

context

outline

templates

prompt engine

Blok 7 — Pipelines require

rewrite-pipeline

seo-pipeline

article-pipeline

Blok 8 — SEO require

meta-builder

schema-builder

faq-builder

seo-engine

Blok 9 — Services require

media-service

seo-service

rewrite-service

article-service

selftest-service

Blok 10 — Support/legacy require

media

post

quality

internal-links

engines

bridge

selftest legacy

devnotes

queue

Blok 11 — Integrations/UI/AJAX

integrations

ajax-handler

panel

ui/settings

Blok 12 — Final boot

boot log

optional module init hook

version/build exposure

Final fonksiyon matrisi

Bu dosyada fonksiyon sayısı minimum tutulmalı. En fazla:

1. aig_module_boot_log(): void

Boot başarılı logu.

2. aig_module_require(string $path): void

İsteğe bağlı küçük helper; require düzeni için.

3. aig_module_init(): void

İsteğe bağlı final init wrapper.

Çağrı ilişkisi

ai-article-generator.php
→ dosyaları sırayla yükler
→ en sonda aig_module_init() veya boot log

Bu dosya başka bir business logic çağırmamalı.

Yazım notu

Bu dosyada en kritik şey:

fonksiyon çokluğu değil

yükleme sırası

2) ajax-handler.php
Dosya iç blok planı
Blok 1 — Guard

ABSPATH kontrolü

Blok 2 — Request helper’ları

nonce verify helper

capability verify helper

boolean/string normalize helper’ları

Blok 3 — Article generate endpoint

input normalize

article-service çağrısı

wp_send_json

Blok 4 — Rewrite endpoint

input normalize

rewrite-service çağrısı

wp_send_json

Blok 5 — SEO endpoint

input normalize

seo-service çağrısı

wp_send_json

Blok 6 — Selftest endpoint

input normalize

selftest-service çağrısı

wp_send_json

Blok 7 — Action registration

add_action('wp_ajax_...')

Final fonksiyon matrisi
Helpers

aig_ajax_verify_nonce(string $nonceField, string $action): bool

aig_ajax_require_capability(string $capability): bool

aig_ajax_bool($value): bool

aig_ajax_str($value, string $default = ''): string

Input builders

aig_ajax_build_article_input(): array

aig_ajax_build_rewrite_input(): array

aig_ajax_build_seo_input(): array

aig_ajax_build_selftest_input(): array

Endpoints

aig_ajax_article_generate(): void

aig_ajax_article_rewrite(): void

aig_ajax_article_generate_seo(): void

aig_ajax_article_selftest(): void

Çağrı ilişkisi
Article

aig_ajax_article_generate()
→ aig_ajax_verify_nonce()
→ aig_ajax_require_capability()
→ aig_ajax_build_article_input()
→ aig_article_service_generate()
→ wp_send_json()

Rewrite

aig_ajax_article_rewrite()
→ aig_rewrite_service_run()

Selftest

aig_ajax_article_selftest()
→ aig_selftest_service_run()

Yazım notu

Bu dosyada en kritik şey:

endpoint kısa kalmalı

helper’lar tekrar eden request kodunu azaltmalı

endpoint içinde prompt/gateway/router mantığı olmamalı

3) core/services/article-service.php
Dosya iç blok planı
Blok 1 — Guard

ABSPATH

Blok 2 — Input normalize

string temizleme

defaults

Blok 3 — Input validate

topic

length

temel alanlar

Blok 4 — Options resolve

settings

feature map

version/build

request overrides

Blok 5 — Main generate use-case

normalize

validate

feature gate

pipeline call

finalize

Blok 6 — Response helpers

error response

success response

Final fonksiyon matrisi

aig_article_service_generate(array $input): array

aig_article_service_normalize_input(array $input): array

aig_article_service_validate_input(array $input): array

aig_article_service_resolve_options(array $input): array

aig_article_service_finalize_response(array $pipelineResult, array $resolvedOptions): array

aig_article_service_build_error(string $code, string $message, array $meta = []): array

Opsiyonel:
7. aig_article_service_apply_feature_gates(array $resolved): array

Çağrı ilişkisi

aig_article_service_generate()
→ normalize
→ validate
→ resolve options
→ feature gate
→ aig_article_pipeline_run()
→ finalize response

Yazım notu

Bu dosyanın ana özelliği:

çok temiz

çok deterministik

çok dar görevli olması

4) core/ai-article-pipeline.php
Dosya iç blok planı
Blok 1 — Guard
Blok 2 — Main pipeline entry
Blok 3 — Context stage
Blok 4 — Outline stage
Blok 5 — Prompt engine stage
Blok 6 — Router stage
Blok 7 — Gateway generate stage
Blok 8 — Parse stage
Blok 9 — Optional rewrite stage
Blok 10 — Optional SEO stage
Blok 11 — Optional quality stage
Blok 12 — Finalize stage
Blok 13 — Error helpers
Final fonksiyon matrisi

aig_article_pipeline_run(array $input): array

Stages

aig_article_pipeline_build_context(array $input): array

aig_article_pipeline_build_outline(array $context, array $input): array

aig_article_pipeline_build_messages(array $context, array $outline, array $input): array

aig_article_pipeline_route(array $input, array $messages): array

aig_article_pipeline_generate(array $route, array $messages, array $input): array

aig_article_pipeline_parse_output(array $generateResult, array $context, array $outline, array $input): array

aig_article_pipeline_apply_rewrite(array $article, array $input): array

aig_article_pipeline_apply_seo(array $article, array $input): array

aig_article_pipeline_apply_quality(array $article, array $input): array

aig_article_pipeline_finalize(array $article, array $seo, array $meta): array

Helpers

aig_article_pipeline_build_stage_error(string $stage, string $code, string $message, array $meta = []): array

Çağrı ilişkisi

aig_article_pipeline_run()
→ build_context()
→ build_outline()
→ build_messages()
→ route()
→ generate()
→ parse_output()
→ optional apply_rewrite()
→ optional apply_seo()
→ optional apply_quality()
→ finalize()

Yazım notu

Bu dosyada en kritik şey:

stage’lerin açık olması

her stage’de anlaşılır hata dönmesi

prompt engine kullanılmadan generate aşamasına gidilmemesi

5) core/services/rewrite-service.php
Dosya iç blok planı
Blok 1 — Guard
Blok 2 — Normalize input
Blok 3 — Validate input
Blok 4 — Build rewrite messages
Blok 5 — Route rewrite task
Blok 6 — Gateway generate
Blok 7 — Cleanup/postprocess
Blok 8 — Final response
Blok 9 — Error helper
Final fonksiyon matrisi

aig_rewrite_service_run(array $input): array

aig_rewrite_service_normalize_input(array $input): array

aig_rewrite_service_validate_input(array $input): array

aig_rewrite_service_build_messages(array $input): array

aig_rewrite_service_finalize(string $rewritten, array $meta, array $input): array

aig_rewrite_service_build_error(string $code, string $message, array $meta = []): array

Opsiyonel:
7. aig_rewrite_service_route(array $input): array
8. aig_rewrite_service_generate(array $route, array $messages): array

Çağrı ilişkisi

aig_rewrite_service_run()
→ normalize
→ validate
→ prompt engine veya build messages
→ aig_router_select(task=article_rewrite)
→ aig_gateway_generate()
→ aig_rewrite_pipeline_cleanup()
→ finalize

Yazım notu

Bu dosyada kritik şey:

rewrite ile cleanup’ın karışmaması

article pipeline mantığının buraya taşmaması

6) core/services/selftest-service.php
Dosya iç blok planı
Blok 1 — Guard
Blok 2 — Normalize input
Blok 3 — Static checks
Blok 4 — Contract checks
Blok 5 — Runtime checks
Blok 6 — Summarize
Blok 7 — Write health.json
Blok 8 — Final result
Blok 9 — Small check helpers
Final fonksiyon matrisi

aig_selftest_service_run(array $input): array

aig_selftest_service_normalize_input(array $input): array

aig_selftest_service_collect_static_checks(): array

aig_selftest_service_collect_contract_checks(): array

aig_selftest_service_collect_runtime_checks(string $mode): array

aig_selftest_service_summarize(array $checks): array

aig_selftest_service_write_health(array $result): void

Check helpers

aig_selftest_check_file_exists(string $name, string $path): array

aig_selftest_check_writable_dir(string $name, string $path): array

aig_selftest_check_function_exists(string $name, string $function): array

Çağrı ilişkisi

aig_selftest_service_run()
→ normalize mode
→ static checks
→ contract checks
→ runtime checks
→ summarize
→ write health
→ return result

Yazım notu

Bu dosyada en kritik şey:

yanlış isim/sabit kontrolünden kaçınmak

runtime check’leri kontrollü yapmak

sonuç shape’ini sabit tutmak

DOSYALAR ARASI ÇAPRAZ BAĞLANTILAR

Şimdi 6 dosya arasındaki doğrudan ilişkiyi tek yerde net veriyorum.

Bootstrap ilişkisi

ai-article-generator.php

diğer 5 dosyayı doğru sırada yükler

AJAX ilişkisi

ajax-handler.php

article-service.php

rewrite-service.php

selftest-service.php
çağırır

Article service ilişkisi

article-service.php

ai-article-pipeline.php
çağırır

Pipeline ilişkisi

ai-article-pipeline.php

context

outline

prompt engine

router

gateway

rewrite-service

seo-service

quality

ile konuşur

Rewrite service ilişkisi

rewrite-service.php

prompt engine

router

gateway

rewrite-pipeline
ile konuşur

Selftest service ilişkisi

selftest-service.php

router

gateway

provider registry

storage helper

settings

news source
gibi katmanları sınar

TAM KOD YAZIMINA GEÇERKEN DOSYA BAŞI CHECKLIST

Her dosya tam kod yazılırken aynı checklist uygulanmalı.

Ortak checklist

ABSPATH guard var mı?

yanlış global yan etki var mı?

input normalize var mı?

hata response standardı var mı?

success response standardı var mı?

log gerekiyorsa var mı?

doğrudan UI/business logic karışması var mı?

helper isimleri tutarlı mı?

pseudo-code’daki blok sırası korunmuş mu?

GERÇEK KOD YAZIM SIRASI — SON HAL

Şimdi artık en son kesin sırayı veriyorum:

1

ai-article-generator.php

2

core/services/article-service.php

3

core/ai-article-pipeline.php

4

core/services/rewrite-service.php

5

core/services/selftest-service.php

6

ajax-handler.php

Bu sıra korunmalı.

BU AŞAMANIN NET SONUCU

Şu anda elimizde artık:

mimari docs

dosya sorumluluk rehberi

runtime truth

stabilizasyon planı

code map

bootstrap planı

ajax contracts

service/pipeline/router/gateway/provider pseudo-code

news/context/outline pseudo-code

seo/quality/selftest pseudo-code

settings/storage/feature map helpers

prompt engine contract

paket 1 planı

ve şimdi de tam kod öncesi final fonksiyon matrisi

var.

Bu artık gerçekten:

“tam dosya koduna geçilebilir”

seviyedir.















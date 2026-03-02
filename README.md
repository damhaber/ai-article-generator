# ✍️ AI Article Generator — Scriptura (Masal Panel)

**Hedef:** Dünyanın en güçlü, en güvenilir ve en “editör‑dostu” AI makale üretim & düzenleme modülü.  
**İlke:** Telifsiz/sıfırdan üretim + kontrollü yeniden yazım + ölçülebilir kalite + izlenebilir log.

- **Sürüm:** 1.4.0
- **Build:** 20260302-024708Z
- **Uyumluluk:** WordPress 6.x • PHP 8.1–8.3
- **Kurulum yeri:** `masal-panel/modules/ai-article-generator/`

---

## İçindekiler
1. [Ne yapar?](#ne-yapar)
2. [Mimari](#mimari)
3. [Kurulum](#kurulum)
4. [Panel ve Kullanım](#panel-ve-kullanim)
5. [Pipeline](#pipeline)
6. [Şablonlar](#sablonlar)
7. [Bağlam ve Kaynaklar](#baglam-ve-kaynaklar)
8. [Pexels Medya](#pexels-medya)
9. [Güvenlik](#guvenlik)
10. [Log ve Debug](#log-ve-debug)
11. [REST API](#rest-api)
12. [Troubleshooting](#troubleshooting)
13. [Roadmap](#roadmap)

---

## Ne yapar?

AI Article Generator (Scriptura), Masal Panel ekosisteminde:

- **Makale üretir** (outline → bölüm genişletme → SEO/meta → schema → kalite skoru)
- **Makale düzenler / yeniden yazar** (telifsiz/sıfırdan kuralına göre)
- **Kaynak & brief ile üretir** (verilen metin/URL listesi ile “context pack”)
- **Kaliteyi ölçer** (deterministik sinyaller + skor)
- **Site içi link önerir** (WP içeriğinden ilgili yazıları bulur)
- **Görsel bulur/ekler** (Pexels üzerinden; opsiyonel)

> Bu modül “tek prompt → tek çıktı” değildir.  
> Bir yayıncılık motoru gibi çalışması için **pipeline + context + kalite kapısı + log** birlikte tasarlanmıştır.

---

## Mimari

### Katmanlar
- **UI (Panel/JS/CSS):** `panel.php`, `ui/editor.js`, `ui/style.css`
- **Uçlar:** `ajax-handler.php`, `integrations/rest-api.php`
- **Çekirdek (Core):** `core/*` (pipeline, context, quality, post, media, metrics)
- **Dokümantasyon:** `CODEMAP.md`, `docs/*`

### Dosya ağacı (özet)
```text
ai-article-generator/
  ai-article-generator.php        # loader
  panel.php                       # admin UI
  ajax-handler.php                # admin-ajax endpoints
  ui/
    editor.js                     # panel davranışları (key save, log, pexels)
    style.css
  core/
    ai-article-pipeline.php        # V2 üretim akışı
    ai-article-context.php         # brand/audience/brief/sources
    ai-article-quality.php         # kalite skoru + sinyaller
    ai-article-internal-links.php  # WP içi link önerileri
    ai-article-post.php            # WP post oluşturma / meta
    ai-article-media.php           # Pexels fetch + attach_images
    ai-article-metrics.php         # metrikler
    ai-article-templates.php       # şablon/prompt yardımcıları
    ai-log.php                     # log altyapısı
  docs/
    ARCHITECTURE.md
    PIPELINE.md
    SECURITY.md
```

---

## Kurulum

1) Modül klasörünü buraya koy:
- `C:\xampp\htdocs\yokno\masal-panel\modules\ai-article-generator\`

2) WordPress içinde loader/tema fonksiyonundan modülü include et (senin mevcut sisteminle uyumlu):
```php
require_once ABSPATH . 'masal-panel/modules/ai-article-generator/ai-article-generator.php';
```

3) Admin’de paneli aç ve API anahtarlarını kaydet.

> Not: Daha önce yüklediğin bazı dosyalar bu sohbet ortamında “expired” olabiliyor.  
> Bu README, en son yüklediğin zip içeriğine göre güncellendi.

---

## Panel ve Kullanım

Panelde temel bloklar:
- **API Anahtarları:** Pexels anahtarı kaydet/doğrula
- **Pexels Medya Arama:** sorgu → sonuç
- **Log:** yenile/temizle + otomatik takip

---

## Pipeline

Pipeline motoru: `core/ai-article-pipeline.php`

Üretim çıktısı (özet):
```json
{
  "title": "…",
  "keyword": "…",
  "sections": [{"heading":"…","content":"…"}],
  "seo": {"slug":"…","meta_description":"…"},
  "schema": { "@type":"Article", "headline":"…" },
  "internal_links": [{"post_id":1,"title":"…","url":"…"}],
  "meta": {
    "quality": 86,
    "quality_signals": {
      "word_count": 1320,
      "keyword_density": 0.012,
      "repetition_ratio": 0.08
    }
  }
}
```

PHP ile hızlı kullanım:
```php
$article = ai_article_pipeline_generate([
  'title'    => 'Türkiye’de Yapay Zekâ ile Haber Yazımı',
  'keyword'  => 'ai haber yazımı',
  'language' => 'tr',
  'template' => 'blog',
  'brief'    => 'YoknoNews tonu: net, güvenilir, sade.',
  'sources'  => ['https://…', 'https://…'],
  'attach_images' => false,
]);
```

---

## Şablonlar

Şablonlar “prompt stratejisi”dir.  
Şimdilik çekirdek yardımcı: `core/ai-article-templates.php`

Hedef standart:
- `news`, `blog`, `guide`, `analysis`, `compare`, `listicle`, `faq`

> Şablonlar JSON’a taşınacak (Roadmap).

---

## Bağlam ve Kaynaklar

`core/ai-article-context.php` “context pack” üretir:

- brand_name / brand_style
- audience
- brief
- sources (URL listesi veya ham metin)

Bu bağlam pipeline prompt’larının başına enjekte edilir.

---

## Pexels Medya

- Anahtar kaydet/doğrula panelden yapılır.
- Görsel ekleme **opsiyoneldir**.

`attach_images=true` verilirse:
- Pexels’ten görsel arar
- indirir
- WP Media’ya ekler
- istenirse featured image yapar

---

## Güvenlik

- Admin işlemleri nonce + capability kontrolü ile korunur.
- Tüm input’lar sanitize edilir, output’lar escape edilir.

Detay: `docs/SECURITY.md`

---

## Log ve Debug

Loglar JSON satır formatında tutulur (panelde okunur).  
Log tipleri:
- `INFO`, `WARN`, `ERROR`, `SUCCESS`

Örnek:
```json
{"ts":"2025-11-18 23:50:19","lvl":"WARN","msg":"Pexels anahtarı yok","ctx":[]}
```

---

## REST API

Detay: `integrations/rest-api.php`

Örnek endpoint’ler (projeye göre değişebilir):
- `GET /wp-json/aig/v1/health`
- `POST /wp-json/aig/v1/generate`

---

## Troubleshooting

### “Pexels anahtarı yok”
- Panelden anahtarı kaydet ve doğrula.
- “Temizle” butonu storage’ı sıfırlıyorsa anahtar da silinebilir.

### “log_nonce_fail”
- AJAX isteği nonce göndermiyordur.
- `ui/editor.js` içinde `AIG.nonce` gönderildiğinden emin ol.

### admin-ajax JSON yerine HTML dönüyor
- PHP warning çıktıları JSON’u bozar.
- WP_DEBUG_DISPLAY kapalı, debug.log açık olmalı.

---

## Roadmap

**AŞAMA 3 (Dünya lideri paket):**
- JSON tabanlı Template Marketplace
- Auto‑Improve (skor düşükse otomatik iyileştirme turu)
- Similarity Guard (tekrar/kopya önleme)
- Token/Cost Monitor (günlük limit, rapor)
- Prompt Versioning + Snapshot

**AŞAMA 4:**
- Universal Editor blok entegrasyonu (H2 bazlı üretim/düzeltme)
- Çok dilli varyasyon üretimi (language hook)

---

## Referanslar
- `CODEMAP.md` — hangi özellik nerede?
- `docs/ARCHITECTURE.md` — mimari
- `docs/PIPELINE.md` — akış
- `docs/SECURITY.md` — güvenlik


---

## V4 Mimari (STABLE)

Bu sürüm, **tek panel (panel.php)** üzerinden yönetilen 5 çekirdek motoru birleştirir:

1) **Article Pipeline**
- Outline → Section Generate → Quality Score → Similarity Guard → (Opsiyonel) Draft Save
- Ayarlar: `min_quality`, `auto_improve`, `max_attempts`, `similarity_threshold`

2) **Auto-Improve**
- Quality / Similarity hedefi tutmuyorsa aynı konu için kontrollü tekrar denemeler.

3) **Similarity Guard**
- Son N çıktıya göre hızlı shingle + Jaccard kıyas.
- Amaç: aynı çıktıyı döndürmeyi ve “benzer metin” riskini azaltmak.

4) **Token/Cost Monitor**
- LLM sağlayıcınız `usage` döndürürse toplamları panelde görürsünüz.
- Not: sağlayıcı `cost_usd` vermezse 0 kalır (entegrasyon tarafında hesaplanabilir).

5) **Template Marketplace (JSON)**
- Marketplace şablonları: `storage/templates-marketplace.json`
- Import/Export panelden yapılır.

---

## Panelde Kesin Çalışan Ekranlar

- **Pexels key** kaydet / doğrula / ara
- **Makale üret** (pipeline)
- **Kaydet** (taslak)
- **Düzenle** (Rewrite Studio)
- **Self-test + Log + Usage**

---

## LLM Entegrasyonu (Provider-Agnostic)

Bu modül doğrudan “hangi LLM?” dayatmaz. Üretim için tek kapı:

- `ai_article/llm_generate` filtresi

Beklenen dönüş:

```php
add_filter('ai_article/llm_generate', function($null, $payload){
  // $payload: prompt, tone, lang, model, format=html
  return [
    'html'  => '<h2>...</h2><p>...</p>',
    'model' => 'your-provider',
    'usage' => [
      'prompt_tokens' => 0,
      'completion_tokens' => 0,
      'total_tokens' => 0,
      'cost_usd' => 0.0,
    ],
  ];
}, 10, 2);
```

Sağlayıcı yoksa sistem **demo-fallback** ile yine çalışır (fatal vermez).


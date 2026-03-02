# CODEMAP — AI Article Generator (Masal Panel)

Bu dosya “unutmama sistemi”dir: **hangi özellik nerede?** sorusunun tek cevabı.

## 0) Giriş noktaları
- **ai-article-generator.php**: Modül loader’ı (PATH/URL sabitleri, include zinciri)
- **panel.php**: Admin panel UI
- **ajax-handler.php**: AJAX uçları (panel/editor tetikler)
- **integrations/rest-api.php**: REST uçları

## 1) Core Katmanı
- **core/ai-article-core.php**
  - Konfigürasyon okuma (API anahtarları, model ayarları)
  - Makale üretim çağrılarının “tek merkez” yönetimi

- **core/ai-article-bridge.php**
  - Dış entegrasyonlarla köprü (SEO, rewrite, sources, language)
  - Tek tip arayüzle “hook” çağırma

- **core/ai-article-outline.php**
  - Başlık/alt başlık iskeleti (outline) üretimi
  - H2/H3 planı, akış kontrolü

- **core/ai-article-templates.php**
  - Şablon mantığı (haber/blog/rehber vb.)
  - Prompt şablonları ve parametreleri

- **core/ai-article-queue.php**
  - Uzun işlerin kuyruğa alınması
  - Retry / bekleyen işler (temel)

- **core/ai-article-post.php**
  - WordPress post oluşturma/güncelleme
  - Başlık, içerik, meta alanlar, kategori/etiket

- **core/ai-article-media.php**
  - Görsel üretim/çekme entegrasyonu (varsa)
  - Featured image / medya ekleme işlemleri

- **core/ai-article-metrics.php**
  - Token / maliyet / süre / başarı oranı metrikleri (temel)
  - İleride “Quality Gate” buraya bağlanacak

- **core/ai-log.php**
  - Log yazımı (dosya tabanlı)
  - Debug / hata izleri

## 2) Entegrasyonlar
- **integrations/ai-seo-hook.php**: SEO motoru ile entegrasyon
- **integrations/ai-rewrite-hook.php**: Rewrite motoru ile entegrasyon (opsiyonel)
- **integrations/ai-language-hook.php**: Çok dil motoru entegrasyonu (opsiyonel)
- **integrations/ai-sources-hook.php**: Kaynak/atıf motoru (opsiyonel)
- **integrations/api-keys-panel.php**: API anahtar yönetimi UI
- **integrations/rest-api.php**: REST endpoint’ler

## 3) UI
- **ui/editor.js**: Editör içi tetikler / butonlar
- **ui/settings.php**: UI ayar ekranı bileşenleri
- **ui/style.css**: Panel/editor stil

## 4) Yeni Dokümanlar
- **docs/ARCHITECTURE.md**: Mimari katmanlar + genişletme noktaları
- **docs/PIPELINE.md**: Üretim pipeline hedefi (V2 planı)
- **docs/SECURITY.md**: Güvenlik standardı (sanitize/escape/nonce/capability)
- **storage/feature-map.json**: Özellik → dosya eşlemesi (makine okunur)

> Kural: Yeni özellik eklenince **CODEMAP.md** ve **storage/feature-map.json** güncellenir.


## Pipeline Engine (V2)
- `core/ai-article-pipeline.php` — Outline→Sections→SEO→Schema→Quality→Save akışının merkezi.


## V2 Context / Quality / Links (since 1.3.1)

- `core/ai-article-context.php` — Context pack (brand, brief, sources) üretir ve pipeline promptlarına enjekte edilir.
- `core/ai-article-quality.php` — Deterministic kalite skoru + sinyaller (kelime sayısı, tekrar, meta length, keyword density).
- `core/ai-article-internal-links.php` — WP içinden ilgili yazıları bulur (internal link önerisi).
- `core/ai-article-pipeline.php` — Artık context + quality + internal_links kullanır.

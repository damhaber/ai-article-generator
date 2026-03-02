# ARCHITECTURE — AI Article Generator (Scriptura)

Bu doküman “ne nereye konur?” sorusunun mimari cevabıdır.

## Katmanlar

### 1) UI
- `panel.php`: Admin panel ekranı
- `ui/editor.js`: Panel davranışları (API key kaydet/doğrula, log yenile/temizle, pexels arama)
- `ui/style.css`: Stil

### 2) Uçlar (Endpoints)
- `ajax-handler.php`: admin-ajax aksiyonları (panelden tetiklenen işlemler)
- `integrations/rest-api.php`: REST uçları (harici tüketim)

### 3) Core
- `core/ai-article-pipeline.php`: Üretim hattı (outline → sections → seo → schema → quality → save)
- `core/ai-article-context.php`: Brand/audience/brief/sources paketleyici
- `core/ai-article-quality.php`: Deterministik kalite skoru (sinyaller + skor)
- `core/ai-article-internal-links.php`: WP içi link önerisi
- `core/ai-article-post.php`: WP post oluşturma / güncelleme / meta
- `core/ai-article-media.php`: Pexels fetch + indir + medya ekle + featured image
- `core/ai-article-metrics.php`: Token/maliyet/performans metrikleri (genişletilebilir)
- `core/ai-article-templates.php`: Şablon/prompt yardımcıları
- `core/ai-log.php`: Log

## Tasarım İlkeleri
- **JSON-first & izlenebilirlik**: üretim çıktısı + metrikler + log kayıtları izlenebilir olmalı.
- **Telifsiz/sıfırdan üretim**: kopyalama değil; kontrollü yeniden yazım ve özgün üretim.
- **Kalite kapısı**: skorsuz içerik “yayınlanabilir” sayılmaz.
- **Kırılmaya dayanıklılık**: admin-ajax yanıtı daima saf JSON olmalı (warning/notice bastırılmalı).

## Genişletme Noktaları
- Template JSON’ları (AŞAMA 3)
- Auto-Improve turu (quality < threshold)
- Similarity Guard (yerel index ile)
- Token/Cost Monitor (dashboard grafikleri)

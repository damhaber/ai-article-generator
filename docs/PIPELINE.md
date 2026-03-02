# PIPELINE — AI Article Generator

## Amaç
Makale üretimi ve düzenleme sürecini “tek bir akıllı hat” haline getirmek:
Outline → Section Expand → SEO → Schema → Quality Gate → Save/Publish

## Akış
1. **Brief/Intent**: başlık, keyword, hedef kitle, marka tonu
2. **Context Pack**: `ai-article-context.php`
3. **Outline**: `ai-article-outline.php` / core ask_ai
4. **Section Expand**: her H2 için içerik üretimi
5. **SEO**: slug + meta description (AŞAMA 3’te TF-IDF/density genişleyecek)
6. **Schema**: Article JSON-LD
7. **Quality**: `ai-article-quality.php`
8. **Internal Links**: `ai-article-internal-links.php`
9. **Save**: `ai-article-post.php` (draft/publish)

## Çıktı Kontratı
Pipeline çıktısı her zaman:
- `title`, `keyword`
- `sections[]`
- `seo{slug,meta_description}`
- `schema`
- `internal_links[]`
- `meta.quality`, `meta.quality_signals`

## Hata Yönetimi
- Her adım loglar: INFO/WARN/ERROR
- admin-ajax her zaman JSON döner

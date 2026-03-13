<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Basit şablonlar — ileride JSON kaynakla genişletilebilir.
 */
if (!function_exists('ai_article_templates')) {
    function ai_article_templates(): array {
        return [
            'news_basic' => [
                'title'=>'Haber (Kısa)',
                'system'=>'Tarafsız, net ve editoryal bir üslup kullan. Türkçe yaz. HTML olarak yalnızca <p>, <ul>, <li>, <strong> kullan. İstenmedikçe başlık tekrar etme.',
                'user'=>'Konu: {{topic}}. Anahtar kelime: {{keyword}}. Tarih: {{date}}. Sadece istenen bölümü yaz; placeholder, şablon etiketi veya {{sources}} bırakma. 220-420 kelime.',
            ],
            'blog_opinion' => [
                'title'=>'Blog (Görüş)',
                'system'=>'Giriş-argüman-sonuç, 600-900 kelime, HTML alt başlıklar.',
                'user'=>'Tema: {{topic}}. Karşı görüş: {{counter}}. Örnekler ver.',
            ],
            'howto_steps' => [
                'title'=>'Nasıl Yapılır',
                'system'=>'Adım adım <ol><li>. Giriş + sonuç.',
                'user'=>'Konu: {{topic}}. Gereksinimler: {{reqs}}. Hedef: {{audience}}.',
            ],
            'review_product' => [
                'title'=>'Ürün İncelemesi',
                'system'=>'Artı/eksi listesi, teknik tablo; tarafsız ton.',
                'user'=>'Ürün: {{product}}. Rakipler: {{rivals}}. Kullanım senaryoları.',
            ],
            'listicle' => [
                'title'=>'Liste İçerik',
                'system'=>'10 maddelik liste; her maddede kısa açıklama.',
                'user'=>'Konu: {{topic}}. Kapsam: {{criteria}}.',
            ],
        ];
    }
}

/* ------------------------------------------------------------
 * Template Marketplace (JSON)
 * storage/templates-marketplace.json
 * ------------------------------------------------------------ */
if (!function_exists('ai_article_templates_marketplace_path')) {
    function ai_article_templates_marketplace_path(): string {
        return wp_normalize_path(dirname(__DIR__, 1) . '/storage/templates-marketplace.json');
    }
}

if (!function_exists('ai_article_templates_marketplace_load')) {
    function ai_article_templates_marketplace_load(): array {
        $file = ai_article_templates_marketplace_path();
        if (!file_exists($file)) return [];
        $raw = json_decode((string)@file_get_contents($file), true);
        return is_array($raw) ? $raw : [];
    }
}

if (!function_exists('ai_article_templates_all')) {
    function ai_article_templates_all(): array {
        // built-in + marketplace
        $built = ai_article_templates();
        $mkt   = ai_article_templates_marketplace_load();
        return array_merge($built, is_array($mkt) ? $mkt : []);
    }
}

if (!function_exists('ai_article_templates_marketplace_save')) {
    function ai_article_templates_marketplace_save(array $templates): bool {
        $file = ai_article_templates_marketplace_path();
        if (!is_dir(dirname($file))) @wp_mkdir_p(dirname($file));
        $json = json_encode($templates, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        return (bool)@file_put_contents($file, (string)$json, LOCK_EX);
    }
}

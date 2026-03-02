<?php
if (!defined('ABSPATH')) { exit; }
/**
 * AI Article Generator — Outline (3 aşamalı üretim planı)
 */
if (!function_exists('ai_article_build_outline')) {
    function ai_article_build_outline(array $args): array {
        $topic = trim((string)($args['topic'] ?? 'Gündem'));
        $outline = [
            'title'    => sprintf('%s — Kapsamlı Analiz', $topic),
            'sections' => [
                ['h2' => 'Giriş',      'bullets' => ['Kısa özet', 'Okurun alacağı değer']],
                ['h2' => 'Arka Plan',  'bullets' => ['Önemli tarihler', 'Kilit aktörler']],
                ['h2' => 'Gelişmeler', 'bullets' => ['Somut veriler', 'Alıntılanabilir noktalar']],
                ['h2' => 'Etkiler',    'bullets' => ['Sektörel etkiler', 'Kamuoyu yansımaları']],
                ['h2' => 'Sonuç',      'bullets' => ['Özet', 'İleri okuma / kaynaklar']],
            ],
        ];
        return apply_filters('ai_article/outline', $outline, $args);
    }
}

<?php
/**
 * AI Article Generator V6
 * FAQ Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_faq_build')) {

    function aig_faq_build(array $article, array $options = []): array
    {
        $title    = trim((string) ($article['title'] ?? 'Bu konu'));
        $category = trim((string) ($article['category'] ?? 'tech'));
        $summary  = trim((string) ($article['summary'] ?? ''));

        $faq = [

            [
                'question' => $title . ' neden önemli?',
                'answer'   => $summary !== ''
                    ? $summary
                    : "Bu gelişme, seçilen kategorideki güncel eğilimleri ve olası etkileri özetlediği için önemlidir.",
            ],

            [
                'question' => ucfirst($category) . ' alanında öne çıkan başlıklar neler?',
                'answer'   => "Makale içinde öne çıkan gelişmeler, sektörel etkiler ve kısa vadeli sonuçlar birlikte ele alınmaktadır.",
            ],

            [
                'question' => 'Bu içerik hangi veri yaklaşımıyla üretildi?',
                'answer'   => "İçerik; kategori bazlı haber akışı, fact pack yaklaşımı ve editoryal düzenleme katmanlarıyla oluşturulmuştur.",
            ],

        ];

        return $faq;
    }
}
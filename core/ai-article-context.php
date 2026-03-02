<?php
/**
 * AI Article Generator — Context Engine
 *
 * Amaç:
 * - Üretime "bağlam" enjekte etmek (brand tone, hedef kitle, kısıtlar, brief, kaynak metinleri/URL listesi)
 * - Telifsiz / sıfırdan üretim standardını korumak (kopyalama yok)
 * - Uydurma veri üretimini azaltmak (source yoksa: genel konuş, kesin sayı verme)
 *
 * @since 1.3.0
 */
if (!defined('ABSPATH')) { exit; }

if (!function_exists('ai_article_build_context_pack')) {

    /**
     * Context pack üret.
     *
     * @param array $args
     *  - brand_name (string)
     *  - brand_style (string)  (news/blog/guide/...)
     *  - audience (string)     (beginner/expert/general)
     *  - lang (string)
     *  - tone (string)
     *  - brief (string)        (kullanıcı notları)
     *  - sources (array|string) URL listesi ya da ham metin
     *
     * @return array {
     *   context_text:string,
     *   meta:array
     * }
     */
    function ai_article_build_context_pack(array $args): array {

        $brand_name  = trim((string)($args['brand_name'] ?? get_option('ai_article_brand_name', '')));
        $brand_style = trim((string)($args['brand_style'] ?? get_option('ai_article_brand_style', '')));
        $audience    = trim((string)($args['audience'] ?? get_option('ai_article_audience', 'general')));
        $lang        = trim((string)($args['lang'] ?? get_option('ai_article_default_lang', 'tr')));
        $tone        = trim((string)($args['tone'] ?? 'neutral'));

        $brief = trim((string)($args['brief'] ?? ''));

        $sources_raw = $args['sources'] ?? [];
        $sources_urls = [];
        $sources_text = '';

        if (is_string($sources_raw)) {
            $sources_text = trim($sources_raw);
        } elseif (is_array($sources_raw)) {
            // URL listesi varsay
            foreach ($sources_raw as $u) {
                $u = trim((string)$u);
                if ($u === '') continue;
                $sources_urls[] = esc_url_raw($u);
            }
        }

        $lines = [];
        $lines[] = "KURALLAR (DEĞİŞMEZ):";
        $lines[] = "- Telifsiz: metni tamamen sıfırdan üret. Kaynak metin varsa bile birebir kopyalama yapma.";
        $lines[] = "- Uydurma istatistik/atıf yazma. Kaynak yoksa kesin sayı verme, genel konuş.";
        $lines[] = "- Gereksiz tekrar yapma. Aynı cümleyi farklı kelimelerle döndürme.";
        $lines[] = "- Okunabilirlik: kısa paragraflar, net cümleler, madde işaretleri gerektiğinde.";

        if ($brand_name !== '' || $brand_style !== '') {
            $lines[] = "";
            $lines[] = "MARKA:";
            if ($brand_name !== '')  $lines[] = "- Marka adı: {$brand_name}";
            if ($brand_style !== '') $lines[] = "- Marka stili: {$brand_style}";
        }

        $lines[] = "";
        $lines[] = "HEDEF:";
        $lines[] = "- Dil: {$lang}";
        $lines[] = "- Ton: {$tone}";
        $lines[] = "- Hedef kitle: {$audience}";

        if ($brief !== '') {
            $lines[] = "";
            $lines[] = "BRIEF / NOTLAR:";
            $lines[] = $brief;
        }

        if (!empty($sources_urls)) {
            $lines[] = "";
            $lines[] = "KAYNAKLAR (URL):";
            foreach ($sources_urls as $u) {
                $lines[] = "- {$u}";
            }
            $lines[] = "Not: Bu URL'leri referans alabilirsin ama içeriklerini BİREBİR kopyalama.";
        }

        if ($sources_text !== '') {
            $lines[] = "";
            $lines[] = "KAYNAK METİN / İPUÇLARI:";
            $lines[] = $sources_text;
            $lines[] = "Not: Yukarıdaki metni BİREBİR kopyalama; sadece anlamını kullan.";
        }

        $context_text = trim(implode("\n", $lines));

        return [
            'context_text' => $context_text,
            'meta' => [
                'brand_name'  => $brand_name,
                'brand_style' => $brand_style,
                'audience'    => $audience,
                'lang'        => $lang,
                'tone'        => $tone,
                'has_brief'   => ($brief !== ''),
                'has_sources' => (!empty($sources_urls) || $sources_text !== ''),
            ],
        ];
    }
}

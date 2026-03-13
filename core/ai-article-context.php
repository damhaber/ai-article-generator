<?php
/**
 * AI Article Generator V6
 * Context Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_article_context_clean_text')) {
    function aig_article_context_clean_text(string $text): string
    {
        $text = wp_strip_all_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim((string) $text);
    }
}

if (!function_exists('aig_article_context_pick_section_key')) {
    function aig_article_context_pick_section_key(int $index): string
    {
        if ($index === 0) {
            return 'intro';
        }
        if ($index <= 2) {
            return 'updates';
        }
        if ($index <= 4) {
            return 'impact';
        }
        return 'outlook';
    }
}

if (!function_exists('aig_article_context_normalize_fact_row')) {
    function aig_article_context_normalize_fact_row(array $row): array
    {
        $facts = [];
        foreach ((array) ($row['facts'] ?? []) as $fact) {
            $fact = aig_article_context_clean_text((string) $fact);
            if ($fact !== '') {
                $facts[] = $fact;
            }
        }

        $entities = [];
        foreach ((array) ($row['entities'] ?? []) as $entity) {
            $entity = aig_article_context_clean_text((string) $entity);
            if ($entity !== '') {
                $entities[] = $entity;
            }
        }

        return [
            'title'        => aig_article_context_clean_text((string) ($row['title'] ?? '')),
            'summary'      => aig_article_context_clean_text((string) ($row['summary'] ?? '')),
            'source'       => aig_article_context_clean_text((string) ($row['source'] ?? '')),
            'published_at' => trim((string) ($row['published_at'] ?? '')),
            'url'          => trim((string) ($row['url'] ?? '')),
            'facts'        => array_values(array_unique($facts)),
            'entities'     => array_values(array_unique($entities)),
        ];
    }
}

if (!function_exists('aig_article_context_build_from_fact_pack')) {
    function aig_article_context_build_from_fact_pack(array $fact_pack, array $args = []): array
    {
        $topic        = trim((string) ($args['topic'] ?? ($fact_pack['topic'] ?? 'Güncel Gelişmeler')));
        $category     = trim((string) ($args['category'] ?? ($fact_pack['category'] ?? 'tech')));
        $lang         = trim((string) ($args['lang'] ?? ($args['language'] ?? 'tr')));
        $tone         = trim((string) ($args['tone'] ?? 'professional'));
        $length       = trim((string) ($args['length'] ?? 'long'));
        $target_words = (int) ($args['target_words'] ?? 1600);
        $brief        = trim((string) ($args['brief'] ?? ''));

        if ($topic === '') {
            $topic = 'Güncel Gelişmeler';
        }
        if ($category === '') {
            $category = 'tech';
        }
        if ($lang === '') {
            $lang = 'tr';
        }
        if ($tone === '') {
            $tone = 'professional';
        }
        if ($length === '') {
            $length = 'long';
        }
        if ($target_words <= 0) {
            $target_words = 1600;
        }

        $headlines_raw = (array) ($fact_pack['headlines'] ?? []);
        $headlines = [];
        $facts_by_section = [
            'intro'   => [],
            'updates' => [],
            'impact'  => [],
            'outlook' => [],
        ];

        $flat_facts = [];
        $all_entities = [];

        foreach ($headlines_raw as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalized = aig_article_context_normalize_fact_row($row);
            if ($normalized['title'] === '' && $normalized['summary'] === '') {
                continue;
            }

            $headlines[] = $normalized;

            $section_key = aig_article_context_pick_section_key((int) $index);
            $facts_by_section[$section_key][] = $normalized;

            foreach ($normalized['facts'] as $fact) {
                $flat_facts[] = $fact;
            }

            foreach ($normalized['entities'] as $entity) {
                $all_entities[] = $entity;
            }
        }

        $flat_facts   = array_values(array_unique($flat_facts));
        $all_entities = array_values(array_unique($all_entities));

        $key_points = [];
        foreach ((array) ($fact_pack['key_points'] ?? []) as $point) {
            $point = aig_article_context_clean_text((string) $point);
            if ($point !== '') {
                $key_points[] = $point;
            }
        }
        $key_points = array_values(array_unique($key_points));

        $sources = [];
        foreach ((array) ($fact_pack['sources'] ?? []) as $source) {
            if (!is_array($source)) {
                continue;
            }

            $name = aig_article_context_clean_text((string) ($source['name'] ?? $source['source'] ?? ''));
            $url  = trim((string) ($source['url'] ?? ''));

            if ($name === '' && $url === '') {
                continue;
            }

            $sources[] = [
                'name' => $name !== '' ? $name : 'Kaynak',
                'url'  => $url,
            ];
        }

        return [
            'topic'            => $topic,
            'category'         => $category,
            'lang'             => $lang,
            'tone'             => $tone,
            'length'           => $length,
            'target_words'     => $target_words,
            'brief'            => $brief,
            'generated_at'     => current_time('c'),
            'key_points'       => $key_points,
            'headlines'        => $headlines,
            'facts_flat'       => $flat_facts,
            'facts_by_section' => $facts_by_section,
            'entities'         => $all_entities,
            'sources'          => $sources,
            'source_count'     => count($sources),
            'range'            => (string) ($fact_pack['range'] ?? ''),
            'item_count'       => (int) ($fact_pack['item_count'] ?? count($headlines)),
        ];
    }
}

if (!function_exists('aig_article_context_build_sources_block')) {
    function aig_article_context_build_sources_block(array $fact_pack): string
    {
        $headlines = (array) ($fact_pack['headlines'] ?? []);
        if (empty($headlines)) {
            return '';
        }

        $lines = [];
        $seen  = [];

        foreach ($headlines as $item) {
            if (!is_array($item)) {
                continue;
            }

            $source = aig_article_context_clean_text((string) ($item['source'] ?? 'Kaynak'));
            $title  = aig_article_context_clean_text((string) ($item['title'] ?? 'Başlık'));
            $url    = trim((string) ($item['url'] ?? ''));

            if ($title === '') {
                continue;
            }

            $uniq = md5(mb_strtolower($source . '|' . $title));
            if (isset($seen[$uniq])) {
                continue;
            }
            $seen[$uniq] = true;

            if ($url !== '') {
                $lines[] = '<li><strong>' . esc_html($source) . '</strong> — <a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($title) . '</a></li>';
            } else {
                $lines[] = '<li><strong>' . esc_html($source) . '</strong> — ' . esc_html($title) . '</li>';
            }
        }

        if (empty($lines)) {
            return '';
        }

        return '<h2>Kaynaklar</h2>' . "\n" . '<ul>' . "\n" . implode("\n", $lines) . "\n" . '</ul>';
    }
}

if (!function_exists('aig_article_context_build_prompt_payload')) {
    function aig_article_context_build_prompt_payload(array $context, array $outline, array $args = []): array
    {
        $system_prompt = implode("\n", [
            'Sen deneyimli bir dijital yayın editörüsün.',
            'Verilen fact pack ve outline dışına taşmadan, akıcı ve güvenilir bir haber-analiz makalesi yaz.',
            'Metin açık, profesyonel ve tekrar etmeyen bir yapıda olmalı.',
            'Varsayım üretme; verilen gelişmeleri dikkatlice işle.',
            'Makaleyi HTML uyumlu paragraf ve başlık düzeniyle oluştur.',
            'Gereksiz tekrar, abartılı iddia ve doğrulanmamış çıkarım kullanma.',
        ]);

        $outline_json   = wp_json_encode($outline, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $headlines_json = wp_json_encode($context['headlines'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $points_text    = implode(' | ', (array) ($context['key_points'] ?? []));
        $entities_text  = implode(', ', (array) ($context['entities'] ?? []));

        $user_prompt = [
            'Konu: ' . (string) ($context['topic'] ?? ''),
            'Kategori: ' . (string) ($context['category'] ?? ''),
            'Dil: ' . (string) ($context['lang'] ?? 'tr'),
            'Ton: ' . (string) ($context['tone'] ?? 'professional'),
            'Hedef Uzunluk: ' . (string) ($context['target_words'] ?? 1600) . ' kelime',
            'Kısa Editör Notu: ' . (string) ($context['brief'] ?? ''),
            'Ana Noktalar: ' . $points_text,
            'Öne Çıkan Varlıklar: ' . $entities_text,
            'Outline: ' . $outline_json,
            'Fact Pack: ' . $headlines_json,
        ];

        return [
            'system_prompt' => $system_prompt,
            'user_prompt'   => implode("\n", $user_prompt),
            'context'       => $context,
            'outline'       => $outline,
            'args'          => $args,
        ];
    }
}

if (!function_exists('ai_article_build_context_pack')) {
    function ai_article_build_context_pack(array $args = []): array
    {
        $fact_pack = (array) ($args['fact_pack'] ?? []);
        return aig_article_context_build_from_fact_pack($fact_pack, $args);
    }
}
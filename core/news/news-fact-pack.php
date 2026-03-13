<?php
// core/news/news-fact-pack.php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * News Fact Pack Builder
 *
 * Amaç:
 * - normalize edilmiş haber listesini LLM için kısa ve temiz bağlama çevirmek
 * - headlines / key_points üretmek
 * - kaynakları çıkarmak
 * - prompt içine gömülebilir rendered_text oluşturmak
 */

if (!function_exists('aig_news_fact_pack_parse_args')) {
    /**
     * Hibrit arg parser
     *
     * Destekler:
     * 1) aig_news_build_fact_pack($items, ['category' => 'tech'])
     * 2) aig_news_build_fact_pack(['category' => 'tech', 'items' => $items])
     */
    function aig_news_fact_pack_parse_args(array $items_or_args, array $args = []): array
    {
        $items = $items_or_args;

        // Eğer tek parametreli config array geldiyse içinden items çek
        if (isset($items_or_args['items']) && is_array($items_or_args['items'])) {
            $args  = $items_or_args;
            $items = (array) $items_or_args['items'];
        }

        $category = sanitize_key((string) ($args['category'] ?? 'tech'));

        if (function_exists('aig_news_limit_range')) {
            $range = aig_news_limit_range((string) ($args['range'] ?? '3d'));
        } else {
            $range = (string) ($args['range'] ?? '3d');
        }

        if (function_exists('aig_news_limit_int')) {
            $max_highlights = aig_news_limit_int($args['max_highlights'] ?? 5, 5, 1, 10);
        } else {
            $max_highlights = max(1, min(10, (int) ($args['max_highlights'] ?? 5)));
        }

        $topic = trim((string) ($args['topic'] ?? ''));

        return [
            'items'          => array_values(is_array($items) ? $items : []),
            'category'       => $category !== '' ? $category : 'tech',
            'range'          => $range,
            'max_highlights' => $max_highlights,
            'topic'          => $topic,
        ];
    }
}

if (!function_exists('aig_news_build_fact_pack')) {
    /**
     * Ana fact pack üretici
     */
    function aig_news_build_fact_pack(array $items_or_args, array $args = []): array
    {
        $parsed = aig_news_fact_pack_parse_args($items_or_args, $args);

        $items          = $parsed['items'];
        $category       = $parsed['category'];
        $range          = $parsed['range'];
        $max_highlights = $parsed['max_highlights'];
        $topic          = $parsed['topic'];

        if (function_exists('aig_news_safe_array')) {
            $items = aig_news_safe_array($items);
        } else {
            $items = is_array($items) ? $items : [];
        }

        $items = array_values($items);

        $headlines  = [];
        $key_points = [];
        $source_rows = [];
        $seen = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (function_exists('aig_news_is_valid_item') && !aig_news_is_valid_item($item)) {
                continue;
            }

            $title = function_exists('aig_news_normalize_text')
                ? aig_news_normalize_text((string) ($item['title'] ?? ''))
                : trim((string) ($item['title'] ?? ''));

            $summary = function_exists('aig_news_trim_summary')
                ? aig_news_trim_summary((string) ($item['summary'] ?? ''), 220)
                : trim((string) ($item['summary'] ?? ''));

            $source = sanitize_text_field((string) ($item['source'] ?? ''));
            $url = esc_url_raw((string) ($item['url'] ?? ''));
            $published_at = sanitize_text_field((string) ($item['published_at'] ?? ''));

            if ($title === '' || $url === '') {
                continue;
            }

            $uniq = md5(mb_strtolower($title . '|' . $source . '|' . $url));
            if (isset($seen[$uniq])) {
                continue;
            }
            $seen[$uniq] = true;

            $row = [
                'title'        => $title,
                'summary'      => $summary,
                'source'       => $source,
                'url'          => $url,
                'published_at' => $published_at,
                'category'     => $category,
                'facts'        => $summary !== '' ? [$summary] : [],
                'entities'     => [],
            ];

            $headlines[] = $row;

            if ($summary !== '') {
                $key_points[] = $summary;
            }

            if ($source !== '') {
                $source_rows[] = [
                    'name' => $source,
                    'url'  => $url,
                ];
            }

            if (count($headlines) >= $max_highlights) {
                break;
            }
        }

        $key_points = array_values(array_unique(array_filter($key_points)));

        $sources = [];
        $source_seen = [];
        foreach ($source_rows as $row) {
            $name = sanitize_text_field((string) ($row['name'] ?? ''));
            $url  = esc_url_raw((string) ($row['url'] ?? ''));

            if ($name === '') {
                continue;
            }

            $suniq = md5(mb_strtolower($name . '|' . $url));
            if (isset($source_seen[$suniq])) {
                continue;
            }
            $source_seen[$suniq] = true;

            $sources[] = [
                'name' => $name,
                'url'  => $url,
            ];
        }

        $fact_pack = [
            'topic'         => $topic,
            'category'      => $category,
            'range'         => $range,
            'item_count'    => count($items),
            'headlines'     => $headlines,
            'key_points'    => $key_points,
            'sources'       => $sources,
            'rendered_text' => '',
        ];

        $fact_pack['rendered_text'] = aig_news_render_fact_pack_text([
            'category'   => $fact_pack['category'],
            'range'      => $fact_pack['range'],
            'highlights' => $fact_pack['headlines'],
            'sources'    => array_map(static function ($row) {
                return (string) ($row['name'] ?? '');
            }, $fact_pack['sources']),
        ]);

        return $fact_pack;
    }
}

if (!function_exists('aig_news_extract_source_names')) {
    /**
     * Kaynak isimlerini benzersiz olarak çıkar
     */
    function aig_news_extract_source_names(array $items): array
    {
        if (function_exists('aig_news_safe_array')) {
            $items = aig_news_safe_array($items);
        } else {
            $items = is_array($items) ? $items : [];
        }

        $seen = [];
        $out = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $source = isset($item['source']) ? sanitize_text_field((string) $item['source']) : '';
            if ($source === '') {
                continue;
            }

            $key = mb_strtolower($source, 'UTF-8');
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $out[] = $source;
        }

        return array_values($out);
    }
}

if (!function_exists('aig_news_render_fact_pack_text')) {
    /**
     * Fact pack'i prompt içine gömülebilir düz metne çevir
     */
    function aig_news_render_fact_pack_text(array $fact_pack): string
    {
        $category   = isset($fact_pack['category']) ? sanitize_key((string) $fact_pack['category']) : 'tech';
        $range      = isset($fact_pack['range']) ? (function_exists('aig_news_limit_range') ? aig_news_limit_range((string) $fact_pack['range']) : (string) $fact_pack['range']) : '3d';
        $highlights = isset($fact_pack['highlights']) && is_array($fact_pack['highlights']) ? $fact_pack['highlights'] : [];
        $sources    = isset($fact_pack['sources']) && is_array($fact_pack['sources']) ? $fact_pack['sources'] : [];

        $lines = [];
        $lines[] = 'Kategori: ' . $category;
        $lines[] = 'Zaman aralığı: ' . (function_exists('aig_news_range_label') ? aig_news_range_label($range) : $range);
        $lines[] = '';

        if (!empty($highlights)) {
            $lines[] = 'Öne çıkan haberler:';

            foreach ($highlights as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $title = function_exists('aig_news_normalize_text')
                    ? aig_news_normalize_text((string) ($row['title'] ?? ''))
                    : trim((string) ($row['title'] ?? ''));

                $summary = function_exists('aig_news_trim_summary')
                    ? aig_news_trim_summary((string) ($row['summary'] ?? ''), 180)
                    : trim((string) ($row['summary'] ?? ''));

                $source = sanitize_text_field((string) ($row['source'] ?? ''));

                if ($title === '') {
                    continue;
                }

                $bullet = '- ' . $title;

                if ($summary !== '') {
                    $bullet .= ' — ' . $summary;
                }

                if ($source !== '') {
                    $bullet .= ' (' . $source . ')';
                }

                $lines[] = $bullet;
            }
        } else {
            $lines[] = 'Öne çıkan haberler:';
            $lines[] = '- Seçilen kategori için işlenmiş güncel haber bulunamadı.';
        }

        if (!empty($sources)) {
            $clean_sources = array_values(array_filter(array_map(static function ($src) {
                if (is_array($src)) {
                    return sanitize_text_field((string) ($src['name'] ?? ''));
                }
                return sanitize_text_field((string) $src);
            }, $sources)));

            if (!empty($clean_sources)) {
                $lines[] = '';
                $lines[] = 'Kaynaklar: ' . implode(', ', $clean_sources);
            }
        }

        return trim(implode("\n", $lines));
    }
}

if (!function_exists('aig_news_fact_pack_compact_highlights')) {
    /**
     * Fact pack özetlerini kısalt
     */
    function aig_news_fact_pack_compact_highlights(array $highlights, int $max_len = 160): array
    {
        $out = [];

        foreach ($highlights as $row) {
            if (!is_array($row)) {
                continue;
            }

            $title = function_exists('aig_news_normalize_text')
                ? aig_news_normalize_text((string) ($row['title'] ?? ''))
                : trim((string) ($row['title'] ?? ''));

            $summary = function_exists('aig_news_trim_summary')
                ? aig_news_trim_summary((string) ($row['summary'] ?? ''), $max_len)
                : trim((string) ($row['summary'] ?? ''));

            $source = sanitize_text_field((string) ($row['source'] ?? ''));
            $url = esc_url_raw((string) ($row['url'] ?? ''));

            if ($title === '') {
                continue;
            }

            $out[] = [
                'title'   => $title,
                'summary' => $summary,
                'source'  => $source,
                'url'     => $url,
            ];
        }

        return $out;
    }
}

if (!function_exists('aig_news_render_prompt_ready_fact_pack')) {
    /**
     * Prompt için ekstra güvenli text üretici
     */
    function aig_news_render_prompt_ready_fact_pack(array $fact_pack): string
    {
        $rendered = isset($fact_pack['rendered_text'])
            ? (string) $fact_pack['rendered_text']
            : '';

        if (function_exists('aig_news_normalize_text')) {
            $rendered = aig_news_normalize_text($rendered);
        } else {
            $rendered = trim($rendered);
        }

        if ($rendered === '') {
            return "Kategori: tech\nZaman aralığı: son 3 gün\n\nÖne çıkan haberler:\n- Güncel haber özeti bulunamadı.";
        }

        return $rendered;
    }
}

if (!function_exists('aig_news_render_sources_html')) {
    /**
     * Kaynaklar HTML bloğu
     */
    function aig_news_render_sources_html(array $sources): string
    {
        $sources = array_values(array_filter(array_map(static function ($value) {
            if (is_array($value)) {
                return sanitize_text_field((string) ($value['name'] ?? ''));
            }
            return sanitize_text_field((string) $value);
        }, $sources)));

        if (empty($sources)) {
            return '';
        }

        $html = '<h3>Kaynaklar</h3><ul>';

        foreach ($sources as $source) {
            $html .= '<li>' . esc_html($source) . '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}
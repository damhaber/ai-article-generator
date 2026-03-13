<?php
// core/news/news-normalizer.php
if (!defined('ABSPATH')) exit;

/**
 * News normalizer
 *
 * Amaç:
 * - ham haber item'larını temizlemek
 * - geçersiz kayıtları elemek
 * - tarih filtresi uygulamak
 * - duplicate kayıtları silmek
 * - sıralamak
 * - limit uygulamak
 */

/**
 * Tüm normalize akışı
 */
function aig_news_normalize_items(array $items, array $args = []): array
{
    $range = aig_news_limit_range((string) ($args['range'] ?? '3d'));
    $limit = aig_news_limit_int($args['limit'] ?? 8, 8, 1, 20);

    $items = aig_news_safe_array($items);

    $items = aig_news_filter_invalid_items($items);
    $items = aig_news_filter_by_date($items, $range);
    $items = aig_news_dedupe_items($items);
    $items = aig_news_sort_items($items);
    $items = aig_news_limit_items($items, $limit);

    return array_values($items);
}

/**
 * Geçersiz item'ları temizle
 *
 * Atılacaklar:
 * - boş başlık
 * - boş url
 * - timestamp yok / bozuk
 */
function aig_news_filter_invalid_items(array $items): array
{
    $out = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $title = isset($item['title']) ? aig_news_normalize_text((string) $item['title']) : '';
        $summary = isset($item['summary']) ? aig_news_trim_summary((string) $item['summary']) : '';
        $url = isset($item['url']) ? esc_url_raw((string) $item['url']) : '';
        $source = isset($item['source']) ? sanitize_text_field((string) $item['source']) : '';
        $source_id = isset($item['source_id']) ? sanitize_key((string) $item['source_id']) : '';
        $category = isset($item['category']) ? sanitize_key((string) $item['category']) : '';
        $lang = isset($item['lang']) ? sanitize_key((string) $item['lang']) : 'en';

        $timestamp = isset($item['timestamp']) ? (int) $item['timestamp'] : 0;
        if ($timestamp <= 0 && !empty($item['published_at'])) {
            $parsed = aig_news_parse_date($item['published_at']);
            $timestamp = $parsed ?: 0;
        }

        $published_at = $timestamp > 0
            ? gmdate('c', $timestamp)
            : '';

        $normalized = [
            'title'        => $title,
            'summary'      => $summary,
            'url'          => $url,
            'source'       => $source,
            'source_id'    => $source_id,
            'published_at' => $published_at,
            'timestamp'    => $timestamp,
            'category'     => $category,
            'lang'         => $lang,
        ];

        if (!aig_news_is_valid_item($normalized)) {
            continue;
        }

        $out[] = $normalized;
    }

    return $out;
}

/**
 * Tarih filtresi
 */
function aig_news_filter_by_date(array $items, string $range): array
{
    $range = aig_news_limit_range($range);
    $out = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $timestamp = isset($item['timestamp']) ? (int) $item['timestamp'] : 0;
        if ($timestamp <= 0) {
            continue;
        }

        if (!aig_news_is_recent($timestamp, $range)) {
            continue;
        }

        $out[] = $item;
    }

    return $out;
}

/**
 * Duplicate silme
 *
 * Öncelik:
 * 1) aynı URL
 * 2) aynı normalize edilmiş başlık
 */
function aig_news_dedupe_items(array $items): array
{
    $out = [];
    $seen_urls = [];
    $seen_titles = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $url = isset($item['url']) ? trim((string) $item['url']) : '';
        $title = isset($item['title']) ? trim((string) $item['title']) : '';
        $title_key = aig_news_slugify_title($title);

        if ($url !== '') {
            $url_key = md5($url);
            if (isset($seen_urls[$url_key])) {
                continue;
            }
            $seen_urls[$url_key] = true;
        }

        if ($title_key !== '') {
            if (isset($seen_titles[$title_key])) {
                continue;
            }
            $seen_titles[$title_key] = true;
        }

        $out[] = $item;
    }

    return $out;
}

/**
 * Yeni tarih en üstte
 */
function aig_news_sort_items(array $items): array
{
    usort($items, static function (array $a, array $b): int {
        $a_ts = isset($a['timestamp']) ? (int) $a['timestamp'] : 0;
        $b_ts = isset($b['timestamp']) ? (int) $b['timestamp'] : 0;

        if ($a_ts !== $b_ts) {
            return $b_ts <=> $a_ts;
        }

        $a_title = isset($a['title']) ? (string) $a['title'] : '';
        $b_title = isset($b['title']) ? (string) $b['title'] : '';

        return strcmp($a_title, $b_title);
    });

    return $items;
}

/**
 * Limit uygula
 */
function aig_news_limit_items(array $items, int $limit): array
{
    $limit = aig_news_limit_int($limit, 8, 1, 20);

    if (count($items) <= $limit) {
        return array_values($items);
    }

    return array_slice(array_values($items), 0, $limit);
}
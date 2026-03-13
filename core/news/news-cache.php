<?php
// core/news/news-cache.php
if (!defined('ABSPATH')) exit;

/**
 * News cache
 *
 * Amaç:
 * - kategori/range/limit bazlı cache yönetmek
 * - transient ile hızlı erişim sağlamak
 * - debug için json kopyası bırakmak
 */

/**
 * Cache TTL
 */
function aig_news_cache_ttl(): int
{
    // İleride settings dosyasından okunabilir
    return 10 * MINUTE_IN_SECONDS;
}

/**
 * Cache key
 */
function aig_news_cache_key(string $category, string $range, int $limit): string
{
    $category = sanitize_key($category);
    $range    = aig_news_limit_range($range);
    $limit    = aig_news_limit_int($limit, 8, 1, 20);

    return 'aig_news_' . $category . '_' . $range . '_' . $limit;
}

/**
 * Debug cache klasörü
 */
function aig_news_cache_dir(): string
{
    if (defined('AI_ARTICLE_GENERATOR_PATH')) {
        return trailingslashit(AI_ARTICLE_GENERATOR_PATH) . 'data/news-cache';
    }

    return dirname(__DIR__, 2) . '/data/news-cache';
}

/**
 * Debug cache dosyası
 */
function aig_news_cache_debug_file(string $category, string $range, int $limit): string
{
    $dir = aig_news_cache_dir();

    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    $category = sanitize_key($category);
    $range    = aig_news_limit_range($range);
    $limit    = aig_news_limit_int($limit, 8, 1, 20);

    return trailingslashit($dir) . $category . '_' . $range . '_' . $limit . '.json';
}

/**
 * Cache oku
 */
function aig_news_cache_get(string $category, string $range, int $limit): array
{
    $key = aig_news_cache_key($category, $range, $limit);

    $payload = get_transient($key);

    if (!is_array($payload) || empty($payload['items']) || !is_array($payload['items'])) {
        return [
            'ok'         => false,
            'cached'     => false,
            'items'      => [],
            'created_at' => 0,
        ];
    }

    return [
        'ok'         => true,
        'cached'     => true,
        'items'      => array_values($payload['items']),
        'created_at' => isset($payload['created_at']) ? (int) $payload['created_at'] : 0,
    ];
}

/**
 * Cache yaz
 */
function aig_news_cache_set(string $category, string $range, int $limit, array $items): bool
{
    $category = sanitize_key($category);
    $range    = aig_news_limit_range($range);
    $limit    = aig_news_limit_int($limit, 8, 1, 20);
    $items    = array_values(aig_news_safe_array($items));

    if (empty($items)) {
        return false;
    }

    $payload = [
        'category'   => $category,
        'range'      => $range,
        'limit'      => $limit,
        'created_at' => time(),
        'items'      => $items,
    ];

    $saved = set_transient(
        aig_news_cache_key($category, $range, $limit),
        $payload,
        aig_news_cache_ttl()
    );

    // debug json
    $file = aig_news_cache_debug_file($category, $range, $limit);
    $json = wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (is_string($json) && $json !== '') {
        @file_put_contents($file, $json);
    }

    return (bool) $saved;
}

/**
 * Cache temizle
 *
 * Not:
 * WordPress transient’lerde wildcard delete yok.
 * Bu yüzden:
 * - debug json dosyalarını temizliyoruz
 * - bilinen kategori/range/limit kombinasyonları için transient siliyoruz
 */
function aig_news_cache_clear(string $category = ''): void
{
    $category = sanitize_key($category);

    $categories = $category !== '' ? [$category] : aig_news_get_categories();
    $ranges = ['24h', '3d', '7d'];
    $limits = range(1, 20);

    foreach ($categories as $cat) {
        foreach ($ranges as $range) {
            foreach ($limits as $limit) {
                delete_transient(aig_news_cache_key($cat, $range, $limit));

                $file = aig_news_cache_debug_file($cat, $range, $limit);
                if (file_exists($file) && is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}
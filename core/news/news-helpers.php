<?php
// core/news/news-helpers.php
if (!defined('ABSPATH')) exit;

/**
 * News helpers
 *
 * Amaç:
 * - metin normalize
 * - tarih parse
 * - zaman aralığı kontrolü
 * - log yardımcıları
 * - güvenli sanitize yardımcıları
 */

/**
 * Text normalize
 */
function aig_news_normalize_text(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = wp_strip_all_tags($text, true);

    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace("/[ \t]+/u", ' ', $text);
    $text = preg_replace("/\n{2,}/u", "\n\n", $text);
    $text = trim((string) $text);

    return $text;
}

/**
 * Başlığı dedupe için normalize et
 */
function aig_news_slugify_title(string $title): string
{
    $title = aig_news_normalize_text($title);
    $title = mb_strtolower($title, 'UTF-8');
    $title = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $title);
    $title = preg_replace('/\s+/u', ' ', (string) $title);
    return trim((string) $title);
}

/**
 * Güvenli dizi
 */
function aig_news_safe_array($value): array
{
    return is_array($value) ? $value : [];
}

/**
 * Range sanitize
 */
function aig_news_limit_range(string $range): string
{
    $range = sanitize_key($range);
    $allowed = ['24h', '3d', '7d'];

    return in_array($range, $allowed, true) ? $range : '3d';
}

/**
 * Integer sanitize / clamp
 */
function aig_news_limit_int($value, int $default = 8, int $min = 1, int $max = 20): int
{
    $value = (int) $value;

    if ($value <= 0) {
        $value = $default;
    }

    if ($value < $min) {
        $value = $min;
    }

    if ($value > $max) {
        $value = $max;
    }

    return $value;
}

/**
 * Date parse -> timestamp
 */
function aig_news_parse_date($value): ?int
{
    if (is_int($value) && $value > 0) {
        return $value;
    }

    if (!is_string($value) || trim($value) === '') {
        return null;
    }

    $ts = strtotime($value);
    if ($ts === false || $ts <= 0) {
        return null;
    }

    return $ts;
}

/**
 * Range -> seconds
 */
function aig_news_range_to_seconds(string $range): int
{
    $range = aig_news_limit_range($range);

    switch ($range) {
        case '24h':
            return DAY_IN_SECONDS;
        case '7d':
            return 7 * DAY_IN_SECONDS;
        case '3d':
        default:
            return 3 * DAY_IN_SECONDS;
    }
}

/**
 * Timestamp son aralık içinde mi?
 */
function aig_news_is_recent(int $timestamp, string $range): bool
{
    if ($timestamp <= 0) {
        return false;
    }

    $seconds = aig_news_range_to_seconds($range);
    $now = time();

    return $timestamp >= ($now - $seconds);
}

/**
 * Range label
 */
function aig_news_range_label(string $range): string
{
    $range = aig_news_limit_range($range);

    switch ($range) {
        case '24h':
            return 'son 24 saat';
        case '7d':
            return 'son 7 gün';
        case '3d':
        default:
            return 'son 3 gün';
    }
}

/**
 * Log dosya yolu
 */
function aig_news_log_file(): string
{
    if (defined('AI_ARTICLE_GENERATOR_PATH')) {
        return trailingslashit(AI_ARTICLE_GENERATOR_PATH) . 'logs/news.log';
    }

    return dirname(__DIR__, 2) . '/logs/news.log';
}

/**
 * Log yaz
 */
function aig_news_log(string $op, array $ctx = []): void
{
    $file = aig_news_log_file();
    $dir  = dirname($file);

    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    $payload = [
        'ts'  => function_exists('current_time')
            ? current_time('c')
            : gmdate('c'),
        'op'  => sanitize_key($op),
        'ctx' => $ctx,
    ];

    $line = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($line) || $line === '') {
        return;
    }

    @file_put_contents($file, $line . PHP_EOL, FILE_APPEND);
}

/**
 * Collector özel log
 */
function aig_news_collector_log(string $op, array $ctx = []): void
{
    $file = defined('AI_ARTICLE_GENERATOR_PATH')
        ? trailingslashit(AI_ARTICLE_GENERATOR_PATH) . 'logs/collector.log'
        : dirname(__DIR__, 2) . '/logs/collector.log';

    $dir = dirname($file);
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    $payload = [
        'ts'  => function_exists('current_time')
            ? current_time('c')
            : gmdate('c'),
        'op'  => sanitize_key($op),
        'ctx' => $ctx,
    ];

    $line = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($line) || $line === '') {
        return;
    }

    @file_put_contents($file, $line . PHP_EOL, FILE_APPEND);
}

/**
 * Kısa özet temizleme
 */
function aig_news_trim_summary(string $summary, int $max_len = 220): string
{
    $summary = aig_news_normalize_text($summary);

    if ($summary === '') {
        return '';
    }

    if (mb_strlen($summary, 'UTF-8') <= $max_len) {
        return $summary;
    }

    $trimmed = mb_substr($summary, 0, $max_len, 'UTF-8');
    $last_space = mb_strrpos($trimmed, ' ', 0, 'UTF-8');

    if ($last_space !== false) {
        $trimmed = mb_substr($trimmed, 0, $last_space, 'UTF-8');
    }

    return rtrim($trimmed, " \t\n\r\0\x0B.,;:-") . '…';
}

/**
 * Basit item doğrulama
 */
function aig_news_is_valid_item(array $item): bool
{
    $title = isset($item['title']) ? trim((string) $item['title']) : '';
    $url   = isset($item['url']) ? trim((string) $item['url']) : '';
    $ts    = isset($item['timestamp']) ? (int) $item['timestamp'] : 0;

    return !($title === '' || $url === '' || $ts <= 0);
}
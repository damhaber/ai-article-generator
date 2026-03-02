<?php
/**
 * AI Article Generator — AI SEO Engine Hook
 * - Post kaydedildikten sonra SEO analizini tetikler
 * - Schema + Score + Telemetry meta’larını günceller (SEO Engine tarafı handle eder)
 */

if (!defined('ABSPATH')) { exit; }

/**
 * ai-article-post.php içinden çağrılmalı:
 * do_action('ai_article/post_saved', $post_id, $meta);
 */
add_action('ai_article/post_saved', function($post_id, $meta = []) {
    $post_id = (int)$post_id;
    if ($post_id <= 0) return;

    // SEO Engine tetikleme
    do_action('ai_seo_engine/analyze_post', $post_id);

    // Log
    if (!function_exists('ai_article_log')) {
        function ai_article_log(string $op, $data = null, string $level = 'info'): void {
            $row = ['ts'=>gmdate('c'),'level'=>$level,'op'=>$op];
            if ($data !== null) $row['data'] = $data;
            $line = json_encode($row, JSON_UNESCAPED_UNICODE);
            $file = wp_normalize_path(dirname(__DIR__, 1) . '/logs/ai-article-generator.log');
            if (!is_dir(dirname($file))) @wp_mkdir_p(dirname($file));
            @file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
            if (defined('AISEO_LOG_FILE')) @file_put_contents(AISEO_LOG_FILE, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
    ai_article_log('seo_analysis_trigger', ['post_id' => $post_id, 'meta' => $meta]);
}, 10, 2);

<?php
/**
 * AI Article Generator — Dev Notes / Feature Map
 *
 * Amaç: “neyi nerede kodladık?” sorusunun makine-okunur cevabı.
 * Kaynak: /storage/feature-map.json
 */
if (!defined('ABSPATH')) exit;

if (!function_exists('ai_article_feature_map')) {
    function ai_article_feature_map(): array {
        $file = AI_ARTICLE_MODULE_PATH . '/storage/feature-map.json';
        if (!file_exists($file)) return [];
        $raw = file_get_contents($file);
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }
}
